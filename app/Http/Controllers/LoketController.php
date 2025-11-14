<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoketCollection;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'global' => 'nullable|string',
                'loket_id' => 'nullable|string',
                'loket_nama' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $global = $request->input('global');
            $loketId = $request->input('loket_id');
            $loketNama = $request->input('loket_nama');

            $query = Loket::query();

            if (!empty($global)) {
                $query->where(function($q) use ($global) {
                    $q->where('loket_id',     'ILIKE', "%$global%")
                    ->orWhere('loket_nama',   'ILIKE', "%$global%");
                });
            }
            if (!empty($loketId)) {
                $query->where('loket_id', 'ILIKE', "%$loketId%");
            }
            if (!empty($loketNama)) {
                $query->where('loket_nama', 'ILIKE', "%$loketNama%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('loket_id')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new LoketCollection($items, $totalItems, $page, $size, $totalPages)
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
            $loket = Loket::select('id', 'loket_id', 'loket_nama')->orderBy('loket_id')->get();

            $data = $loket->map(function ($lk) {
                return [
                    'loket_nama' => $lk->loket_nama,
                    'loket_id' => $lk->loket_id,
                    'id' => $lk->id,
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
            $loketSiesta = (new Loket)->setTable('simpra_loket_ft')->get();
            $synced = 0;
            foreach ($loketSiesta as $l) {
                // Check if data already exist in table master_loket (based on loket_id)
                $exist = Loket::where('loket_id', $l->loket_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    Loket::create($l->toArray());
                    $synced++;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Data Master Loket berhasil disinkronisasi',
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
