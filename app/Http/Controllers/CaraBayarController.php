<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaraBayarCollection;
use App\Models\CaraBayar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CaraBayarController extends Controller
{

    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'global' => 'nullable|string',
                'carabayar_id' => 'nullable|string',
                'carabayar_nama' => 'nullable|string',
            ]);

            $page = $request->input('page', 1);
            $size = $request->input('size', 100);
            $global = $request->input('global');
            $carabayarId = $request->input('carabayar_id');
            $carabayarNama = $request->input('carabayar_nama');

            $query = CaraBayar::query();

            if (!empty($global)) {
                $query->where(function($q) use ($global) {
                    $q->where('carabayar_id',     'ILIKE', "%$global%")
                    ->orWhere('carabayar_nama',   'ILIKE', "%$global%");
                });
            }
            if (!empty($carabayarId)) {
                $query->where('carabayar_id', 'ILIKE', "%$carabayarId%");
            }
            if (!empty($carabayarNama)) {
                $query->where('carabayar_nama', 'ILIKE', "%$carabayarNama%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new CaraBayarCollection($items, $totalItems, $page, $size, $totalPages)
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
            $caraBayar = CaraBayar::select('id', 'carabayar_id', 'carabayar_nama')->get();

            $data = $caraBayar->map(function ($cb) {
                return [
                    'carabayar_nama' => $cb->carabayar_nama,
                    'carabayar_id' => $cb->carabayar_id,
                    'id' => $cb->id,
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
