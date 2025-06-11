<?php

namespace App\Http\Controllers;

use App\Http\Requests\AkunRequest;
use App\Http\Resources\AkunCollection;
use App\Http\Resources\AkunResource;
use App\Models\Akun;
use App\Models\MasterRekeningView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AkunController extends Controller
{

    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
            ]);

            $page = $request->input('page', 1);
            $size = $request->input('size', 10);

            $query = Akun::query();

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new AkunCollection($items, $totalItems, $page, $size, $totalPages)
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

            $akun = Akun::where('id', $id)->first();

            if (!$akun) {
                return response()->json([
                    'message' => 'Akun not found.'
                ], 404);
            }
            return response()->json(
                new AkunResource($akun)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(AkunRequest $request)
    {
        $data = $request->validated();

        $akun = Akun::create([
            ...$data,
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => $akun,
        ], 200);
    }

    public function update(AkunRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $akun = Akun::findOrFail($id);
            $akun->update($data);

            return response()->json([
                new AkunResource($akun)
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
        $akun = Akun::find($id);
        if (!$akun) {
            return response()->json([
                'detail' => [[
                    'loc' => ['id', 0],
                    'msg' => 'Akun tidak ditemukan',
                    'type' => 'not_found'
                ]]
            ], 422);
        }

        $akun->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Akun berhasil dihapus',
            'data'    => $akun->id
        ], 200);
    }

    public function list(Request $request)
    {
        try {
            $request->validate([
                'akun_kode' => 'nullable|string',
            ]);

            $akunKode = $request->input('akun_kode');
            $prefix = "4";

            $query = Akun::query();

            if (!empty($akunKode)) {
                $query->where('akun_kode', 'ILIKE', "$akunKode%");
            }
            $akuns = $query->select('akun_id', 'akun_nama')
                ->where('rek_id', 'LIKE', "$prefix%")
                ->whereNotNull('rek_id')
                ->orderBy('akun_id')
                ->get();

            $data = $akuns->map(function ($akun) {
                return [
                    'akun_id' => $akun->akun_id,
                    'akun_nama' => $akun->akun_nama,
                ];
            })->toArray();

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => $data
            ], 200);
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

    public function listAkunPotensiLain(Request $request)
    {
        try {
            $request->validate([
                'akun_kode' => 'nullable|string',
            ]);

            $akunKode = $request->input('akun_kode');
            $prefix = "102";
            $limit = 1000;

            $query = Akun::query();

            if (!empty($akunKode)) {
                $query->where('akun_kode', 'ILIKE', "$akunKode%");
            }
            $akuns = $query->select('akun_id', 'akun_nama')
                ->where('akun_kode', 'LIKE', "$prefix%")
                ->whereNotNull('rek_id')
                ->orderBy('id')
                ->limit($limit)
                ->get();

            $data = $akuns->map(function ($akun) {
                return [
                    'akun_id' => $akun->akun_id,
                    'akun_nama' => $akun->akun_nama,
                ];
            })->toArray();

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => $data
            ], 200);
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

    public function listPendapatan(Request $request)
    {
        try {
            $request->validate([
                'akun_kode' => 'nullable|string',
            ]);

            $akunKode = $request->input('akun_kode');

            $akuns = MasterRekeningView::all();

            $data = $akuns->map(function ($rek) {
                return [
                    'rek_id' => $rek->rek_id,
                    'rek_nama' => $rek->rek_nama,
                ];
            })->toArray();

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => $data
            ], 200);
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
}
