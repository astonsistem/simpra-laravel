<?php

namespace App\Http\Controllers;

use App\Actions\BillingKasir\ValidasiBillingKasir;
use App\Http\Requests\PenerimaanLainRequest;
use App\Http\Requests\ValidasiPenerimaanLainRequest;
use App\Http\Requests\ValidasiCancelPenerimaanLainRequest;
use App\Http\Resources\PenerimaanLainCollection;
use App\Http\Resources\PenerimaanLainResource;
use App\Models\DataPenerimaanLain;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PenerimaanLainController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tahunPeriode' => 'nullable|string',
                'tglAwal' => 'nullable|string',
                'tglAkhir' => 'nullable|string',
                'periode' => 'nullable|string',
                'noBayar' => 'nullable|string',
                'tglBayar' => 'nullable|string',
                'pihak3' => 'nullable|string',
                'uraian' => 'nullable|string',
                'noDokumen' => 'nullable|string',
                'tglDokumen' => 'nullable|string',
                'sumberTransaksi' => 'nullable|string',
                'instalasi' => 'nullable|string',
                'metodeBayar' => 'nullable|string',
                'caraBayar' => 'nullable|string',
                'rekeningDpa' => 'nullable|string',
                'bank' => 'nullable|string',
                'jumlahBruto' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahunPeriode');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');
            $periode = $request->input('periode');
            $noBayar = $request->input('noBayar');
            $tglBayar = $request->input('tglBayar');
            $pihak3 = $request->input('pihak3');
            $uraian = $request->input('uraian');
            $noDokumen = $request->input('noDokumen');
            $tglDokumen = $request->input('tglDokumen');
            $sumberTransaksi = $request->input('sumberTransaksi');
            $instalasi = $request->input('instalasi');
            $metodeBayar = $request->input('metodeBayar');
            $caraBayar = $request->input('caraBayar');
            $rekeningDpa = $request->input('rekeningDpa');
            $bank = $request->input('bank');
            $jumlahNetto = $request->input('jumlahNetto');

            $query = DataPenerimaanLain::query();

            if( config('app.env') == 'production' ) {
                $query->whereIn('sumber_transaksi', function ($sub) {
                    $sub->select('sumber_id')
                        ->from('master_sumbertransaksi')
                        ->where('sumber_jenis', 'Lainnya');
                });
            }

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_bayar', (int)$tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "TANGGAL") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bayar', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "BULANAN") {
                $startMonth = Carbon::parse($tglAwal)->startOfMonth();
                $endMonth = Carbon::parse($tglAkhir)->endOfMonth();
                $query->whereBetween('tgl_bayar', [$startMonth, $endMonth]);
            }
            if (!empty($noBayar)) {
                $query->where('no_bayar', 'ILIKE', "%$noBayar%");
            }
            if (!empty($tglBayar)) {
                $query->where('tgl_bayar', $tglBayar);
            }
            if (!empty($pihak3)) {
                $query->where('pihak3', 'ILIKE', "%$pihak3%");
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($noDokumen)) {
                $query->where('no_dokumen', 'ILIKE', "%$noDokumen%");
            }
            if (!empty($tglDokumen)) {
                $query->where('tgl_dokumen', $tglDokumen);
            }
            // if (!empty($sumberTransaksi)) {
            //     $query->where('sumber_transaksi', $sumberTransaksi);
            // }
            if (!empty($instalasi)) {
                $query->where('instalasi_nama', 'ILIKE', "%$instalasi%");
            }
            if (!empty($metodeBayar)) {
                $query->where('metode_pembayaran', 'ILIKE', "%$metodeBayar%");
            }
            if (!empty($caraBayar)) {
                $query->where('cara_pembayaran', 'ILIKE', "%$caraBayar%");
            }
            if (!empty($rekeningDpa)) {
                $query->where('rek_dpa', 'ILIKE', "%$rekeningDpa%");
            }
            if (!empty($bank)) {
                $query->where('bank_tujuan', 'ILIKE', "%$bank%");
            }
            if (!empty($jumlahNetto)) {
                $query->where('jumlah_netto', 'LIKE', "%$jumlahNetto%");
            }

            $totalItems = $query->count();
            $items = $query->with('masterAkun')
                ->orderBy('tgl_bayar', 'desc')
                ->skip(($page - 1) * $size)
                ->take($size)
                ->get();

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
            // if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            //     return response()->json([
            //         'detail' => [
            //             [
            //                 'loc' => ['path', 'id'],
            //                 'msg' => 'ID must be a valid UUID format.',
            //                 'type' => 'validation'
            //             ]
            //         ]
            //     ], 422);
            // }

            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            $penerimaanLain->load('masterAkun');
            return new PenerimaanLainResource($penerimaanLain);

        } catch (\Exception $e) {
            Log::error('Error in PenerimaanLainController@show: ' . $e->getMessage());
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

    public function statistik()
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

    public function list(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'id' => 'nullable|string',
                'tgl_awal' => 'required|string',
                'tgl_akhir' => 'required|string',
                'bulan_awal' => 'required|string',
                'bulan_akhir' => 'required|string',
                'year' => 'required|string',
                'periode' => 'required|string',
                'uraian' => 'required|string',
                'sumber_transaksi' => 'required|string',
                'akun_id' => 'required|string',
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

    public function store(PenerimaanLainRequest $request)
    {
        $data = $request->validated();

        $type = "PENERIMAAN LAIN";
        $countInsert = 0;
        $countUpdate = 0;
        $checkData = DataPenerimaanLain::where('no_bayar', $data['no_bayar'])->first();

        if ($checkData) {
            unset($data['akun_data']);

            DB::beginTransaction();
            $penerimaanLain = DataPenerimaanLain::firstOrFail($checkData->id);
            $penerimaanLain->update($data);
            DB::commit();

            $countUpdate++;
        } else {
            if ($data['tgl_bayar']) {
                $tglBayar = Carbon::createFromFormat('Y-m-d', $data['tgl_bayar'])->addDay()->toDateString();
            }
            $total = $data['total'] != null ? floatval($data['total']) : floatval(0);
            $adminKredit = $data['admin_kredit'] != null ? floatval($data['admin_kredit']) : floatval(0);
            $selisih = $data['selisih'] != null ? floatval($data['selisih']) : floatval(0);
            $pendapatan = $data['pendapatan'] != null ? floatval($data['pendapatan']) : floatval(0);
            $jumlahNetto = $total - $adminKredit + $selisih;
            $piutang = $total - $adminKredit + $selisih;
            $bankTujuan = !empty($data['bank_tujuan']) ? $data['bank_tujuan'] : "TUNAI";

            DB::beginTransaction();
            $penerimaanLain = DataPenerimaanLain::create([
                ...$data,
                'tgl_bayar' => $tglBayar,
                'pendapatan' => $pendapatan,
                'piutang' => $piutang,
                'bank_tujuan' => $bankTujuan,
                'jumlah_netto' => $jumlahNetto,
                'type' => $type,
            ]);
            DB::commit();

            $countInsert++;
        }
        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => [
                'insert' => $countInsert,
                'update' => $countUpdate,
                'data' => $penerimaanLain
            ],
        ], 200);
    }

    public function createData(PenerimaanLainRequest $request)
    {
        $data = $request->validated();

        $checkData = DataPenerimaanLain::where('no_bayar', $data['no_bayar'])->first();

        if ($checkData) {
            return response()->json([
                'message' => "Data dengan id $checkData->id sudah tersedia."
            ], 409);
        }

        $type = "PENERIMAAN LAIN";
        if ($data['tgl_bayar']) {
            $tglBayar = Carbon::createFromFormat('Y-m-d', $data['tgl_bayar'])->addDay()->toDateString();
        }
        $total = $data['total'] != null ? floatval($data['total']) : floatval(0);
        $adminKredit = $data['admin_kredit'] != null ? floatval($data['admin_kredit']) : floatval(0);
        $selisih = $data['selisih'] != null ? floatval($data['selisih']) : floatval(0);
        $pendapatan = $data['pendapatan'] != null ? floatval($data['pendapatan']) : floatval(0);
        $jumlahNetto = $total - $adminKredit + $selisih;
        $piutang = $total - $adminKredit + $selisih;
        $bankTujuan = !empty($data['bank_tujuan']) ? $data['bank_tujuan'] : "TUNAI";

        DB::beginTransaction();
        $penerimaanLain = DataPenerimaanLain::create([
            ...$data,
            'tgl_bayar' => $tglBayar,
            'pendapatan' => $pendapatan,
            'piutang' => $piutang,
            'bank_tujuan' => $bankTujuan,
            'jumlah_netto' => $jumlahNetto,
            'type' => $type,
        ]);
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => $penerimaanLain,
        ], 200);
    }

    public function update(PenerimaanLainRequest $request, string $id)
    {
        try {
            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();

            if (!$penerimaanLain) {
                throw new \Exception("Data dengan id $id tidak ditemukan.", 404);
            }

            $data = $request->validated();

            DB::beginTransaction();

            $penerimaanLain->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil memperbarui data billing swa',
                'data' => new PenerimaanLainResource($penerimaanLain),
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

    public function updateEditData(PenerimaanLainRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            unset($data['akun_data']);

            DB::beginTransaction();

            $penerimaanLain = DataPenerimaanLain::firstOrFail($id);
            $penerimaanLain->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil memperbarui data billing swa',
                'data' => new PenerimaanLainResource($penerimaanLain),
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

    public function validasi(string $id)
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

            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $penerimaanLain->tgl_bayar;
            $bankTujuan = $penerimaanLain->bank_tujuan;
            $rcId = $penerimaanLain->rc_id;
            $caraPembayaran = $penerimaanLain->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRc($rcId, $tglBuktiBayar, $bankTujuan);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                    $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($totalSetor) {
                        return $koran->kredit == $totalSetor;
                    });
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($penerimaanLain->total ?? 0) -
                    ($penerimaanLain->admin_kredit ?? 0) +
                    ($penerimaanLain->selisih ?? 0);

                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $totalSetor = floatval($jumlahNetto);
                }

                $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($jumlahNetto) {
                    return $koran->kredit == $jumlahNetto;
                });
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $penerimaanLain,
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

    public function validasiFilter(string $id)
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

            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBayar = $penerimaanLain->tgl_bayar;
            $bankTujuan = $penerimaanLain->bank_tujuan;
            $rcId = $penerimaanLain->rc_id;
            $caraPembayaran = $penerimaanLain->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilter($tglBayar, $bankTujuan);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $jumlahNetto =
                        ($penerimaanLain->total ?? 0) -
                        ($penerimaanLain->admin_kredit ?? 0) +
                        ($penerimaanLain->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $penerimaanLain,
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

    public function validasiFilterUraian(Request $request, string $id)
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

            $uraian = $request->query('uraian');

            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $penerimaanLain->tgl_bayar;
            $bankTujuan = $penerimaanLain->bank_tujuan;
            $uraian = $penerimaanLain->rc_id;
            $caraPembayaran = $penerimaanLain->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterUraian($tglBuktiBayar, $bankTujuan, $uraian);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $jumlahNetto =
                        ($penerimaanLain->total ?? 0) -
                        ($penerimaanLain->admin_kredit ?? 0) +
                        ($penerimaanLain->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $penerimaanLain,
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

    public function validasiFilterJumlah(Request $request, string $id)
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

            $jumlah = $request->query('jumlah');

            $penerimaanLain = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;

            if (!$penerimaanLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBayar = $penerimaanLain->tgl_bayar;
            $bankTujuan = $penerimaanLain->bank_tujuan;
            $caraPembayaran = $penerimaanLain->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterJumlah($tglBayar, $bankTujuan, $jumlah);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($penerimaanLain->total);
                } else {
                    $jumlahNetto =
                        ($penerimaanLain->total ?? 0) -
                        ($penerimaanLain->admin_kredit ?? 0) +
                        ($penerimaanLain->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $penerimaanLain,
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

    public function updateValidasi(ValidasiPenerimaanLainRequest $request)
    {
        $data = $request->validated();
        $penerimaanLainId = $data['id'];
        $rcId = $data['rc_id'];
        $akunId = $data['akun_id'] ?? '1010101';

        try {
            DB::transaction(function () use ($penerimaanLainId, $rcId, $akunId) {
                $penerimaanLain = DataPenerimaanLain::where('id', $penerimaanLainId)->first();

                if (!$penerimaanLain) {
                    throw new \Exception('Penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $rcIdValue = ($rcId === 0 || $rcId === '0') ? null : $rcId;

                DataPenerimaanLain::where('id', $penerimaanLainId)
                    ->update([
                        'rc_id'     => $rcIdValue,
                    ]);

                $penerimaanLainTableName = (new DataPenerimaanLain())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLayanan = DB::table($penerimaanLainTableName)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Layanan: " . $klarifLayanan);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_layanan' => $klarifLayanan,
                        'akun_id'     => $akunId || '1010101',
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil validasi penerimaan lain',
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
                'message' => 'Terjadi kesalahan saat validasi penerimaan lain.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }

    public function cancelValidasi(ValidasiCancelPenerimaanLainRequest $request)
    {
        $data = $request->validated();
        $penerimaanLainId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($penerimaanLainId, $rcId) {
                $penerimaanLain = DataPenerimaanLain::where('id', $penerimaanLainId)
                    ->first();

                if (!$penerimaanLain) {
                    throw new \Exception('penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $penerimaanLain->update([
                        'rc_id'     => null,
                    ]);

                $modelTable = (new DataPenerimaanLain())->getTable();
                $rekeningTable = (new DataRekeningKoran())->getTable();

                $klarifLain = DB::table($modelTable)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Lain: " . $klarifLain);
                $akun = DB::table($modelTable)->where('rc_id', $rcId)->value('akun_id');

                DB::table($rekeningTable)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_lain' => $klarifLain,
                        'akun_id'     => $akun,
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil membatalkan validasi penerimaan lain',
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
                'message' => 'Terjadi kesalahan saat membatalkan validasi penerimaan lain.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }
}
