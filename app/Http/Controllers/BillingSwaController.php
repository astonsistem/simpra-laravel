<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingSwaRequest;
use App\Http\Requests\ValidasiBillingKasirRequest;
use App\Http\Requests\ValidasiBillingSwaRequest;
use App\Http\Resources\BillingSwaCollection;
use App\Http\Resources\BillingSwaResource;
use App\Http\Resources\BillingSwaSimpleResource;
use App\Models\DataPenerimaanLain;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BillingSwaController extends Controller
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
                'pasien' => 'nullable|string',
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
            $size = $request->input('size', 10) ?? 10;
            $tahunPeriode = $request->input('tahunPeriode');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');
            $periode = $request->input('periode');
            $noBayar = $request->input('noBayar');
            $tglBayar = $request->input('tglBayar');
            $pasien = $request->input('pasien');
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
            $query->whereIn('sumber_transaksi', function ($sub) {
                $sub->select('sumber_id')
                    ->from('master_sumbertransaksi')
                    ->where('sumber_jenis', 'Billing 118');
            });

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_bayar', (int)$tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "TANGGAL") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bayar', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "BULANAN") {
                $startDate = Carbon::parse($tglAwal)->startOfMonth();
                $endDate = Carbon::parse($tglAkhir)->endOfMonth();
                $query->whereBetween('tgl_bayar', [$startDate, $endDate]);
            }
            if (!empty($noBayar)) {
                $query->where('no_bayar', 'ILIKE', "%$noBayar%");
            }
            if (!empty($tglBayar)) {
                $query->where('tgl_bayar', $tglBayar);
            }
            if (!empty($pasien)) {
                $query->where('pasien_nama', 'ILIKE', "%$pasien%");
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
            if (!empty($sumberTransaksi)) {
                $query->where('sumber_transaksi', $sumberTransaksi);
            }
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

            $query->when(!empty($params['pihak3']), function ($q) use ($params) {
                $q->where('pihak3', 'ILIKE', '%' . $params['pihak3'] . '%');
            });

            $query->when(!empty($params['rekeningDpa']), function ($q) use ($params) {
                $q->whereHas('rekeningDpa', function ($q) use ($params) {
                    $q->where('rek_nama', 'ILIKE', '%' . $params['rekeningDpa'] . '%')
                      ->orWhere('rek_id', $params['rekeningDpa']);
                });
            });

            $query->when(!empty($params['jumlahBrutoMin']), function ($q) use ($params) {
                $operator = empty($params['jumlahBrutoMax']) ? '=' : '>=';
                $q->where('total', $operator, $params['jumlahBrutoMin']);
            });

            $query->when(!empty($params['jumlahBrutoMax']), function ($q) use ($params) {
                $operator = empty($params['jumlahBrutoMin']) ? '=' : '<=';
                $q->where('total', $operator, $params['jumlahBrutoMax']);
            });

            // Filter Netto
            $query->when(!empty($params['jumlahNettoMin']), function ($q) use ($params) {
                $operator = empty($params['jumlahNettoMax']) ? '=' : '>=';
                $q->where('jumlah_netto', $operator, $params['jumlahNettoMin']);
            });

            $query->when(!empty($params['jumlahNettoMax']), function ($q) use ($params) {
                $operator = empty($params['jumlahNettoMin']) ? '=' : '<=';
                $q->where('jumlah_netto', $operator, $params['jumlahNettoMax']);
            });

            if($request->has('validated')) {
                $query->where(function($query) use ($params) {
                    $validated = $params['validated'] ?? null;
                    if($validated == '1') {
                        $query->whereNotNull('rc_id')->where('rc_id', '>', 0);
                    } elseif($validated == '0') {
                        $query->whereNull('rc_id');
                    }
                });
            }

            $query->orderBy('tgl_bayar', 'desc')->orderBy('no_bayar', 'desc')->with('masterAkun');

            return  BillingSwaResource::collection($query->paginate( $size));

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

            $billingSwa = DataPenerimaanLain::with('masterAkun')->where('id', $id)->first();

            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return new BillingSwaSimpleResource($billingSwa);
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

        $sumPendapatan = DataPenerimaanLain::sumTotal('BILLING SWA');
        $sumPendapatanCurrent = DataPenerimaanLain::sumTotal('BILLING SWA', $currentMonth);
        $sumPendapatan118 = DataPenerimaanLain::sumTotal('BILLING SWA', $currentMonth, "118");
        $sumPendapatanSwab = DataPenerimaanLain::sumTotal('BILLING SWA', $currentMonth, "SWAB");

        return response()->json([
            'total' => $sumPendapatan,
            'current' => $sumPendapatanCurrent,
            'ambulan' => $sumPendapatan118,
            'swab' => $sumPendapatanSwab,
        ]);
    }

    public function update(BillingSwaRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            DB::beginTransaction();

            $billingSwa = DataPenerimaanLain::where('id', $id)->first();

            if (!$billingSwa) {
                throw new \Exception('Penerimaan lain tidak ditemukan.', 404);
            }

            $billingSwa->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil memperbarui data billing swa',
                'data' => new BillingSwaResource($billingSwa),
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

            $billingSwa = DataPenerimaanLain::find($id);
            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $billingSwa->delete();

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
            $billingSwa = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingSwa->tgl_bayar;
            $bankTujuan = $billingSwa->bank_tujuan;
            $rcId = $billingSwa->rc_id;
            $caraPembayaran = $billingSwa->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRc($rcId, $tglBuktiBayar, $bankTujuan);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = floatval($billingSwa->total);
                    $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($totalSetor) {
                        return $koran->kredit == $totalSetor;
                    });
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($billingSwa->total ?? 0) -
                    ($billingSwa->admin_kredit ?? 0) +
                    ($billingSwa->selisih ?? 0);

                if (empty($rcId)) {
                    $totalSetor = floatval($billingSwa->total);
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
                    'penerimaan_lain' => $billingSwa,
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
            $billingSwa = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBayar = $billingSwa->tgl_bayar;
            $bankTujuan = $billingSwa->bank_tujuan;
            $rcId = $billingSwa->rc_id;
            $caraPembayaran = $billingSwa->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilter($tglBayar, $bankTujuan);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $jumlahNetto =
                        ($billingSwa->total ?? 0) -
                        ($billingSwa->admin_kredit ?? 0) +
                        ($billingSwa->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $billingSwa,
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
            $uraian = $request->query('uraian');

            $billingSwa = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingSwa->tgl_bayar;
            $bankTujuan = $billingSwa->bank_tujuan;
            $uraian = $billingSwa->rc_id;
            $caraPembayaran = $billingSwa->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterUraian($tglBuktiBayar, $bankTujuan, $uraian);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $jumlahNetto =
                        ($billingSwa->total ?? 0) -
                        ($billingSwa->admin_kredit ?? 0) +
                        ($billingSwa->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $billingSwa,
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
            $jumlah = $request->query('jumlah');

            $billingSwa = DataPenerimaanLain::where('id', $id)->first();
            $rekeningKoran = [];
            $totalSetor = 0;

            if (!$billingSwa) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBayar = $billingSwa->tgl_bayar;
            $bankTujuan = $billingSwa->bank_tujuan;
            $caraPembayaran = $billingSwa->cara_pembayaran;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterJumlah($tglBayar, $bankTujuan, $jumlah);

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $totalSetor = DataPenerimaanLain::sumTotalByRcId($rcId);
                }
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                if (empty($rcId)) {
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $jumlahNetto =
                        ($billingSwa->total ?? 0) -
                        ($billingSwa->admin_kredit ?? 0) +
                        ($billingSwa->selisih ?? 0);
                    $totalSetor = $jumlahNetto;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_lain' => $billingSwa,
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

    public function updateValidasi(ValidasiBillingSwaRequest $request)
    {
        $data = $request->validated();
        $billingSwaId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($billingSwaId, $rcId) {
                $billingSwa = DataPenerimaanLain::where('id', $billingSwaId)->first();

                if (!$billingSwa) {
                    throw new \Exception('Penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $rcIdValue = ($rcId === 0 || $rcId === '0') ? null : $rcId;

                $billingSwa->update([
                        'rc_id'     => $rcIdValue,
                    ]);

                $billingKasirTableName = (new DataPenerimaanLain())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLain = DB::table($billingKasirTableName)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Lain: " . $klarifLain);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_lain' => $klarifLain,
                        'akun_id'     => '1010101',
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

    public function cancelValidasi(ValidasiBillingKasirRequest $request)
    {
        $data = $request->validated();
        $billingSwaId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($billingSwaId, $rcId) {
                $billingSwa = DataPenerimaanLain::where('id', $billingSwaId)
                    ->first();

                if (!$billingSwa) {
                    throw new \Exception('penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $billingSwa->update([
                        'rc_id'     => null,
                    ]);

                $billingKasirTableName = (new DataPenerimaanLain())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLain = DB::table($billingKasirTableName)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Lain: " . $klarifLain);
                $akun = DB::table($billingKasirTableName)->where('rc_id', $rcId)->value('akun_id');

                DB::table($rekeningKoranTableName)
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
