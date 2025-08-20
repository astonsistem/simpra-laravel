<?php

namespace App\Http\Controllers;

use App\Http\Resources\PendapatanPelayananCollection;
use App\Http\Resources\PendapatanPelayananResource;
use App\Models\DataPendapatanPelayanan;
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
                'cara_bayar' => 'nullable|string',
                'penjamin' => 'nullable|string',
                'status' => 'nullable|boolean',
                'penjamin_lebih_1' => 'nullable|boolean',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $jenisPelayanan = $request->input('jenis_pelayanan');
            $noPendaftaran = $request->input('no_pendaftaran');
            $noRM = $request->input('no_rm');
            $nama = $request->input('nama');
            $caraBayar = $request->input('cara_bayar');
            $penjamin = $request->input('penjamin');
            $status = $request->input('status');
            $penjaminLebih1 = $request->input('penjamin_lebih_1');

            $query = DataPendapatanPelayanan::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($jenisPelayanan)) {
                $query->where('jenis_tagihan', $jenisPelayanan);
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
                $query->where('carabayar_id', $caraBayar);
            }
            if (!empty($penjamin)) {
                $query->where('penjamin_id', $penjamin);
            }
            if (!empty($status)) {
                $query->where('is_valid', (bool) $status);
            }
            if (!empty($penjaminLebih1)) {
                $query->where('is_penjaminlebih1', (bool) $status);
            }

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

            $pendapatanPelayanan = SyncPendapatanPelayanan::where('id', $id)->first();

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
}
