<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDataSelisihRequest;
use App\Http\Resources\DataSelisihCollection;
use App\Http\Resources\DataSelisihResource;
use App\Models\DataPenerimaanSelisih;
use App\Models\DataSelisihView;
use App\Models\Kasir;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DataSelisihController extends Controller
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
                // 'tglSetor' => 'nullable|string',
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
            // $tglSetor = $request->input('tglSetor');
            $noSetor = $request->input('noSetor');
            $nominal = $request->input('nominal');
            $rekeningDpa = $request->input('rekeningDpa');
            $loketKasir = $request->input('loketKasir');
            $caraPembayaran = $request->input('caraPembayaran');
            $bank = $request->input('bank');
            $jenis = $request->input('jenis');

            $query = DataSelisihView::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_bayar', (int)$tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "TANGGAL") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bukti', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "BULANAN") {
                $startMonth = Carbon::parse($tglAwal)->format('m');
                $endMonth = Carbon::parse($tglAkhir)->format('m');
                $query->whereBetween('tgl_bukti', [$startMonth, $endMonth]);
            }
            if (!empty($noBukti)) {
                $query->where('no_buktibayar', 'ILIKE', "%$noBukti%");
            }
            if (!empty($tglBukti)) {
                $query->whereDate('tgl_bukti', $tglBukti);
            }
            // if (!empty($tglSetor)) {
            //     $query->where('tgl_setor', $tglSetor);
            // }
            if (!empty($noSetor)) {
                $query->where('no_setor', 'ILIKE', "%$noSetor%");
            }
            if (!empty($nominal)) {
                $query->where('nilai', 'ILIKE', "%$nominal%");
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
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bukti', 'desc')->orderBy('tgl_bukti', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new DataSelisihCollection($items, $totalItems, $page, $size, $totalPages)
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

            $dataSelisih = DataSelisihView::where('id', $id)->first();

            if (!$dataSelisih) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new DataSelisihResource($dataSelisih)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateDataSelisihRequest $request, string $id)
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

            // Seharusnya ini adalah update, bukan create.
            // Cek kembali fungsionalitas yang diinginkan.
            $dataSelisih = DataPenerimaanSelisih::find($id);

            if (!$dataSelisih) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $data = $request->validated();

            $kasir = null;
            $loket = null;
            if ($request->has('kasir_id')) {
                $kasir = Kasir::find($request->input('kasir_id'));
                $data['kasir_nama'] = $kasir ? $kasir->nama : null;
            }
            if ($request->has('loket_id')) {
                $loket = Loket::find($request->input('loket_id'));
                $data['loket_nama'] = $loket ? $loket->nama : null;
            }

            // Metode 'create' pada instance model yang ada tidak valid.
            // Seharusnya menggunakan 'update' atau 'fill' lalu 'save'.
            $dataSelisih->update($data);

            return response()->json(
                new DataSelisihResource($dataSelisih)
            );
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['body', $field],
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
