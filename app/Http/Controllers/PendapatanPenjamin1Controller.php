<?php

namespace App\Http\Controllers;

use App\Http\Resources\PendapatanPenjamin1Collection;
use App\Http\Resources\PendapatanPenjamin1Resource;
use App\Http\Requests\PendapatanPenjamin1Request;
use App\Models\DataPendapatanPenjamin1;
use App\Models\DataPendapatanPelayanan;
use App\Models\CaraBayar;
use App\Models\Instalasi;
use App\Models\Kasir;
use App\Models\Loket;
use App\Models\Penjamin;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PendapatanPenjamin1Controller extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tahun_periode' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'pelayanan_id' => 'nullable|string',
                'jenis_pelayanan' => 'nullable|string',
                'no_pendaftaran' => 'nullable|string',
                'no_rekam_medik' => 'nullable|string',
                'pasien_nama' => 'nullable|string',
                'carabayar_nama' => 'nullable|string',
                'penjamin_nama' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahun_periode');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $pelayananId = $request->input('pelayanan_id');
            $jenisPelayanan = $request->input('jenis_pelayanan');
            $noPendaftaran = $request->input('no_pendaftaran');
            $noRM = $request->input('no_rekam_medik');
            $nama = $request->input('pasien_nama');
            $caraBayar = $request->input('carabayar_nama');
            $penjamin = $request->input('penjamin_nama');
            $status = $request->input('status');

            $query = DataPendapatanPenjamin1::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_pelayanan', $tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($pelayananId)) {
                $query->where('pelayanan_id', $pelayananId);
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
                $query->where('status', 'ILIKE', "%$status%");
            }

            $sortField = $request->input('sortField', 'tgl_pendaftaran');
            $sortOrder = $request->input('sortOrder', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PendapatanPenjamin1Collection($items, $totalItems, $page, $size, $totalPages)
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

            $pendapatanPenjamin1 = DataPendapatanPenjamin1::where('id', $id)->first();
            if (!$pendapatanPenjamin1) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            return response()->json(
                new PendapatanPenjamin1Resource($pendapatanPenjamin1)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(PendapatanPenjamin1Request $request)
    {
        try {
            $data = $request->validated();
            
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $data['pelayanan_id'])->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Data Pendapatan Pelayanan selected not found'
                ], 404);
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
            $kasir = Kasir::where('kasir_id', $data['kasir_id'])->first();
            if ($kasir) {
                $data['kasir_nama'] = $kasir->kasir_nama;
            }
            $loket = Loket::where('loket_id', $data['loket_id'])->first();
            if ($loket) {
                $data['loket_nama'] = $loket->loket_nama;
            }

            $pendapatanPenjamin1New = DataPendapatanPenjamin1::create([
                'id' => Str::uuid()->toString(),
                ...$data,
            ]);

            $pendapatanPelayanan->update([
                'is_penjaminlebih1' => true
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => $pendapatanPenjamin1New,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function update(PendapatanPenjamin1Request $request, string $id)
    {
        try {
            $data = $request->validated();

            $pendapatanPenjamin1 = DataPendapatanPenjamin1::where('id', $id)->first();
            if (!$pendapatanPenjamin1) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (strtolower($pendapatanPenjamin1->status) == 'terbayar') {
                return response()->json([
                    'message' => 'Data cannot be edited because its status is "terbayar".'
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
            $kasir = Kasir::where('kasir_id', $data['kasir_id'])->first();
            if ($kasir) {
                $data['kasir_nama'] = $kasir->kasir_nama;
            }
            $loket = Loket::where('loket_id', $data['loket_id'])->first();
            if ($loket) {
                $data['loket_nama'] = $loket->loket_nama;
            }

            $pendapatanPenjamin1->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data pendapatan penjamin > 1',
                'data' => new PendapatanPenjamin1Resource($pendapatanPenjamin1),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function sinkron(string $id)
    {
        try {
            $pendapatanPenjamin1 = DataPendapatanPenjamin1::where('id', $id)->first();
            if (!$pendapatanPenjamin1) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $pendaftaranId = $pendapatanPenjamin1->pendaftaran_id;

            $sql = "
                SELECT p.pendaftaran_id, s.status_nama AS status
                FROM data_potensi_pelayanan p
                LEFT JOIN master_status s ON p.status_id = s.status_id
                WHERE p.pendaftaran_id = :id
                UNION ALL
                SELECT r.pendaftaran_id, s.status_nama AS status
                FROM data_potensi_pelayanan p
                JOIN rincian_potensi_pelayanan r ON p.id::text = r.piutang_id::text
                LEFT JOIN master_status s ON p.status_id = s.status_id
                WHERE r.pendaftaran_id = :id
            ";
            $result = DB::select($sql, ['id' => $pendaftaranId]);
            $statusNama = $result[0]->status ?? null;

            if ($statusNama) {
                $pendapatanPenjamin1->status = $statusNama;
                $pendapatanPenjamin1->save();
            }

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

            $pendapatanPenjamin1 = DataPendapatanPenjamin1::where('id', $id)->first();
            if (!$pendapatanPenjamin1) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (strtolower($pendapatanPenjamin1->status) != 'piutang') {
                return response()->json([
                    'message' => 'Data cannot be deleted because its status not "piutang".'
                ], 422);
            }

            $pelayananId = $pendapatanPenjamin1->pelayanan_id;
            $pendapatanPelayanan = DataPendapatanPelayanan::where('id', $pelayananId)->first();
            if (!$pendapatanPelayanan) {
                return response()->json([
                    'message' => 'Data Pendapatan Pelayanan of this Penjamin not found'
                ], 404);
            }

            $pendapatanPenjamin1->delete();

            if (!DataPendapatanPenjamin1::where('pelayanan_id', $pelayananId)->exists()) {
                $pendapatanPelayanan->update([
                    'is_penjaminlebih1' => false
                ]);
            }

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
}
