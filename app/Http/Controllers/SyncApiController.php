<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncApiRequest;
use App\Http\Resources\SyncApiCollection;
use App\Http\Resources\SyncApiResource;
use App\Models\MasterSinkronisasi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SyncApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
            ]);

            $page = $request->input('page', 1);
            $size = $request->input('size', 100);

            $query = MasterSinkronisasi::query();

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new SyncApiCollection($items, $totalItems, $page, $size, $totalPages)
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

            $akun = MasterSinkronisasi::where('id', $id)->first();

            if (!$akun) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new SyncApiResource($akun)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(SyncApiRequest $request)
    {
        $data = $request->validated();

        $sync = MasterSinkronisasi::create($data);

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil menambahkan data sync',
            'data' => new SyncApiResource($sync),
        ], 200);
    }

    public function update(SyncApiRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $sync = MasterSinkronisasi::find($id);
            if (!$sync) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $sync->update($data);

            return response()->json(new SyncApiResource($sync), 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function menu()
    {
        $menuList = MasterSinkronisasi::select('sinkronisasi_menu')->distinct()->get();

        if (!$menuList) {
            return response()->json([
                'message' => 'Not found.'
            ], 404);
        }
        return response()->json(
            $menuList,
            200
        );
    }

    public function list($menu)
    {
        $menuList = MasterSinkronisasi::where('sinkronisasi_menu', $menu)->get();
        if (!$menuList) {
            return response()->json([
                'message' => 'Not found.'
            ], 404);
        }
        return response()->json(
            SyncApiResource::collection($menuList),
            200
        );
    }
}
