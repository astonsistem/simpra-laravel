<?php

namespace App\Http\Controllers;

use App\Http\Resources\PotensiPelayananCollection;
use App\Http\Resources\PotensiPelayananResource;
use App\Http\Requests\PotensiPelayananRequest;
use App\Http\Requests\TerimaPotensiRequest;
use App\Models\DataPotensiPelayanan;
use App\Models\DataPenerimaanLain;
use App\Models\RincianPotensiPelayanan;
use App\Models\CaraBayar;
use App\Models\Instalasi;
use App\Models\Penjamin;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PotensiPelayananController extends Controller
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
                'no_dokumen' => 'nullable|string',
                'carabayar_nama' => 'nullable|string',
                'penjamin_nama' => 'nullable|string',
                'uraian' => 'nullable|string',
                'pasien_nama' => 'nullable|string',
                'sisa_potensi' => 'nullable|array',
                'sisa_potensi.value' => 'nullable|integer',
                'sisa_potensi.matchMode' => 'nullable|string|in:equals,notEquals,gt,gte,lt,lte',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahun_periode');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $noDokumen = $request->input('no_dokumen');
            $caraBayar = $request->input('carabayar_nama');
            $penjamin = $request->input('penjamin_nama');
            $uraian = $request->input('uraian');
            $pasien = $request->input('pasien_nama');
            $sisaPotensi = data_get($request->input('sisa_potensi'), 'value');

            $query = DataPotensiPelayanan::query();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_dokumen', $tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_dokumen', [$startDate, $endDate]);
            }
            if (!empty($noDokumen)) {
                $query->where('no_dokumen', 'ILIKE', "%$noDokumen%");
            }
            if (!empty($caraBayar)) {
                $query->where('carabayar_nama', 'ILIKE', "%$caraBayar%");
            }
            if (!empty($penjamin)) {
                $query->where('penjamin_nama', 'ILIKE', "%$penjamin%");
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($pasien)) {
                $query->where('pasien_nama', 'ILIKE', "%$pasien%");
            }
            if (!empty($sisaPotensi)) {
                $matchMode = data_get($request->input('sisa_potensi'), 'matchMode');

                $mapMatchMode = [
                    'equals' => '=',
                    'notEquals' => '!=',
                    'gt' => '>',
                    'gte' => '>=',
                    'lt' => '<',
                    'lte' => '<=',
                ];

                if (isset($mapMatchMode[$matchMode])) {
                    $query->whereRaw(
                        "(total - COALESCE(
                            (SELECT SUM(jumlah_netto) FROM data_penerimaan_lain dpl 
                            WHERE dpl.piutang_id = data_potensi_pelayanan.id), 0
                        )) {$mapMatchMode[$matchMode]} ?",
                        [$sisaPotensi]
                    );
                }
            }

            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder');
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('pelayanan_id', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PotensiPelayananCollection($items, $totalItems, $page, $size, $totalPages)
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
            $potensiPelayanan = DataPotensiPelayanan::where('id', $id)->first();

            if (!$potensiPelayanan) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PotensiPelayananResource($potensiPelayanan)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function tarik(Request $request)
    {
        try {
            $request->validate([
                'tgl_pelayanan' => 'nullable|string',
            ]);

            $potensiPelayananSiesta = (new DataPotensiPelayanan)->setTable('simpra_potensipelayanan_ft')->whereDate('tgl_pelayanan', $request->tgl_pelayanan)->get();
            $count = 0;
            foreach ($potensiPelayananSiesta as $pp) {
                // Check if data already exist in table dokumen_nonlayanan (based on id)
                $exist = DataPotensiPelayanan::where('pendaftaran_id', $pp->pendaftaran_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataPotensiPelayanan::create($pp->toArray());
                    $count++;
                }
            }

            return response()->json([
                'status' => 200,
                'count' => $count,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }    

    public function store(PotensiPelayananRequest $request)
    {
        try {
            $data = $request->validated();
            
            $instalasi = Instalasi::where('instalasi_id', $data['instalasi_id'])->first();
            if ($instalasi) {
                $data['instalasi_nama'] = $instalasi->instalasi_nama;
            }
            $caraBayar = CaraBayar::where('carabayar_id', $data['carabayar_id'])->first();
            if ($caraBayar) {
                $data['carabayar_nama'] = $caraBayar->carabayar_nama;
            }
            $penjamin = Penjamin::where('penjamin_id', $data['penjamin_id'])->first();
            if ($penjamin) {
                $data['penjamin_nama'] = $penjamin->penjamin_nama;
            }

            $potensiPelayananNew = DataPotensiPelayanan::create([
                'id' => Str::uuid()->toString(),
                ...$data,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => $potensiPelayananNew,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function terima(TerimaPotensiRequest $request)
    {
        try {
            $data = $request->validated();

            $request->validate([
                'desc_piutang_pelayanan' => 'required|string',
                'piutang_id' => 'required|string',
            ]);

            $potensiPelayanan = DataPotensiPelayanan::where('id', $data['piutang_id'])->first();
            if (!$potensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($potensiPelayanan->sisa_potensi == 0) {
                return response()->json([
                    'message' => 'Data cannot be edited because its sisa potensi is 0.'
                ], 422);
            }
            
            $terimaPotensiPelayanan = DataPenerimaanLain::create([
                'id' => Str::uuid()->toString(),
                ...$data,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => $terimaPotensiPelayanan,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function updateTP(string $id)
    {
         try {
            $potensiPelayanan = DataPotensiPelayanan::where('id', $id)->first();
            if (!$potensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $newTotalPengajuan = RincianPotensiPelayanan::where('piutang_id', $id)->sum('total_klaim');

            $potensiPelayanan->update(['total_pengajuan' => $newTotalPengajuan]);

            return response()->json([
                'message' => 'Berhasil memperbarui total pengajuan dari data potensi pelayanan',
                'data' => new PotensiPelayananResource($potensiPelayanan),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PotensiPelayananRequest $request, string $id)
    {
         try {
            $data = $request->validated();

            $potensiPelayanan = DataPotensiPelayanan::where('id', $id)->first();
            if (!$potensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $instalasi = Instalasi::where('instalasi_id', $data['instalasi_id'])->first();
            if ($instalasi) {
                $data['instalasi_nama'] = $instalasi->instalasi_nama;
            }
            $caraBayar = CaraBayar::where('carabayar_id', $data['carabayar_id'])->first();
            if ($caraBayar) {
                $data['carabayar_nama'] = $caraBayar->carabayar_nama;
            }
            $penjamin = Penjamin::where('penjamin_id', $data['penjamin_id'])->first();
            if ($penjamin) {
                $data['penjamin_nama'] = $penjamin->penjamin_nama;
            }

            $potensiPelayanan->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data potensi pelayanan',
                'data' => new PotensiPelayananResource($potensiPelayanan),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $potensiPelayanan = DataPotensiPelayanan::where('id', $id)->first();
            if (!$potensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($potensiPelayanan->terbayar != 0) {
                return response()->json([
                    'message' => 'Data cannot be deleted because its terbayar is not 0.'
                ], 422);
            }

            $potensiPelayanan->delete();

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
