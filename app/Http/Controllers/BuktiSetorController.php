<?php

namespace App\Http\Controllers;

use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BuktiSetorController extends Controller
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
                'loket' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|string',
                'cara_pembayaran' => 'nullable|string',
                'no_closingkasir' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $bank = $request->input('bank');

            $query = DataRekeningKoran::query();

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
            if (!empty($bank)) {
                $query->where('bank_tujuan', 'ILIKE', "%$bank%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_buktibayar', 'desc')->orderBy('no_buktibayar', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json();
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

    public function statistik()
    {
        $currentMonth = Carbon::now()->format('m');

        $sum = DataRekeningKoran::sumBuktiSetor($currentMonth);
        $count = DataRekeningKoran::countBuktiSetor($currentMonth);
        $sumCurrent = DataRekeningKoran::sumBuktiSetorCurrent($currentMonth);
        $countCurrent = DataRekeningKoran::countBuktiSetorCurrent($currentMonth);

        return response()->json([
            'sum' => $sum,
            'count' => $count,
            'sum_current' => $sumCurrent,
            'count_current' => $countCurrent,
        ]);
    }
}
