<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstalasiCollection;
use App\Models\Instalasi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InstalasiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'global' => 'nullable|string',
                'instalasi_id' => 'nullable|string',
                'instalasi_nama' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $global = $request->input('global');
            $instalasiId = $request->input('instalasi_id');
            $instalasiNama = $request->input('instalasi_nama');

            $query = Instalasi::query();

            if (!empty($global)) {
                $query->where(function($q) use ($global) {
                    $q->where('instalasi_id',     'ILIKE', "%$global%")
                    ->orWhere('instalasi_nama',   'ILIKE', "%$global%");
                });
            }
            if (!empty($instalasiId)) {
                $query->where('instalasi_id', 'ILIKE', "%$instalasiId%");
            }
            if (!empty($instalasiNama)) {
                $query->where('instalasi_nama', 'ILIKE', "%$instalasiNama%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new InstalasiCollection($items, $totalItems, $page, $size, $totalPages)
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
            $instalasi = Instalasi::select('id', 'instalasi_id', 'instalasi_nama')->get();

            $data = $instalasi->map(function ($ins) {
                return [
                    'instalasi_nama' => $ins->instalasi_nama,
                    'instalasi_id' => $ins->instalasi_id,
                    'id' => $ins->id,
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
            $instalasiSiesta = (new Instalasi)->setTable('simpra_instalasi_ft')->get();
            $synced = 0;
            foreach ($instalasiSiesta as $i) {
                // Check if data already exist in table master_instalasi (based on instalasi_id)
                $exist = Instalasi::where('instalasi_id', $i->instalasi_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    Instalasi::create($i->toArray());
                    $synced++;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Data Master Instalasi berhasil disinkronisasi',
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
