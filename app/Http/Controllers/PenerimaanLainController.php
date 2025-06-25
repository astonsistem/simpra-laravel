<?php

namespace App\Http\Controllers;

use App\Http\Resources\PenerimaanLainCollection;
use App\Http\Resources\PenerimaanLainResource;
use App\Models\DataPenerimaanLain;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PenerimaanLainController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'id' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'sumber_transaksi' => 'nullable|string',
                'akun_id' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $paramId = $request->input('id');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $uraian = $request->input('uraian');
            $sumberTransaksi = $request->input('sumber_transaksi');
            $akunId = $request->input('akun_id');

            $query = DataPenerimaanLain::query();
            $query->where('type', '!=', "BILLING SWA");
            $query->where('akun_id', '!=', 1010101);

            if (!empty($paramId)) {
                $query->where('id', $paramId);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bayar', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_bayar', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_bayar', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_bayar', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($sumberTransaksi)) {
                $query->where('sumber_transaksi', $sumberTransaksi);
            }
            if (!empty($akunId)) {
                $query->where('akun_id', $akunId);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bayar', 'desc')->orderBy('no_bayar', 'desc')->with('masterAkun')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PenerimaanLainCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function show(string $id)
    {
        try {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID must be a valid UUID format.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $penerimaanLain = DataPenerimaanLain::with('masterAkun')->where('id', $id)->first();

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PenerimaanLainResource($penerimaanLain)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getdata(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'id' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'sumber_transaksi' => 'nullable|string',
                'akun_id' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $paramId = $request->input('id');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $uraian = $request->input('uraian');
            $sumberTransaksi = $request->input('sumber_transaksi');
            $akunId = $request->input('akun_id');

            $query = DataPenerimaanLain::query();

            if (!empty($paramId)) {
                $query->where('id', $paramId);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bayar', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_bayar', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_bayar', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_bayar', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($sumberTransaksi)) {
                $query->where('sumber_transaksi', $sumberTransaksi);
            }
            if (!empty($akunId)) {
                $query->where('akun_id', $akunId);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bayar', 'desc')->orderBy('no_bayar', 'desc')->with('masterAkun')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PenerimaanLainCollection($items, $totalItems, $page, $size, $totalPages)
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


    public function statistik(Request $request)
    {
        $currentMonth = Carbon::now()->format('m');

        $sumPendapatan = DataPenerimaanLain::sumTotal('PENERIMAAN LAIN');
        $sumPendapatanCurrent = DataPenerimaanLain::sumTotal('PENERIMAAN LAIN', $currentMonth);
        $sumPendapatanBpjs = DataPenerimaanLain::sumTotal('PENERIMAAN LAIN', null, "PIUTANG");
        $sumPendapatanBpjsCurrent = DataPenerimaanLain::sumTotal('PENERIMAAN LAIN', $currentMonth, "PIUTANG");

        return response()->json([
            'total' => $sumPendapatan,
            'current' => $sumPendapatanCurrent,
            'bpjs' => $sumPendapatanBpjs,
            'bpjs_current' => $sumPendapatanBpjsCurrent,
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        try {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID must be a valid UUID format.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $penerimaanLain = DataPenerimaanLain::find($id);
            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $penerimaanLain->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menghapus data'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        //
    }
}
