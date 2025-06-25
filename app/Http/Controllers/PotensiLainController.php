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
            $uraian = $request->input('uraian');
            $pihak3 = $request->input('pihak3');

            $query = DokumenNonlayanan::query();

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
