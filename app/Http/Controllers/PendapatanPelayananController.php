<?php

namespace App\Http\Controllers;

use App\Http\Resources\PendapatanPelayananCollection;
use App\Http\Resources\PendapatanPelayananResource;
use App\Http\Requests\PendapatanPelayananRequest;
use App\Models\DataPendapatanPelayanan;
use App\Models\CaraBayar;
use App\Models\Penjamin;
use App\Models\Instalasi;
use App\Models\Kasir;
use App\Models\Loket;
use App\Models\DataPenerimaanLayanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PendapatanPelayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'jenis_pelayanan' => 'nullable|string',
                'no_pendaftaran' => 'nullable|string',
                'no_rm' => 'nullable|string',
                'nama' => 'nullable|string',
                'cara_bayar' => 'nullable|int',
                'penjamin' => 'nullable|int',
                'status' => 'nullable|boolean',
                'penjamin_lebih_1' => 'nullable|boolean',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $jenisPelayanan = $request->input('jenis_pelayanan');
            $noPendaftaran = $request->input('no_pendaftaran');
            $noRM = $request->input('no_rekam_medik');
            $nama = $request->input('pasien_nama');
            $caraBayar = $request->input('carabayar_nama');
            $penjamin = $request->input('penjamin_nama');
            $status = $request->input('is_valid');
            $penjaminLebih1 = $request->input('is_penjaminlebih1');

            $query = DataPendapatanPelayanan::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($jenisPelayanan)) {
                $query->where('jenis_tagihan', 'ILIKE', "%$jenisPelayanan%");
            }
            if (!empty($noPendaftaran)) {
                $query->where('no_pendaftaran', 'ILIKE', "%$noPendaftaran%");
            }
            if (!empty($noRM)) {
                $query->where('no_rekam_medik', 'ILIKE', "%$noRM%");
            }
            if (!empty($nama)) {
                $query->where('pasien_nama', 'ILIKE', "%$nama%");
            }
            if (!empty($caraBayar)) {
                $query->where('carabayar_nama', 'ILIKE', "%$caraBayar%");
            }
            if (!empty($penjamin)) {
                $query->where('penjamin_nama', 'ILIKE', "%$penjamin%");
            }
            if (!empty($status)) {
                $query->where('is_valid', (bool) $status);
            }
            if (!empty($penjaminLebih1)) {
                $query->where('is_penjaminlebih1', (bool) $status);
            }

            $sortField = $request->input('sortField', 'tgl_pendaftaran');
            $sortOrder = $request->input('sortOrder', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PendapatanPelayananCollection($items, $totalItems, $page, $size, $totalPages)
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

            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();

            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PendapatanPelayananResource($pendapatanPelayanan)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistik()
    {
        // sum_pendapatan = get_sum_pendapatan(db)
        // jumlah_pasien = get_count_pasien(db)
        // klaim = get_sum_pendapatan_by_status_id(db,2)
        // verif = get_sum_pendapatan_by_status_id(db,3)
        // terima = get_sum_pendapatan_by_status_id(db,4)
        // setor = get_sum_pendapatan_by_status_id(db,5)

        return response()->json([
            'pendapatan' => "sum_pendapatan.total",
            'jumlah_pasien' => "jumlah_pasien.total",
            'pendapatan_klaim' => "klaim.total",
            'pendapatan_verif' => "verif.total",
            'pendapatan_terima' => "terima.total",
            'pendapatan_setor' => "setor.total"
        ], 200);
    }

    public function update(PendapatanPelayananRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($pendapatanPelayanan->is_valid == true) {
                return response()->json([
                    'message' => 'Data cannot be edited because its valid.'
                ], 422);
            }

            $caraBayar = CaraBayar::where('carabayar_id', $data['carabayar_id'])->first();
            if ($caraBayar) {
                $data['carabayar_nama'] = $caraBayar->carabayar_nama;
            }
            $penjamin = Penjamin::where('penjamin_id', $data['penjamin_id'])->first();
            if ($penjamin) {
                $data['penjamin_nama'] = $penjamin->penjamin_nama;
            }
            $instalasi = Instalasi::where('instalasi_id', $data['instalasi_id'])->first();
            if ($instalasi) {
                $data['instalasi_nama'] = $instalasi->instalasi_nama;
            }
            $loket = Loket::where('loket_id', $data['loket_id'])->first();
            if ($loket) {
                $data['loket_nama'] = $loket->loket_nama;
            }
            $kasir = Kasir::where('kasir_id', $data['kasir_id'])->first();
            if ($kasir) {
                $data['kasir_nama'] = $kasir->kasir_nama;
            }

            $pendapatanPelayanan->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data pendapatan pelayanan',
                'data' => new PendapatanPelayananResource($pendapatanPelayanan),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }


    public function tarik(string $id)
    {
        try {
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($pendapatanPelayanan->is_valid == true) {
                return response()->json([
                    'message' => 'Data cannot be edited because its valid.'
                ], 422);
            }

            if (strtolower($pendapatanPelayanan->jenis_tagihan) == 'rawat jalan') {
                $simpraTable = 'simpra_pendapatanjalan_ft';
            }
            if (strtolower($pendapatanPelayanan->jenis_tagihan) == 'rawat inap') {
                $simpraTable = 'simpra_pendapataninap_ft';
            }

            $pendapatanPelayananSiesta = (new DataPendapatanPelayanan)->setTable($simpraTable)->where('pendaftaran_id', $pendapatanPelayanan->pendaftaran_id)->first();
            if (!$pendapatanPelayananSiesta) {
                return response()->json([
                    'message' => 'Data Pendapatan Pelayanan Siesta Not found.'
                ], 404);
            }

            $pendapatanPelayanan->update($pendapatanPelayananSiesta->toArray());

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function sinkron_fase1(string $id)
    {
        try {
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($pendapatanPelayanan->is_valid == true) {
                return response()->json([
                    'message' => 'Data cannot be edited because its valid.'
                ], 422);
            }

            $total_sharing = $pendapatanPelayanan->total_sharing;
            $total_dijamin = $pendapatanPelayanan->total_dijamin;
            $pendapatan    = $pendapatanPelayanan->pendapatan;
            $pdd           = $pendapatanPelayanan->pdd;
            $piutang       = $pendapatanPelayanan->piutang;

            if (($total_sharing + $total_dijamin) == ($pendapatan + $pdd + $piutang)) {
                $pendapatanPelayanan->status_fase1 = 'Valid';
            } else {
                if ($total_sharing === ($pendapatan + $pdd)) {
                    if ($piutang === $total_dijamin) {
                        $pendapatanPelayanan->status_fase1 = 'Koreksi Pendapatan';
                    } else {
                        $pendapatanPelayanan->status_fase1 = 'Koreksi Piutang';
                    }
                } else {
                    $pendapatanPelayanan->status_fase1 = 'Koreksi Tagihan';
                }
            }

            $pendapatanPelayanan->save();

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function sinkron_fase2(string $id)
    {
        try {
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($pendapatanPelayanan->is_valid == true) {
                return response()->json([
                    'message' => 'Data cannot be edited because its valid.'
                ], 422);
            }

            $penerimaanLayanan = DataPenerimaanLayanan::selectRaw("
                    pendaftaran_id,
                    SUM(CASE WHEN LOWER(klasifikasi) = 'pendapatan' THEN total ELSE 0 END) as pendapatan,
                    SUM(CASE WHEN LOWER(klasifikasi) = 'pdd' THEN total ELSE 0 END) as pdd,
                    SUM(CASE WHEN LOWER(klasifikasi) = 'piutang' THEN total ELSE 0 END) as piutang,
                    SUM(admin_kredit) as bea_admin,
                    SUM(total) as total
                ")
                ->where('metode_bayar', 'ILIKE', '%langsung%')
                ->where('pendaftaran_id', $pendapatanPelayanan->pendaftaran_id)
                ->groupBy('pendaftaran_id')
                ->first();
            if (!$penerimaanLayanan) {
                return response()->json([
                    'message' => 'Data Penerimaan Layanan not found'
                ], 404);
            }

            if ($pendapatanPelayanan->pendapatan != $penerimaanLayanan->pendapatan || $pendapatanPelayanan->pdd != $penerimaanLayanan->pdd) {
                $pendapatanPelayanan->status_fase2 = 'Koreksi Pendapatan';
            } elseif ($pendapatanPelayanan->piutang != $penerimaanLayanan->piutang) {
                $pendapatanPelayanan->status_fase2 = 'Koreksi Piutang';
            } else {
                $pendapatanPelayanan->status_fase2 = 'Valid';
            }

            $pendapatanPelayanan->biaya_admin = $penerimaanLayanan->bea_admin;
            if ($penerimaanLayanan->total != 0) {
                $pendapatanPelayanan->status = 'bayar';
            }

            $pendapatanPelayanan->save();

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function validasi(string $id)
    {
        try {
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $id)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $pendapatanPelayanan->update([
                'is_valid' => strtolower($pendapatanPelayanan->status_fase1) == 'valid' && strtolower($pendapatanPelayanan->status_fase2) == 'valid'
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
