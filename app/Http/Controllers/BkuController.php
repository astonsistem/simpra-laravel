<?php

namespace App\Http\Controllers;

use App\Http\Requests\BkuRequest;
use App\Http\Resources\BkuCollection;
use App\Http\Resources\BkuResource;
use App\Models\MasterJenisBku;
use App\Models\DataBku;
use App\Models\DataRincianBku;
use App\Models\DataRekeningKoran;
use App\Models\DataPenerimaanLayanan;
use App\Models\DataPenerimaanLain;
use App\Models\DataPenerimaanSelisih;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BkuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tahun_periode' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'no_bku' => 'nullable|string',
                'uraian' => 'nullable|string',
                'jenisbku_nama' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahun_periode');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $noBKU = $request->input('no_bku');
            $uraian = $request->input('uraian');
            $jenisBKUNama = $request->input('jenisbku_nama');
            $status = $request->input('status');

            $query = DataBKU::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_bku', $tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bku', [$startDate, $endDate]);
            }
            if (!empty($noBKU)) {
                $query->where('no_bku', 'ILIKE', "%$noBKU%");
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($jenisBKUNama)) {
                $query->where('jenisbku_nama', 'ILIKE', "%$jenisBKUNama%");
            }
            if (!empty($status)) {
                $query->havingRaw('status ILIKE ?', [$status]);
            }

            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder');
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $totalItems = $query->toBase()->getCountForPagination();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bku')->orderBy('no_bku')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new BkuCollection($items, $totalItems, $page, $size, $totalPages)
            );
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['query', $field],
                        'msg' => $message,
                        'type' => 'validation',
                    ];
                }
            }
            return response()->json([
                'detail' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $BKU = DataBku::where('bku_id', $id)->first();

            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new BkuResource($BKU)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list_jenisbku()
    {
        try {
            $jenisBKU = MasterJenisBku::select('jenisbku_id', 'jenisbku_nama')->get();

            $data = $jenisBKU->map(function ($p) {
                return [
                    'jenisbku_nama' => $p->jenisbku_nama,
                    'jenisbku_id' => $p->jenisbku_id,
                ];
            })->toArray();

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => $data
            ], 200);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['query', $field],
                        'msg' => $message,
                        'type' => 'validation',
                    ];
                }
            }
            return response()->json([
                'detail' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(BkuRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tgl'] = now();

            [$noBKU, $noUrut] = $this->generateNoBku($data['jenis'], $data['tgl_bku']);
            $data['no_bku'] = $noBKU;
            $data['nourut_bku'] = $noUrut;

            $BKUNew = DataBku::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => new BkuResource($BKUNew),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function update(BkuRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $BKU = DataBku::where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($BKU->pad_id || $BKU->pad_tgl) {
                return response()->json([
                    'message' => 'Data cannot be edited because its already do kirim PAD.'
                ], 422);
            }

            $BKU->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data BKU',
                'data' => new BkuResource($BKU),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function validasi($id)
    {
        try {
            DB::beginTransaction();

            $BKU = DataBku::where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($BKU->tgl_valid) {
                return response()->json([
                    'message' => 'Data cannot be edited because its valid.'
                ], 422);
            }

            // UPDATE DATA
            // Jenis BKU: Mutasi Kas
            if ($BKU->jenis == 1) {
                DataRekeningKoran::where('tgl_rc', $BKU->tgl_bku)
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->whereRaw('COALESCE(kredit, 0) > 0')
                                ->whereNull('akunls_id');
                        })->orWhere(function ($q) {
                            $q->whereRaw('COALESCE(debit, 0) > 0')
                                ->where('akunls_id', 'LIKE', '20102');
                        });
                    })
                    ->update(['bku_id' => $id]);
            }
            // Jenis BKU: Pendapatan Kas
            if ($BKU->jenis == 2) {
                DataPenerimaanLayanan::where('tgl_buktibayar', $BKU->tgl_bku)
                    ->update(['bku_id' => $id]);
                DataPenerimaanLain::where('tgl_bayar', $BKU->tgl_bku)
                    ->update(['bku_id' => $id]);
                DataPenerimaanSelisih::where('tgl_setor', $BKU->tgl_bku)
                    ->update(['bku_id' => $id]);
            }
            // Jenis BKU: Pendapatan Langsung
            if ($BKU->jenis == 3) {
                DataRekeningKoran::where('tgl_rc', $BKU->tgl_bku)
                    ->whereRaw('COALESCE(kredit, 0) > 0')
                    ->whereNotNull('akunls_id')
                    ->update(['bku_id' => $id]);
            }

            // MOVE DATA TO RINCIAN BKU
            // Jenis BKU: Pendapatan Kas
            if ($BKU->jenis == 2) {
                DataRincianBku::insertUsing(
                    ['bku_id', 'akun_id', 'rek_id', 'uraian', 'jumlah', 'pendapatan', 'piutang', 'pdd'],
                    DB::table('bku_pendapatankas_v')
                        ->selectRaw('bku_id, akun_id, rek_id::numeric, uraian, jumlah, pendapatan, piutang, pdd')
                        ->where('bku_id', $id)
                );
            }
            // Jenis BKU: Pendapatan Langsung
            if ($BKU->jenis == 3) {
                DataRincianBku::insertUsing(
                    ['bku_id', 'akun_id', 'rek_id', 'uraian', 'jumlah', 'pendapatan', 'piutang', 'pdd'],
                    DB::table('bku_pendapatanls_v')
                        ->selectRaw('bku_id, akun_id, rek_id::numeric, uraian, jumlah, pendapatan, piutang, pdd')
                        ->where('bku_id', $id)
                );
            }

            // UPDATE TGL VALID
            $BKU->update(['tgl_valid' => now()]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil validasi data BKU'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function batalValidasi($id)
    {
        try {
            DB::beginTransaction();

            $BKU = DataBku::where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (!$BKU->tgl_valid) {
                return response()->json([
                    'message' => 'Data cannot be edited because its not valid.'
                ], 422);
            }

            // Set bku_id = null in all related table for BKU
            DataRekeningKoran::where('bku_id', $id)->update(['bku_id' => null]);
            DataPenerimaanLayanan::where('bku_id', $id)->update(['bku_id' => null]);
            DataPenerimaanLain::where('bku_id', $id)->update(['bku_id' => null]);
            DataPenerimaanSelisih::where('bku_id', $id)->update(['bku_id' => null]);

            // Delete BKU details
            DataRincianBku::where('bku_id', $id)->delete();

            // Reset tgl_valid in BKU table
            $BKU->update(['tgl_valid' => null]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil batal validasi data BKU'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function kirimPAD($id)
    {
        try {
            DB::beginTransaction();

            $BKU = DataBku::with('jenisBku')->where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (!$BKU->tgl_valid) {
                return response()->json([
                    'message' => 'Data cannot be edited because its not valid.'
                ], 422);
            }

            // Check pad token
            $padUrl = session('pad_url');
            $token = session('pad_token');
            $expiresAt = session('pad_token_expires');

            // If token is not set or its expired so get new token
            if (!$token || !$expiresAt || now()->gte($expiresAt)) {
                $token = $this->loginPAD();
                if (!$token) {
                    return response()->json(['error' => 'PAD login failed'], 500);
                }
            }

            // Set header
            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];

            // Get kas ke and kas dari
            $padKaske   = $BKU->jenisBku->bku_kaske;
            $padKasdari = $BKU->jenisBku->bku_kasdari;

            // Begin Send PAD
            if ($BKU->jenis == 2 || $BKU->jenis == 3) {
                // Set array rincian
                $rincian = $BKU->rincian->map(function ($rinci) {
                    return [
                        "id_keg"       => 902,
                        "id_koderek"   => $rinci->rek_id,
                        "jml"          => $rinci->jumlah - $rinci->pdd - $rinci->piutang,
                        "jml_dimuka"   => $rinci->pdd,
                        "jml_piutang"  => $rinci->piutang,
                        "ket"          => $rinci->akun_nama,
                    ];
                })->values()->toArray();

                // Set request body
                $body = [
                    "id_jenis"     => 2,
                    "id_kas"       => $padKaske,
                    "no_bukti"     => $BKU->no_bku,
                    "tgl_bukti"    => $BKU->tgl_bku,
                    "untuk"        => $BKU->uraian,
                    "is_sisa_kas"  => false,
                    "terima_rek"   => $rincian,
                ];

                // Send request
                $response = Http::withHeaders($headers)->post($padUrl .'/terima', $body);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Failed Terima PAD',
                        'detail'  => $response->json(),
                    ], $response->status());
                }

                $data = $response->json();

                if (empty($data['id'])) {
                    return response()->json([
                        'message' => 'Terima Response not valid: ID empty or null',
                    ], 422);
                }
                if (empty($data['terima_rek'])) {
                    return response()->json([
                        'message' => 'Terima Response not valid: terima_rek empty or null',
                    ], 422);
                }

                // Update data BKU
                $BKU->update([
                    'pad_id' => $data['id'],
                    'pad_tgl' => now(),
                ]);
                // Update rincian BKU
                foreach ($BKU->rincian as $index => $rinci) {
                    $rinci->update([
                        'pad_rinci' => $data['terima_rek'][$index]['id'],
                    ]);
                }
            }
            if ($BKU->jenis == 1 || $BKU->jenis == 9) {
                // Set request body
                $totalJumlah = $BKU->rincian->sum('jumlah');
                $body = [
                    "id_jenis"  => 2,
                    "no_bukti"  => $BKU->no_bku,
                    "tgl_bukti" => $BKU->tgl_bku,
                    "dr_kas"    => $padKasdari,
                    "ke_kas"    => $padKaske,
                    "jml"       => $totalJumlah,
                    "untuk"     => $BKU->uraian,
                ];

                // Send request
                $response = Http::withHeaders($headers)->post($padUrl .'/transfer-kas', $body);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Failed Transfer Kas PAD',
                        'detail'  => $response->json(),
                    ], $response->status());
                }

                $data = $response->json();

                if (empty($data['id'])) {
                    return response()->json([
                        'message' => 'Transfer Kas Response not valid: ID empty or null',
                    ], 422);
                }

                // Update data BKU
                $BKU->update([
                    'pad_id' => $data['id'],
                    'pad_tgl' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil kirim PAD data BKU'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function batalPAD($id)
    {
        try {
            DB::beginTransaction();

            $BKU = DataBku::with('jenisBku')->where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (!$BKU->pad_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its not send PAD.'
                ], 422);
            }

            // Check pad token
            $padUrl = session('pad_url');
            $token = session('pad_token');
            $expiresAt = session('pad_token_expires');

            // If token is not set or its expired so get new token
            if (!$token || !$expiresAt || now()->gte($expiresAt)) {
                $token = $this->loginPAD();
                if (!$token) {
                    return response()->json(['error' => 'PAD login failed'], 500);
                }
            }

            // Set header
            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];

            // Begin Delete PAD
            $padId = $BKU->pad_id;
            if ($BKU->jenis == 2 || $BKU->jenis == 3) {
                // Send request
                $response = Http::withHeaders($headers)->delete($padUrl .'/terima/'. $padId);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Failed Delete PAD',
                        'detail'  => $response->json(),
                    ], $response->status());
                }

                // Update data BKU
                $BKU->update([
                    'pad_id' => null,
                    'pad_tgl' => null,
                ]);
                // Update rincian BKU
                foreach ($BKU->rincian as $index => $rinci) {
                    $rinci->update([
                        'pad_rinci' => null,
                    ]);
                }
            }
            if ($BKU->jenis == 1 || $BKU->jenis == 9) {
                // Send request
                $response = Http::withHeaders($headers)->delete($padUrl .'/transfer-kas/'. $padId);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Failed Delete PAD',
                        'detail'  => $response->json(),
                    ], $response->status());
                }

                // Update data BKU
                $BKU->update([
                    'pad_id' => null,
                    'pad_tgl' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil batal PAD data BKU'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $BKU = DataBku::where('bku_id', $id)->first();
            if (!$BKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (strtolower($BKU->status) == 'validasi') {
                return response()->json([
                    'message' => 'Data cannot be edited because its status is validasi.'
                ], 422);
            }

            $BKU->delete();

            $rincianBKU = DataRincianBku::where('bku_id', $id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => "Berhasil menghapus data BKU"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function generateNoBku(int $jenis, string $tglBKU)
    {
        // ambil tahun & bulan dari tgl_bku
        $tahun = date('Y', strtotime($tglBKU));
        $bulan = date('n', strtotime($tglBKU)); // 1 - 12

        $romawi = [
            '', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII',
            'VIII', 'IX', 'X', 'XI', 'XII'
        ];
        $bulanRomawi = $romawi[$bulan];

        // cari max nourut_bku dengan Eloquent
        $maxNoUrut = DataBKU::where('jenis', $jenis)
            ->whereYear('tgl_bku', $tahun)
            ->whereMonth('tgl_bku', $bulan)
            ->max('nourut_bku');

        $newNoUrut = $maxNoUrut ? $maxNoUrut + 1 : 1;

        // format nomor bku -> contoh: BPN.2/0005/VII/2025
        $noBKU = sprintf("BPN.%d/%04d/%s/%d", $jenis, $newNoUrut, $bulanRomawi, $tahun);

        return [$noBKU, $newNoUrut];
    }

    function loginPAD()
    {
        // Get pad credentials
        $settings = Setting::whereIn('key', [
                'sync_pad_url',
                'sync_pad_user',
                'sync_pad_password',
                'sync_tahun'
            ])
            ->pluck('value', 'key');
        $padUrl      = rtrim($settings['sync_pad_url'], '/');
        $padUser     = $settings['sync_pad_user'];
        $padPassword = $settings['sync_pad_password'];
        $padTahun    = $settings['sync_tahun'];

        // Build request body
        $body = [
            "username"  => $padUser,
            "password"  => $padPassword,
            "aplikasi"  => "pad",
            "tahun"     => $padTahun,
        ];

        // Make request for get login token
        $url = $padUrl .'/login';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        if ($response->failed()) {
            \Log::error('PAD login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        // Get token and set in session
        $data  = $response->json();
        // If token is not exist in response
        if (!isset($data['token'])) {
            \Log::error('PAD login returned no token', ['response' => $data]);
            return null;
        }
        // Set token if exist
        $token = $data['token'];

        session([
            'pad_url' => $padUrl,
            'pad_token' => $token,
            'pad_token_expires' => now()->addSeconds($data['expires_in']),
        ]);

        return $token;
    }
}
