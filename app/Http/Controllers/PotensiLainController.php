<?php

namespace App\Http\Controllers;

use App\Http\Requests\PotensiLainRequest;
use App\Http\Resources\PotensiLainCollection;
use App\Http\Resources\PotensiLainResource;
use App\Models\DataPenerimaanLain;
use App\Models\DokumenNonlayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLainController extends Controller
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
                'pihak3' => 'nullable|string',
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
            $pihak3 = $request->input('pihak3');

            $dokumenNonlayananTable = (new DokumenNonlayanan())->getTable();
            $dataPenerimaanLainTable = (new DataPenerimaanLain())->getTable();

            $query = DokumenNonlayanan::query()
                ->select(
                    "{$dokumenNonlayananTable}.*",
                    DB::raw("COALESCE(SUM({$dataPenerimaanLainTable}.jumlah_netto), 0) as terbayar")
                )
                ->leftJoin(
                    $dataPenerimaanLainTable,
                    function ($join) use ($dokumenNonlayananTable, $dataPenerimaanLainTable) {
                        $join->on(DB::raw("CAST({$dokumenNonlayananTable}.id AS VARCHAR)"), '=', "{$dataPenerimaanLainTable}.piutanglain_id");
                    }
                )
                ->groupBy("{$dokumenNonlayananTable}.id");

            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "tanggal") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_dokumen', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_dokumen', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_dokumen', '<=', (int)$bulanAkhir);
            }
            if (!empty($uraian)) {
                $query->where('uraian', $uraian);
            }
            if (!empty($pihak3)) {
                $query->where('pihak3', $pihak3);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('id', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PotensiLainCollection($items, $totalItems, $page, $size, $totalPages)
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

            $potensiLain = DokumenNonlayanan::where('transaksi_id', $id)->first();

            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PotensiLainResource($potensiLain)
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
        # sum_pendapatan = get_sum_penerimaan_lain(db, "PENERIMAAN LAIN")
        # sum_pendapatan_current = get_sum_penerimaan_lain(db, "PENERIMAAN LAIN", currentMonth)
        # sum_pendapatan_bpjs = get_sum_penerimaan_lain(db, "PENERIMAAN LAIN", sumber_transaksi="PIUTANG")
        # sum_pendapatan_bpjs_current = get_sum_penerimaan_lain(db, "PENERIMAAN LAIN", currentMonth, "PIUTANG")

        return response()->json([
            "total" => 0,
            "total_pay" => 0,
            "total_current" => 0,
            "total_current_pay" => 0,
        ], 200);
    }

    public function store(PotensiLainRequest $request)
    {
        try {
            $data = $request->validated();

            $checkData = DokumenNonlayanan::where('no_closing', $data['no_closing'])->first();
            if ($checkData) {
                return response()->json([
                    'message' => "No Closing sudah ada."
                ], 400);
            }

            DB::beginTransaction();

            $potensiLain = DokumenNonlayanan::create($data);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menambahkan data sync',
                'data' => new PotensiLainResource($potensiLain),
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

    public function update(PotensiLainRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            $potensiLain = DokumenNonlayanan::find($id);
            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            DB::beginTransaction();

            $potensiLain->update($data);

            DB::commit();

            return response()->json(new PotensiLainResource($potensiLain), 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (empty($id)) {
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

            $potensiLain = DokumenNonlayanan::find($id);
            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            DB::beginTransaction();

            $potensiLain->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => "Berhasil menghapus data potensi lain"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function terima(string $id)
    {
        //
    }
}
