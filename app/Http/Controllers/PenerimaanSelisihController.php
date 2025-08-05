<?php

namespace App\Http\Controllers;

use App\Http\Resources\PenerimaanSelisihCollection;
use App\Models\DataPenerimaanSelisih;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PenerimaanSelisihController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tahunPeriode' => 'nullable|string',
                'periode' => 'nullable|string',
                'tglAwal' => 'nullable|string',
                'tglAkhir' => 'nullable|string',
                'noBukti' => 'nullable|string',
                'tglBukti' => 'nullable|string',
                'tglSetor' => 'nullable|string',
                'noSetor' => 'nullable|string',
                'nominal' => 'nullable|string',
                'rekeningDpa' => 'nullable|string',
                'loketKasir' => 'nullable|string',
                'caraPembayaran' => 'nullable|string',
                'bank' => 'nullable|string',
                'jenis' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahunPeriode');
            $periode = $request->input('periode');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');
            $noBukti = $request->input('noBukti');
            $tglBukti = $request->input('tglBukti');
            $tglSetor = $request->input('tglSetor');
            $noSetor = $request->input('noSetor');
            $nominal = $request->input('nominal');
            $rekeningDpa = $request->input('rekening$rekeningDpa');
            $loketKasir = $request->input('loketKasir');
            $caraPembayaran = $request->input('caraPembayaran');
            $bank = $request->input('bank');
            $jenis = $request->input('jenis');

            $query = DataPenerimaanSelisih::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_bayar', (int)$tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "tanggal") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_setor', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "bulan") {
                $startMonth = Carbon::parse($tglAwal)->format('m');
                $endMonth = Carbon::parse($tglAkhir)->format('m');
                $query->whereBetween('tgl_setor', [$startMonth, $endMonth]);
            }
            if (!empty($noBukti)) {
                $query->where('no_buktibayar', 'ILIKE', "%$noBukti%");
            }
            if (!empty($tglBukti)) {
                $query->where('tgl_buktibayar', $tglBukti);
            }
            if (!empty($tglSetor)) {
                $query->where('tgl_setor', $tglSetor);
            }
            if (!empty($noSetor)) {
                $query->where('no_setor', 'ILIKE', "%$noSetor%");
            }
            if (!empty($nominal)) {
                $query->where('jumlah', 'ILIKE', "%$nominal%");
            }
            if (!empty($rekeningDpa)) {
                $query->where('rek_id', 'ILIKE', "%$rekeningDpa%");
            }
            if (!empty($loketKasir)) {
                $query->where('loket_nama', 'ILIKE', "%$loketKasir%");
            }
            if (!empty($caraPembayaran)) {
                $query->where('cara_pembayaran', $caraPembayaran);
            }
            if (!empty($bank)) {
                $query->where('bank_tujuan', 'ILIKE', "%$bank%");
            }
            if (!empty($jenis)) {
                $query->where('jenis', 'ILIKE', "%$jenis%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_setor', 'desc')->orderBy('tgl_setor', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PenerimaanSelisihCollection($items, $totalItems, $page, $size, $totalPages)
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
}
