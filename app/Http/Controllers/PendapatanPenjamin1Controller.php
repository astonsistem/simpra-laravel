<?php

namespace App\Http\Controllers;

use App\Http\Resources\PendapatanPenjamin1Collection;
use App\Http\Resources\PendapatanPenjamin1Resource;
use App\Http\Requests\PendapatanPenjamin1Request;
use App\Models\DataPendapatanPenjamin1;
use App\Models\CaraBayar;
use App\Models\Instalasi;
use App\Models\Kasir;
use App\Models\Loket;
use App\Models\Penjamin;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PendapatanPenjamin1Controller extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'pelayanan_id' => 'nullable|string',
                'jenis_pelayanan' => 'nullable|string',
                'no_pendaftaran' => 'nullable|string',
                'no_rm' => 'nullable|string',
                'nama' => 'nullable|string',
                'cara_bayar' => 'nullable|int',
                'penjamin' => 'nullable|int',
                'status' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $pelayananId = $request->input('pelayanan_id');
            $jenisPelayanan = $request->input('jenis_pelayanan');
            $noPendaftaran = $request->input('no_pendaftaran');
            $noRM = $request->input('no_rm');
            $nama = $request->input('nama');
            $caraBayar = $request->input('cara_bayar');
            $penjamin = $request->input('penjamin');
            $status = $request->input('status');

            $query = DataPendapatanPenjamin1::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($pelayananId)) {
                $query->where('pelayanan_id', $pelayananId);
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
                $query->where('status', 'ILIKE', "%$status%");
            }

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
        $data = $request->validated();

        $akun = DataPendapatanPenjamin1::create([
            'id' => Str::uuid()->toString(),
            ...$data,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => $akun,
        ], 200);
    }

    public function update(PendapatanPenjamin1Request $request, string $id)
    {
        try {
            $data = $request->validated();

            $pendapatanPenjamin1 = DataPendapatanPenjamin1::where('id', $id)->firstOrFail();
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

            if (!empty($data['carabayar_id'])) {
                $caraBayar = CaraBayar::where('carabayar_id', $data['carabayar_id'])->first();
                if ($caraBayar) {
                    $data['carabayar_nama'] = $caraBayar->carabayar_nama;
                }
            }
            if (!empty($data['penjamin_id'])) {
                $penjamin = Penjamin::where('penjamin_id', $data['penjamin_id'])->first();
                if ($penjamin) {
                    $data['penjamin_nama'] = $penjamin->penjamin_nama;
                }
            }
            if (!empty($data['instalasi_id'])) {
                $instalasi = Instalasi::where('instalasi_id', $data['instalasi_id'])->first();
                if ($instalasi) {
                    $data['instalasi_nama'] = $instalasi->instalasi_nama;
                }
            }
            if (!empty($data['kasir_id'])) {
                $kasir = Kasir::where('kasir_id', $data['kasir_id'])->first();
                if ($kasir) {
                    $data['kasir_nama'] = $kasir->kasir_nama;
                }
            }
            if (!empty($data['loket_id'])) {
                $loket = Loket::where('loket_id', $data['loket_id'])->first();
                if ($loket) {
                    $data['loket_nama'] = $loket->loket_nama;
                }
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

            $pendapatanPenjamin1->delete();

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
