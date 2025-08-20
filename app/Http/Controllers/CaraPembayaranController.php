<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaraPembayaranRequest;
use App\Http\Resources\CaraPembayaranCollection;
use App\Http\Resources\CaraPembayaranResource;
use App\Models\MasterCaraPembayaran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CaraPembayaranController extends Controller
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

            $query = MasterCaraPembayaran::query();
            $query->where('is_aktif', 't');

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new CaraPembayaranCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function show($id)
    {
        try {
            $caraPembayaran = MasterCaraPembayaran::where('bayar_id', $id);

            if (!$caraPembayaran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new CaraPembayaranResource($caraPembayaran)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CaraPembayaranRequest $request)
    {
        $data = $request->validated();

        $caraPembayaran = MasterCaraPembayaran::create($data);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => $caraPembayaran,
        ], 200);
    }

    public function update(CaraPembayaranRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $caraPembayaran = MasterCaraPembayaran::findOrFail($id);
            if (!$caraPembayaran) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }
            $caraPembayaran->update($data);

            return response()->json(new CaraPembayaranResource($caraPembayaran), 200);
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
            $caraPembayaran = MasterCaraPembayaran::findOrFail($id);
            $caraPembayaran->delete();
            return response()->json([
                'status' => "200",
                'message' => "Cara Pembayaran deleted successfully."
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cara Pembayaran not found.'
            ], 404);
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
            $caraPembayaran = MasterCaraPembayaran::get();

            $data = $caraPembayaran->map(function ($cp) {
                return [
                    'bayar_id' => $cp->bayar_id,
                    'bayar_nama' => $cp->bayar_nama,
                    'is_aktif' => $cp->is_aktif,
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
