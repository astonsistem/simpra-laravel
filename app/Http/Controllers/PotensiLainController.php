<?php

namespace App\Http\Controllers;

use App\Http\Resources\PotensiLainCollection;
use App\Models\DokumenNonlayanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            $loket = $request->input('loket');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $caraPembayaran = $request->input('cara_pembayaran');
            $noClosingkasir = $request->input('no_closingkasir');

            $query = DokumenNonlayanan::query();

            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "tanggal") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereBetween('bulan_pelayanan', [(int)$bulanAwal, (int)$bulanAkhir]);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_buktibayar', (int)$year);
            }
            if (!empty($loket)) {
                $query->where('loket_id', $loket);
            }
            if (!empty($uraian)) {
                $query->where('status_id', "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank_tujuan', 'ILIKE', "%$bank%");
            }
            if (!empty($caraPembayaran)) {
                $query->where('cara_pembayaran', $caraPembayaran);
            }
            if (!empty($noClosingkasir)) {
                $query->where('no_closingkasir', 'ILIKE', "%$noClosingkasir%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_buktibayar', 'desc')->orderBy('no_buktibayar', 'desc')->get();

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
        //
    }

    public function statistik()
    {
        return response()->json([
            "total" => 0,
            "total_pay" => 0,
            "total_current" => 0,
            "total_current_pay" => 0,
        ], 200);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
