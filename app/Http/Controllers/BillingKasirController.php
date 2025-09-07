<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kasir;
use App\Models\Loket;
use App\Models\MasterStatus;
use Illuminate\Http\Request;
use App\Models\DataRekeningKoran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DataPenerimaanLayanan;
use App\Http\Requests\BillingKasirRequest;
use App\Http\Resources\BillingKasirResource;
use App\Http\Resources\BillingKasirCollection;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\BillingKasirFormResource;
use App\Actions\BillingKasir\ValidasiBillingKasir;
use App\Http\Requests\ValidasiBillingKasirRequest;

class BillingKasirController extends Controller
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
            $size = $request->input('size', 100) ?? 100;
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

            $query = DataPenerimaanLayanan::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_buktibayar', (int)$tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir && $periode == "TANGGAL")) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($tglAwal) && !empty($tglAkhir) && $periode === "BULANAN") {
                $startMonth = Carbon::parse($tglAwal)->format('m');
                $endMonth = Carbon::parse($tglAkhir)->format('m');
                $query->whereBetween('bulan_pelayanan', [$startMonth, $endMonth]);
            }
            if (!empty($noBayar)) {
                $query->where('no_buktibayar', 'ILIKE', "%$noBayar%");
            }
            if (!empty($tglBayar)) {
                $query->where('tgl_buktibayar', $tglBayar);
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
                $query->where('tgl_pelayanan', $tglDokumen);
            }
            if (!empty($sumberTransaksi)) {
                $query->where('jenis_tagihan', $sumberTransaksi);
            }
            if (!empty($instalasi)) {
                $query->where('instalasi_nama', 'ILIKE', "%$instalasi%");
            }
            if (!empty($metodeBayar)) {
                $query->where('metode_bayar', 'ILIKE', "%$metodeBayar%");
            }
            if (!empty($caraBayar)) {
                $query->where('carabayar_nama', 'ILIKE', "%$caraBayar%");
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

            $query->with('rekeningKoran')->orderBy('tgl_buktibayar', 'desc')->orderBy('no_buktibayar', 'desc');

            return BillingKasirResource::collection(
                $query->paginate($size)
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

    public function show(Request $request, string $id)
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

            $billingKasir = DataPenerimaanLayanan::where('id', $id)->first();

            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $billingKasir->load('rekeningKoran');

            if($request->has('action') && $request->action === 'validasi') {
                return new BillingKasirResource($billingKasir);
            }

            return new BillingKasirFormResource($billingKasir);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistik()
    {
        $sumPenerimaan = DataPenerimaanLayanan::sumTotal();
        $jumlahPenerimaan = DataPenerimaanLayanan::countTotal();
        $penerimaanTunai = DataPenerimaanLayanan::sumTotalByPaymentMethod("TUNAI");
        $jumlahPenerimaanTunai = DataPenerimaanLayanan::countTotalByPaymentMethod("TUNAI");
        $penerimaanNonTunai = DataPenerimaanLayanan::sumTotalByNotPaymentMethod("TUNAI");
        $jumlahPenerimaanNonTunai = DataPenerimaanLayanan::countTotalByNotPaymentMethod("TUNAI");
        $pendapatanTersetor = DataPenerimaanLayanan::sumTotalByStatus(['5', '6']);
        $kasOnHand = DataPenerimaanLayanan::sumTotalByNotStatus(['5', '6'], "TUNAI");


        return response()->json([
            'penerimaan' => $sumPenerimaan,
            'jumlah_penerimaan' => $jumlahPenerimaan,
            'penerimaan_tunai' => $penerimaanTunai,
            'jumlah_penerimaan_tunai' => $jumlahPenerimaanTunai,
            'penerimaan_non_tunai' => $penerimaanNonTunai,
            'jumlah_penerimaan_non_tunai' => $jumlahPenerimaanNonTunai,
            'kas_on_hand' => $kasOnHand->total,
            'jumlah_kas_on_hand' => $kasOnHand->count_total,
            'pendapatan_tersetor' => $pendapatanTersetor->total,
            'jumlah_pendapatan_tersetor' => $pendapatanTersetor->count_total
        ]);
    }

    public function update(BillingKasirRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $billingKasir = DataPenerimaanLayanan::where('id', $id)->firstOrFail();

            $billingKasir->update($data);

            if($billingKasir->status_id == MasterStatus::SETOR_ID && $billingKasir->rc_id)
            {
                (new ValidasiBillingKasir())->handle($billingKasir->rc_id);
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil memperbarui data billing kasir',
                'data' => new BillingKasirFormResource($billingKasir),
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

    public function destroy($id)
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

            $billingKasir = DataPenerimaanLayanan::find($id);
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $billingKasir->delete();

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
            $billingKasir = DataPenerimaanLayanan::where('id', $id)->firstOrFail();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingKasir->tgl_buktibayar;
            $bankTujuan = $billingKasir->bank_tujuan;
            $rcId = $billingKasir->rc_id;

            $rekeningKoran = DataRekeningKoran::getTanggalRc($rcId, $tglBuktiBayar, $bankTujuan);

            $noClosing = $billingKasir->no_closingkasir;
            $caraPembayaran = $billingKasir->cara_pembayaran;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                $totalSetor =  DataPenerimaanLayanan::sumTotalSetor($noClosing, $caraPembayaran);

                $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($totalSetor) {
                    return $koran->kredit == $totalSetor;
                });
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($billingKasir->total ?? 0) -
                    ($billingKasir->admin_kredit ?? 0) +
                    ($billingKasir->selisih ?? 0);

                $rekeningKoran = $rekeningKoran->filter(function ($koran) use ($jumlahNetto) {
                    return $koran->kredit == $jumlahNetto;
                });

                $totalSetor = $jumlahNetto;
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_pelayanan' => $billingKasir,
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
            $billingKasir = DataPenerimaanLayanan::where('id', $id)->firstOrFail();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingKasir->tgl_buktibayar;
            $bankTujuan = $billingKasir->bank_tujuan;
            $rcId = $billingKasir->rc_id;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilter($tglBuktiBayar, $bankTujuan);

            $noClosing = $billingKasir->no_closingkasir;
            $caraPembayaran = $billingKasir->cara_pembayaran;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                $totalSetor =  DataPenerimaanLayanan::sumTotalSetor($noClosing, $caraPembayaran);
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($billingKasir->total ?? 0) -
                    ($billingKasir->admin_kredit ?? 0) +
                    ($billingKasir->selisih ?? 0);
                $totalSetor = $jumlahNetto;
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_pelayanan' => $billingKasir,
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

            $billingKasir = DataPenerimaanLayanan::where('id', $id)->firstOrFail();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingKasir->tgl_buktibayar;
            $bankTujuan = $billingKasir->bank_tujuan;
            $uraian = $billingKasir->rc_id;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterUraian($tglBuktiBayar, $bankTujuan, $uraian);

            $noClosing = $billingKasir->no_closingkasir;
            $caraPembayaran = $billingKasir->cara_pembayaran;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                $totalSetor =  DataPenerimaanLayanan::sumTotalSetor($noClosing, $caraPembayaran);
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($billingKasir->total ?? 0) -
                    ($billingKasir->admin_kredit ?? 0) +
                    ($billingKasir->selisih ?? 0);
                $totalSetor = $jumlahNetto;
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_pelayanan' => $billingKasir,
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

            $billingKasir = DataPenerimaanLayanan::where('id', $id)->firstOrFail();
            $rekeningKoran = [];
            $totalSetor = 0;
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingKasir->tgl_buktibayar;
            $bankTujuan = $billingKasir->bank_tujuan;

            $rekeningKoran = DataRekeningKoran::getTanggalRcFilterJumlah($tglBuktiBayar, $bankTujuan, $jumlah);

            $noClosing = $billingKasir->no_closingkasir;
            $caraPembayaran = $billingKasir->cara_pembayaran;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                $totalSetor =  DataPenerimaanLayanan::sumTotalSetor($noClosing, $caraPembayaran);
            } elseif (in_array($caraPembayaran, ['QRIS', 'TRANSFER'])) {
                $jumlahNetto =
                    ($billingKasir->total ?? 0) -
                    ($billingKasir->admin_kredit ?? 0) +
                    ($billingKasir->selisih ?? 0);
                $totalSetor = $jumlahNetto;
            }

            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    'penerimaan_pelayanan' => $billingKasir,
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
        $billingKasirId = $data['id'];
        $rcId = $data['rc_id'];

        $currentUserId = auth()->user()->id;

        try {
            DB::transaction(function () use ($billingKasirId, $rcId, $currentUserId) {
                $billingKasir = DataPenerimaanLayanan::where('id', $billingKasirId)
                    ->where(function ($query) {
                        $query->where('status_id', '!=', MasterStatus::SETOR_ID)
                            ->orWhereNull('status_id');
                    })
                    ->first();

                if (!$billingKasir) {
                    throw new \Exception('Penerimaan layanan tidak ditemukan atau status tidak valid.');
                }

                $rcIdValue = ($rcId === 0 || $rcId === '0') ? null : $rcId;

                $billingKasir->update([
                        'rc_id'     => $rcIdValue,
                        'status_id' => MasterStatus::SETOR_ID,
                        'status'    => MasterStatus::SETOR,
                        'monev_id'  => $currentUserId,
                    ]);

                (new ValidasiBillingKasir )->handle($rcId);
            });

            return response()->json([
                'message' => 'Berhasil validasi penerimaan layanan',
                'status'  => 200,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan validasi.',
                'errors'  => $e->errors(),
                'status'  => 422,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in updateValidasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi penerimaan layanan.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }

    public function cancelValidasi(ValidasiBillingKasirRequest $request)
    {
        $data = $request->validated();
        $billingKasirId = $data['id'];
        $rcId = $data['rc_id'];

        try {
            DB::transaction(function () use ($billingKasirId, $rcId) {
                $billingKasir = DataPenerimaanLayanan::where('id', $billingKasirId)
                    ->first();

                if (!$billingKasir) {
                    throw new \Exception('Penerimaan layanan tidak ditemukan atau status tidak valid.');
                }

                $billingKasir->update([
                        'status_id' => null,
                        'status'    => null,
                        'monev_id'  => null,
                        'rc_id'     => null
                    ]);

                $billingKasirTableName = (new DataPenerimaanLayanan())->getTable();
                $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

                $klarifLayanan = DB::table($billingKasirTableName)
                    ->where('rc_id', $rcId)
                    ->select(DB::raw('COALESCE(SUM(total - admin_kredit + selisih), 0) as sum'))
                    ->value('sum');

                $rkQuery = DB::table($rekeningKoranTableName)->where('rc_id', $rcId);
                $akunId = $rkQuery->value('akun_id');

                $rkQuery->update([
                        'klarif_layanan' => $klarifLayanan,
                        'akun_id'        => $klarifLayanan == 0 ? null : $akunId,
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil membatalkan validasi penerimaan layanan',
                'status'  => 200,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan validasi.',
                'errors'  => $e->errors(),
                'status'  => 422,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in cancelValidasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan validasi penerimaan layanan.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }
}
