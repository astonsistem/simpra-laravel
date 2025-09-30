<?php
namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RequestBankJatim
{
    const TTL = 300; // 5 menit

    public static function handle(Request $request)
    {
        $log = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/request_bank_jatim.log')
        ]);

        $response = self::fetchData($request);

        if ($response->failed()) {
            $log->error('Error in RequestBankJatim: ' . $response->body());

            return [
                "success" => false,
                "data" => [
                    "responseCode" => "99",
                    "responseDesc" => "Failed",
                    "history" => []
                ],
            ];
        }

        // save to cache
        self::setCacheData( $response->object()->history );

        return [
            "success" => true,
            "expired_time" => now()->addSeconds( self::TTL ),
            "data" => $response->json(),
        ];
    }

    public static function fetchData($request): Response
    {
        $setting = config('settings');

        // create file log for own
        $log = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/request_bank_jatim.log')
        ]);

        $log->info("Auth: ". json_encode(Auth::user()));

        $data = [
            'tglawal' => self::formatDate($request->tglawal),
            'tglakhir' => self::formatDate($request->tglakhir),
            'url' => self::mask($setting['sync_url_jatim']),
            'key' => self::mask($setting['sync_key_jatim']),
        ];

        $log->info('RequestBankJatim: '. json_encode($data));

        return Http::post($setting['sync_url_jatim'], [
            'key' => $setting['sync_key_jatim'],
            'tglawal' => $data['tglawal'],
            'tglakhir' => $data['tglakhir'],
        ]);
    }
    /**
     * $request->tglawal
     * $request->tglakhir
     */
    public static function getCacheData($request)
    {
        $user = Auth::user();
        $key = $user->id.":request_bank_jatim";

        if( Cache::has($key) ) {
            $data = Cache::get( $key);
            $data = $data && Str::isJson($data) ? json_decode($data, true) : $data;
            self::log()->info("Data cache:  {data} ", ['data' => $data]);
            return $data;
        }

        $response = self::fetchData($request);

        return $response->object()->history;
    }

    public static function setCacheData($data)
    {
        $user = Auth::user();

        Cache::remember($user->id.":request_bank_jatim", self::TTL, function () use ($data) {
            return $data;
        });
    }

    public static function mask($string)
    {
        return Str::mask(Str::take($string, 12) , '*', 8);
    }

    public static function formatDate($date)
    {
        return date('Ymd', strtotime($date));
    }

    public static function log(): Logger
    {
        return Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/request_bank_jatim.log')
        ]);
    }

    private function exampleResponse()
    {
        return json_decode('{
            "responseCode": "00",
            "responseDesc": "Success",
            "history": [
                {
                    "dateTime": "2025-08-06 02:01:42",
                    "description": "PRACQ MB:9360001410068579035",
                    "transactionCode": "1017",
                    "amount": 40222.0,
                    "flag": "C",
                    "ccy": "IDR",
                    "reffno": "JQA026742329"
                },
                {
                    "dateTime": "2025-08-06 12:05:14",
                    "description": "000 P.LS0174 06082025",
                    "transactionCode": "5004",
                    "amount": 6869626372.0,
                    "flag": "D",
                    "ccy": "IDR",
                    "reffno": "EI174978"
                }
            ]
        }');
    }
}
