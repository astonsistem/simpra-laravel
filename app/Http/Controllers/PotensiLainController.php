<?php

namespace App\Http\Controllers;

use App\Http\Requests\PotensiLainRequest;
use App\Http\Requests\TerimaPotensiRequest;
use App\Http\Resources\PotensiLainCollection;
use App\Http\Resources\PotensiLainResource;
use App\Models\DokumenNonlayanan;
use App\Models\DataPenerimaanLain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLainController extends Controller
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
                'uraian' => 'nullable|string',
                'pihak3' => 'nullable|string',
                'is_buktitagihan' => 'nullable|string',
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
            $uraian = $request->input('uraian');
            $pihak3 = $request->input('pihak3');
            $buktiTagihan = $request->input('is_buktitagihan');
            $sisaPotensi = data_get($request->input('sisa_potensi'), 'value');

            $query = DokumenNonlayanan::query();

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
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($pihak3)) {
                $query->where('pihak3', 'ILIKE', "%$pihak3%");
            }
            if (!is_null($buktiTagihan)) {
                if (filter_var($buktiTagihan, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
                    $query->where(function ($q) {
                        $q->where('is_buktitagihan', false)
                        ->orWhereNull('is_buktitagihan');
                    });
                } else {
                    $query->where('is_buktitagihan', true);
                }
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
                            WHERE dpl.piutanglain_id = CAST(dokumen_nonlayanan.id AS VARCHAR)), 0
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
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('id', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PotensiLainCollection($items, $totalItems, $page, $size, $totalPages)
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
    public function index_rincian(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'is_daftar' => 'nullable|boolean',
                'induk_id' => 'nullable|string',
                'pihak3' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $isDaftar = $request->input('is_daftar');
            $indukId = $request->input('induk_id');
            $pihak3 = $request->input('pihak3');

            $query = DokumenNonlayanan::query();

            if (!is_null($isDaftar)) {
                $query->where('induk_id', $indukId);
            } else {
                $query->whereNull('induk_id')
                    ->where('is_buktitagihan', true)
                    ->where('pihak3', 'ILIKE', $pihak3);
            }

            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder');
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('id', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PotensiLainCollection($items, $totalItems, $page, $size, $totalPages)
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
            $potensiLain = DokumenNonlayanan::where('id', $id)->first();

            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new PotensiLainResource($potensiLain)
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
                'tgl_dokumen' => 'nullable|string',
            ]);

            $potensiLainSiesta = (new DokumenNonlayanan)->setTable('simpra_potensilain_ft')->whereDate('tgl_dokumen', $request->tgl_dokumen)->get();
            $count = 0;
            foreach ($potensiLainSiesta as $pl) {
                // Check if data already exist in table dokumen_nonlayanan (based on id)
                $exist = DokumenNonlayanan::where('id', $pl->id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DokumenNonlayanan::create($pl->toArray());
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

    public function store(PotensiLainRequest $request)
    {
        try {
            $data = $request->validated();

            $potensiLainNew = DokumenNonlayanan::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => new PotensiLainResource($potensiLainNew),
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
                'desc_piutang_lain' => 'required|string',
                'piutanglain_id' => 'required|integer',
            ]);

            $potensiLain = DokumenNonlayanan::find($data['piutanglain_id']);
            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($potensiLain->sisa_potensi == 0 || $potensiLain->is_buktitagihan) {
                return response()->json([
                    'message' => 'Data cannot be edited because its sisa potensi is 0 or already has bukti tagihan.'
                ], 422);
            }
            
            $terimaPotensiLain = DataPenerimaanLain::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => $terimaPotensiLain,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function update(PotensiLainRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $potensiLain = DokumenNonlayanan::find($id);
            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $potensiLain->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data potensi lain',
                'data' => new PotensiLainResource($potensiLain),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function batalkan($id)
    {
        try {
            $rincianPotensiLainnya = DokumenNonlayanan::find($id);
            if (!$rincianPotensiLainnya) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (!$rincianPotensiLainnya->induk_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its induk_id is null.'
                ], 422);
            }

            $rincianPotensiLainnya->update(['induk_id' => null]);

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

    public function daftarkan(Request $request, $id)
    {
        try {
            $rincianPotensiLainnya = DokumenNonlayanan::find($id);
            if (!$rincianPotensiLainnya) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($rincianPotensiLainnya->induk_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its induk_id not null.'
                ], 422);
            }
            if (!$rincianPotensiLainnya->is_buktitagihan) {
                return response()->json([
                    'message' => 'Data cannot be edited because its is_buktitagihan is false.'
                ], 422);
            }
            if ($rincianPotensiLainnya->pihak3 != $request->pihak3) {
                return response()->json([
                    'message' => 'Data cannot be edited because its pihak3 not same as surat tagihan.'
                ], 422);
            }

            $rincianPotensiLainnya->update(['induk_id' => $request->induk_id]);

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

    public function destroy($id)
    {
        try {
            $potensiLain = DokumenNonlayanan::find($id);
            if (!$potensiLain) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($potensiLain->terbayar != 0) {
                return response()->json([
                    'message' => 'Data cannot be deleted because its terbayar is not 0.'
                ], 422);
            }

            $potensiLain->delete();

            return response()->json([
                'status'  => 200,
                'message' => "Berhasil menghapus data potensi lain"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
