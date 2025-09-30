<?php
namespace App\Services;

use Illuminate\Http\Request;

class RequestBankJatim
{
    public static function getToken()
    {
        return env('BANK_JATIM_TOKEN', 'bank_jatim_token');
    }

    public static function handle(Request $request)
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
