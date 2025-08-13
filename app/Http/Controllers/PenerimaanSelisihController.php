<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePenerimaanSelisihRequest;
use App\Http\Resources\PenerimaanSelisihCollection;
use App\Http\Resources\PenerimaanSelisihResource;
use App\Models\DataPenerimaanSelisih;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

            $penerimaanSelisih = DataPenerimaanSelisih::where('id', $id)->first();

            if (!$penerimaanSelisih) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PenerimaanSelisihResource($penerimaanSelisih)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreatePenerimaanSelisihRequest $request)
    {
        try {
            $data = $request->validated();

            $penerimaanSelisih = DataPenerimaanSelisih::create([
                ...$data,
                'total' => $data['jumlah'] - $data['admin_kredit'],
            ]);

            return response()->json(
                new PenerimaanSelisihResource($penerimaanSelisih),
                201
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

    public function update(Request $request, string $id)
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

            $penerimaanSelisih = DataPenerimaanSelisih::find($id);

            if (!$penerimaanSelisih) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $request->validate([
                'no_buktibayar' => 'required|string|max:255',
                'tgl_buktibayar' => 'required|date',
                'tgl_setor' => 'required|date',
                'no_setor' => 'required|string|max:255',
                'jumlah' => 'required|numeric',
                'rek_id' => 'required|string|max:255',
                'loket_nama' => 'required|string|max:255',
                'cara_pembayaran' => 'required|string|max:50',
                'bank_tujuan' => 'nullable|string|max:255',
                'jenis' => 'nullable|string|max:50',
            ]);

            $penerimaanSelisih->update($request->all());

            return response()->json(
                new PenerimaanSelisihResource($penerimaanSelisih)
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

    public function destroy(string $id)
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

            $penerimaanSelisih = DataPenerimaanSelisih::find($id);

            if (!$penerimaanSelisih) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $penerimaanSelisih->delete();

            return response()->json([
                'message' => 'Data penerimaan selisih berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list($request)
    {
        try {
            $request->validate([
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

            $penerimaanSelisih = $query->orderBy('tgl_setor', 'desc')->orderBy('tgl_setor', 'desc')->get();

            return response()->json(
                PenerimaanSelisihResource::collection($penerimaanSelisih)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validasi() {}

    public function cancelValidasi() {}

    public function setor() {}
}
