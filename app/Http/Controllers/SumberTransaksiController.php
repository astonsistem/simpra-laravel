<?php

namespace App\Http\Controllers;

use App\Http\Requests\SumberTransaksiRequest;
use App\Http\Resources\SumberTransaksiCollection;
use App\Http\Resources\SumberTransaksiResource;
use App\Models\MasterSumberTransaksi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SumberTransaksiController extends Controller
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

            $query = MasterSumberTransaksi::query();

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new SumberTransaksiCollection($items, $totalItems, $page, $size, $totalPages)
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
            $sumberTransaksi = MasterSumberTransaksi::findOrFail($id);
            if (!$sumberTransaksi) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new SumberTransaksiResource($sumberTransaksi)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(SumberTransaksiRequest $request)
    {
        $data = $request->validated();

        $sumberTransaksi = MasterSumberTransaksi::create($data);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditambahkan',
            'data' => $sumberTransaksi,
        ], 200);
    }

    public function update(SumberTransaksiRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $sumberTransaksi = MasterSumberTransaksi::findOrFail($id);
            if (!$sumberTransaksi) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }
            $sumberTransaksi->update($data);

            return response()->json(new SumberTransaksiResource($sumberTransaksi), 200);
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
            $sumberTransaksi = MasterSumberTransaksi::findOrFail($id);
            $sumberTransaksi->delete();
            return response()->json([
                'status' => "200",
                'message' => "Sumber Transaksi deleted successfully."
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Sumber Transaksi not found.'
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
            $sumberTransaksi = MasterSumberTransaksi::get();

            $data = $sumberTransaksi->map(function ($st) {
                return [
                    'sumber_id' => $st->sumber_id,
                    'sumber_nama' => $st->sumber_nama,
                    'sumber_jenis' => $st->sumber_jenis,
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
