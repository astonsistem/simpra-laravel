<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingKasirRequest;
use App\Http\Resources\BillingKasirCollection;
use App\Http\Resources\BillingKasirResource;
use App\Models\DataPenerimaanLayanan;
use App\Models\DataRekeningKoran;
use App\Models\Kasir;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BillingKasirController extends Controller
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
            $loket = $request->input('loket');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $caraPembayaran = $request->input('cara_pembayaran');
            $noClosingkasir = $request->input('no_closingkasir');

            $query = DataPenerimaanLayanan::query();

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
            if (!empty($loket)) {
                $query->where('loket_id', $loket);
            }
            if (!empty($uraian)) {
                $query->where('status_id', 'ILIKE', "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank_tujuan', 'ILIKE', "%$bank%");
            }
            if (!empty($caraPembayaran)) {
                $query->where('cara_pembayaran', $caraPembayaran);
            }
            if (!empty($noClosingkasir)) {
                $query->where('no_closingkasir', 'ILIKE', "%$noClosingkasir%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_buktibayar', 'desc')->orderBy('no_buktibayar', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new BillingKasirCollection($items, $totalItems, $page, $size, $totalPages)
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

            $billingKasir = DataPenerimaanLayanan::where('id', $id)->first();

            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new BillingKasirResource($billingKasir)
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
        // pendapatan_tersetor = get_sum_total_by_status(db,['5','6'])
        // kas_on_hand = get_sum_total_by_not_status(db,['5','6'], "TUNAI")


        return response()->json([
            'penerimaan' => "sum_penerimaan.total",
            'jumlah_penerimaan' => "jumlah_penerimaan",
            'penerimaan_tunai' => "penerimaan_tunai.total",
            'jumlah_penerimaan_tunai' => "penerimaan_tunai.count_total",
            'penerimaan_non_tunai' => "penerimaan_non_tunai.total",
            'jumlah_penerimaan_non_tunai' => "penerimaan_non_tunai.count_total",
            'kas_on_hand' => "kas_on_hand.total",
            'jumlah_kas_on_hand' => "kas_on_hand.count_total",
            'pendapatan_tersetor' => "pendapatan_tersetor.total",
            'jumlah_pendapatan_tersetor' => "pendapatan_tersetor.count_total"
        ]);
    }

    public function update(BillingKasirRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            $billingKasir = DataPenerimaanLayanan::where('tandabuktibayar_id', $id)->firstOrFail();

            if (!empty($data['loket_id'])) {
                $loket = Loket::where('id', $data['loket_id'])->first();
                if ($loket) {
                    $data['loket_nama'] = $loket->loket_nama;
                }
            }

            if (!empty($data['kasir_id'])) {
                $kasir = Kasir::where('id', $data['kasir_id'])->first();
                if ($kasir) {
                    $data['kasir_nama'] = $kasir->kasir_nama;
                }
            }

            $billingKasir->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data billing kasir',
                'data' => new BillingKasirResource($billingKasir),
            ], 200);
        } catch (\Exception $e) {
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
            $billingKasir = DataPenerimaanLayanan::where('tandabuktibayar_id', $id)->firstOrFail();
            if (!$billingKasir) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $tglBuktiBayar = $billingKasir->tgl_buktibayar;
            $bankTujuan = $billingKasir->bank_tujuan;
            $rcId = $billingKasir->rc_id;

            $rekeningKoran = DataRekeningKoran::where('rc_id', $rcId)
                ->where('tgl_rc', '>=', $tglBuktiBayar)
                ->where('bank_tujuan', $bankTujuan)
                ->get();

            $caraPembayaran = $billingKasir->cara_pembayaran;
            $totalSetor = 0;

            if (in_array($caraPembayaran, ['TUNAI', 'EDC'])) {
                // $totalSetor =  sum_total_setor(db, no_closing, cara_pembayaran);

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
                'message' => 'success',
                'data' => [
                    'penerimaan_pelayanan' => new BillingKasirResource($billingKasir),
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
        //
    }

    public function validasiFilterUraian(string $id)
    {
        //
    }

    public function validasiFilterJumlah(string $id)
    {
        //
    }

    public function updateValidasi()
    {
        //
    }

    public function cancelValidasi()
    {
        //
    }
}
