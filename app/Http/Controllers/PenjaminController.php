<?php

namespace App\Http\Controllers;

use App\Http\Resources\PenjaminCollection;
use App\Models\Penjamin;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PenjaminController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'penjamin_nama' => 'nullable|string',
            ]);

            $page = $request->input('page', 1);
            $size = $request->input('size', 100);
            $penjaminNama = $request->input('penjamin_nama');

            $query = Penjamin::query();
            if (!empty($penjaminNama)) {
                $query->where('penjamin_nama', 'ILIKE', "%$penjaminNama%");
            }

            $totalItems = $query->count();

            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PenjaminCollection($items, $totalItems, $page, $size, $totalPages)
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
            $penjamin = Penjamin::select('id', 'penjamin_id', 'penjamin_nama')->get();

            $data = $penjamin->map(function ($p) {
                return [
                    'penjamin_nama' => $p->penjamin_nama,
                    'penjamin_id' => $p->penjamin_id,
                    'id' => $p->id,
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
    }
}
