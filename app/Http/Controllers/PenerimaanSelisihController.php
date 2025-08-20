<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePenerimaanSelisihRequest;
use App\Http\Requests\UpdatePenerimaanSelisihRequest;
use App\Http\Requests\ValidasiPenerimaanSelisihRequest;
use App\Http\Resources\PenerimaanSelisihCollection;
use App\Http\Resources\PenerimaanSelisihResource;
use App\Models\DataPenerimaanSelisih;
use App\Models\DataRekeningKoran;
use App\Models\Kasir;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "TANGGAL") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_setor', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "BULANAN") {
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
                'jumlah_netto' => $data['jumlah'] - $data['admin_kredit'],
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

    public function update(UpdatePenerimaanSelisihRequest $request, string $id)
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

            $penerimaanSelisih->update($data);

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

    public function validasi(string $id)
    {
        try {
            $penerimaanSelisih = DataPenerimaanSelisih::where('id', $id)->firstOrFail();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$penerimaanSelisih) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $penerimaanSelisih->tgl_buktibayar;
            $bankTujuan = $penerimaanSelisih->bank_tujuan;
            $rcId = $penerimaanSelisih->rc_id;

            $rekeningKoran = DataRekeningKoran::getTanggalRc($rcId, $tglBuktiBayar, $bankTujuan);

            $noClosing = $penerimaanSelisih->no_closingkasir;
            $caraPembayaran = $penerimaanSelisih->cara_pembayaran;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                $totalSetor =  DataPenerimaanSelisih::sumTotalSetor($noClosing, $caraPembayaran);

                $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($totalSetor) {
                    return $koran->kredit == $totalSetor;
                });
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($penerimaanSelisih->total ?? 0) -
                    ($penerimaanSelisih->admin_kredit ?? 0) +
                    ($penerimaanSelisih->selisih ?? 0);

                $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($jumlahNetto) {
                    return $koran->kredit == $jumlahNetto;
                });

                $totalSetor = $jumlahNetto;
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_selisih' => $penerimaanSelisih,
                    'rekening_koran' => $rekeningKoran,
                    'total_setor' => $totalSetor
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateValidasi(ValidasiPenerimaanSelisihRequest $request)
    {
        $data = $request->validated();
        $penerimaanSelisihId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($penerimaanSelisihId, $rcId) {
                $penerimaanSelisih = DataPenerimaanSelisih::where('id', $penerimaanSelisihId)
                    ->first();

                if (!$penerimaanSelisih) {
                    throw new \Exception('Penerimaan selisih tidak ditemukan atau status tidak valid.');
                }

                $rcIdValue = ($rcId === 0 || $rcId === '0') ? null : $rcId;

                DataPenerimaanSelisih::where('id', $penerimaanSelisihId)
                    ->update([
                        'rc_id'     => $rcIdValue,
                    ]);

                $penerimaanSelisihTableName = (new DataPenerimaanSelisih())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLayananSubquery = DB::table($penerimaanSelisihTableName)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_layanan' => DB::raw('(' . $klarifLayananSubquery->toSql() . ')'),
                        'akun_id'        => '1010102',
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil validasi Penerimaan selisih',
                'status'  => 200,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan validasi.',
                'errors'  => $e->errors(),
                'status'  => 422,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi Penerimaan selisih.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }

    public function cancelValidasi(ValidasiPenerimaanSelisihRequest $request)
    {
        $data = $request->validated();
        $penerimaanSelisihId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($penerimaanSelisihId, $rcId) {
                $penerimaanSelisih = DataPenerimaanSelisih::where('id', $penerimaanSelisihId)->first();

                if (!$penerimaanSelisih) {
                    throw new \Exception('Penerimaan selisih tidak ditemukan atau status tidak valid.');
                }

                DataPenerimaanSelisih::where('id', $penerimaanSelisihId)
                    ->update([
                        'rc_id'     => null,
                    ]);

                $penerimaanSelisihTableName = (new DataPenerimaanSelisih())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLayananSubquery = DB::table($penerimaanSelisihTableName)
                    ->select(DB::raw('COALESCE(SUM(total - admin_kredit + selisih), 0)'))
                    ->where('rc_id', $rcId);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_layanan' => DB::raw('(' . $klarifLayananSubquery->toSql() . ')'),
                        'akun_id'        => DB::raw(
                            "CASE WHEN (" . $klarifLayananSubquery->toSql() . ") = 0 THEN NULL ELSE akun_id END"
                        ),
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil membatalkan validasi Penerimaan selisih',
                'status'  => 200,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan validasi.',
                'errors'  => $e->errors(),
                'status'  => 422,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan validasi Penerimaan selisih.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }

    public function setor() {}
}
