<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingSwaRequest;
use App\Http\Requests\ValidasiBillingKasirRequest;
use App\Http\Resources\BillingSwaCollection;
use App\Http\Resources\BillingSwaResource;
use App\Models\DataPenerimaanLain;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingSwaController extends Controller
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
                'sumber_transaksi' => 'nullable|string',
                'cara_pembayaran' => 'nullable|string',
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
            $sumberTransaksi = $request->input('sumber_transaksi');
            $caraPembayaran = $request->input('cara_pembayaran');

            $query = DataPenerimaanLain::query();
            $query->where('type', "BILLING SWA");

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
            if (!empty($caraPembayaran)) {
                $query->where('cara_pembayaran', $caraPembayaran);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bayar', 'asc')->orderBy('no_bayar', 'asc')->with('masterAkun')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new BillingSwaCollection($items, $totalItems, $page, $size, $totalPages)
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
            return response()->json(
                new BillingSwaResource($billingSwa)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistik(Request $request)
    {
        // sum_penerimaan = get_sum_billing_kasirs(db)
        // jumlah_penerimaan = count_billing_kasirs(db)
        // penerimaan_tunai = get_sum_total_by_payment_method(db,"TUNAI")
        // penerimaan_non_tunai = get_sum_total_by_not_payment_method(db,"TUNAI")       

        return response()->json([
            'total' => "sumPendapatan",
            'current' => "sumPendapatanCurrent",
            'ambulan' => "sumPendapatan",
            'swab' => "sumPendapatan",
        ]);
    }

    public function update(BillingSwaRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            unset($data['akun_data']);

            DB::beginTransaction();

            $billingSwa = DataPenerimaanLain::firstOrFail($id);
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
            $billingSwa = DataPenerimaanLain::where('id', $id)->firstOrFail();
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
                    $totalSetor = intval($billingSwa->total);
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
                    $totalSetor = intval($billingSwa->total);
                } else {
                    $totalSetor = intval($jumlahNetto);
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
            $billingSwa = DataPenerimaanLain::where('id', $id)->firstOrFail();
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

            $billingSwa = DataPenerimaanLain::where('id', $id)->firstOrFail();
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

            $billingSwa = DataPenerimaanLain::where('id', $id)->firstOrFail();
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

    public function updateValidasi(ValidasiBillingKasirRequest $request)
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

                DataPenerimaanLain::where('id', $billingSwaId)
                    ->update([
                        'rc_id'     => $rcIdValue,
                    ]);

                $billingSwaTableName = (new DataPenerimaanLain())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLainSubquery = DB::table($billingSwaTableName)
                    ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
                    ->where('rc_id', $rcId);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_lain'   => DB::raw('(' . $klarifLainSubquery->toSql() . ')'),
                        'akun_id'       => '1010101',
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

                DataPenerimaanLain::where('id', $billingSwaId)
                    ->update([
                        'rc_id'     => null,
                    ]);

                $billingSwaTableName = (new DataPenerimaanLain())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLainSubquery = DB::table($billingSwaTableName)
                    ->select(DB::raw('COALESCE(SUM(total - admin_kredit + selisih), 0)'))
                    ->where('rc_id', $rcId);

                DB::table($rekeningKoranTableName)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_lain' => DB::raw('(' . $klarifLainSubquery->toSql() . ')'),
                        'akun_id'        => DB::raw(
                            "CASE WHEN (" . $klarifLainSubquery->toSql() . ") = 0 THEN NULL ELSE akun_id END"
                        ),
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
