<?php

namespace App\Http\Controllers;

use App\Http\Requests\AkunRequest;
use App\Http\Resources\AkunCollection;
use App\Http\Resources\AkunResource;
use App\Models\MasterAkun;
use App\Models\MasterRekeningView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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

            $query = MasterAkun::query();

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

            $akun = MasterAkun::where('id', $id)->first();

            if (!$akun) {
                return response()->json([
                    'message' => 'Not found.'
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

        $akun = MasterAkun::create([
            'id' => Str::uuid()->toString(),
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

            $akun = MasterAkun::findOrFail($id);
            if (!$akun) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }
            $akun->update($data);

            return response()->json(new AkunResource($akun), 200);
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

            $akun = MasterAkun::find($id);

            if (!$akun) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $akun->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Akun berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        try {
            $params = $request->validate([
                'page' => 'nullable|numeric|min:1',
                'per_page' => 'nullable|numeric|min:1',
                'search' => 'nullable|string',
                'filters.akun_id.value' => 'nullable|numeric',
                'filters.akun_nama.value' => 'nullable|string',
                'sortField' => 'nullable|string',
                'sortOrder' => 'nullable|string',
                'akun_kode' => 'nullable|string',
                'pagination' => 'nullable',
            ]);

            $akunKode = $request->input('akun_kode');
            $prefix = "4";

            $query = MasterAkun::query();

            if (!empty($akunKode)) {
                $query->where('akun_kode', 'ILIKE', "$akunKode%");
            }

            // FILTER akun_id
            $query->when($request->has('filters.akun_id.value'), function ($q) use ($params) {
                $akun_id = $params['filters']['akun_id']['value'];
                $q->where('akun_id', $akun_id);
            });
            // FILTER akun_id
            $query->when($request->has('filters.akun_nama.value'), function ($q) use ($params) {
                $akun_nama = $params['filters']['akun_nama']['value'];
                $q->where('akun_nama', 'ILIKE', "%$akun_nama%");
            });

            $query->select('akun_id', 'akun_nama')
                ->where('rek_id', 'LIKE', "$prefix%")
                ->whereNotNull('rek_id');

            $query->orderBy($params['sortField'] ?? 'akun_id', $params['sortOrder'] ?? 'asc');

            return response()->json([
                'status' => 200,
                'message' => "success",
                'data' => $request->has('pagination') ? $query->paginate( $params['per_page'] ?? 20 ) : $query->get()
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

            $query = MasterAkun::query();

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
