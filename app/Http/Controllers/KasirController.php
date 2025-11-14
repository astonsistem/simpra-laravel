<?php

namespace App\Http\Controllers;

use App\Http\Resources\KasirCollection;
use App\Models\Kasir;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'global' => 'nullable|string',
                'kasir_id' => 'nullable|string',
                'kasir_nama' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $global = $request->input('global');
            $kasirId = $request->input('kasir_id');
            $kasirNama = $request->input('kasir_nama');

            $query = Kasir::query();

            if (!empty($global)) {
                $query->where(function($q) use ($global) {
                    $q->where('kasir_id',     'ILIKE', "%$global%")
                    ->orWhere('kasir_nama',   'ILIKE', "%$global%");
                });
            }
            if (!empty($kasirId)) {
                $query->where('kasir_id', 'ILIKE', "%$kasirId%");
            }
            if (!empty($kasirNama)) {
                $query->where('kasir_nama', 'ILIKE', "%$kasirNama%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('kasir_nama')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new KasirCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function list()
    {
        try {
            $kasir = Kasir::select('id', 'kasir_id', 'kasir_nama')->orderBy('kasir_nama')->get();

            $data = $kasir->map(function ($k) {
                return [
                    'kasir_nama' => $k->kasir_nama,
                    'kasir_id' => $k->kasir_id,
                    'id' => $k->id,
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

    public function sync()
    {
        try {
            $kasirSiesta = (new Kasir)->setTable('simpra_kasir_ft')->get();
            $synced = 0;
            foreach ($kasirSiesta as $k) {
                // Check if data already exist in table master_kasir (based on kasir_id)
                $exist = Kasir::where('kasir_id', $k->kasir_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    Kasir::create($k->toArray());
                    $synced++;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Data Master Kasir berhasil disinkronisasi',
                'count' => $synced,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
