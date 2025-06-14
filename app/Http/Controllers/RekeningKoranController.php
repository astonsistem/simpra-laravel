<?php

namespace App\Http\Controllers;

use App\Http\Resources\RekeningKoranCollection;
use App\Http\Resources\RekeningKoranResource;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RekeningKoranController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|integer',
                'debit' => 'nullable|integer',
                'kredit' => 'nullable|integer',
                'kualifikasi' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $debit = $request->input('debit');
            $kredit = $request->input('kredit');
            $kualifikasi = $request->input('kualifikasi');

            $query = DataRekeningKoran::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_rc', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_rc', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_rc', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', "ILIKE", "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($debit)) {
                $query->where('debit', $debit);
            }
            if (!empty($kredit)) {
                $query->where('kredit$kredit', $kredit);
            }
            if (!empty($kualifikasi) && $kualifikasi == 1) {
                $query->whereNotNull('debit');
            } elseif (!empty($kualifikasi) && $kualifikasi == 2) {
                $query->whereNotNull('kredit');
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_rc', 'desc')->orderBy('no_rc', 'asc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new RekeningKoranCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function sum(Request $request)
    {
        try {
            $request->validate([
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|integer',
                'debit' => 'nullable|integer',
                'kredit' => 'nullable|integer',
                'kualifikasi' => 'nullable|integer',
            ]);

            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $debit = $request->input('debit');
            $kredit = $request->input('kredit');
            $kualifikasi = $request->input('kualifikasi');

            $query = DataRekeningKoran::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_rc', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_rc', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_rc', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', "ILIKE", "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($debit)) {
                $query->where('debit', $debit);
            }
            if (!empty($kredit)) {
                $query->where('kredit$kredit', $kredit);
            }
            if (!empty($kualifikasi) && $kualifikasi == 1) {
                $query->whereNotNull('debit');
            } elseif (!empty($kualifikasi) && $kualifikasi == 2) {
                $query->whereNotNull('kredit');
            }

            $items = $query->orderBy('tgl_rc', 'desc')->orderBy('no_rc', 'asc')->get();

            $totalDebit = 0;
            $totalKredit = 0;
            foreach ($items as $key => $item) {
                $totalDebit += $item->debit ?? 0;
                $totalKredit += $item->kredit ?? 0;
            }

            return response()->json([
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
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

    public function show(string $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new RekeningKoranResource($rekeningKoran)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistik()
    {
        // currentMonth = datetime.now().month

        // debit_mutasi_all = get_sum_debit(db)
        // kredit_mutasi_all = get_sum_kredit(db)


        // debit_mutasi_now = get_sum_debit(db, currentMonth)
        // kredit_mutasi_now = get_sum_kredit(db, currentMonth)

        // debit_mutasi_mandiri = get_sum_debit(db, currentMonth, "MANDIRI")
        // kredit_mutasi_mandiri = get_sum_kredit(db, currentMonth, "MANDIRI")

        // debit_mutasi_bca = get_sum_debit(db, currentMonth, "BCA")
        // kredit_mutasi_bca = get_sum_kredit(db, currentMonth, "BCA")

        // debit_mutasi_jatim = get_sum_debit(db, currentMonth, "JATIM")
        // kredit_mutasi_jatim = get_sum_kredit(db, currentMonth, "JATIM")

        return response()->json([
            "all" => [
                "debit" => 'debit_mutasi_all.total',
                "kredit" => 'kredit_mutasi_all.total',
                "selisih" => 'debit_mutasi_all.total - kredit_mutasi_all.total',
            ],
            "now" => [
                "debit" => 'debit_mutasi_now.total',
                "kredit" => 'kredit_mutasi_now.total',
                "selisih" => 'debit_mutasi_now.total - kredit_mutasi_now.total',
            ],
            "mandiri" => [
                "debit" => 'debit_mutasi_mandiri.total',
                "kredit" => 'kredit_mutasi_mandiri.total',
                "selisih" => 'debit_mutasi_mandiri.total - kredit_mutasi_mandiri.total',
            ],
            "bca" => [
                "debit" => 'debit_mutasi_bca.total',
                "kredit" => 'kredit_mutasi_bca.total',
                "selisih" => 'debit_mutasi_bca.total - kredit_mutasi_bca.total',
            ],
            "jatim" => [
                "debit" => 'debit_mutasi_jatim.total',
                "kredit" => 'kredit_mutasi_jatim.total',
                "selisih" => 'debit_mutasi_jatim.total - kredit_mutasi_jatim.total',
            ],
        ], 200);
    }

    public function pbUncheck()
    {
        $rekeningKoran = DataRekeningKoran::whereNull('pb')->where('bank', '!=', "JATIM")->get();

        if (!$rekeningKoran) {
            return response()->json([
                'message' => 'Not found.'
            ], 404);
        }
        return response()->json(
            $rekeningKoran
        );
    }

    public function pbCheck(string $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(new RekeningKoranResource($rekeningKoran), 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buktiSetor(string $id)
    {
        return "";
    }

    public function update(Request $request, string $id)
    {
        //
    }
}
