<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaraPembayaranCollection;
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
            $bank = MasterBank::findOrFail($id);

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => [
                    'bank_id' => $bank->bank_id,
                    'bank_nama' => $bank->bank_nama,
                    'is_aktif' => $bank->is_aktif,
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Bank not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'bank_id' => 'required|string|max:255',
                'bank_nama' => 'required|string|max:255',
                'is_aktif' => 'required|boolean',
            ]);

            $bank = MasterBank::create($request->all());

            return response()->json([
                'status' => "201",
                'message' => "Bank created successfully.",
                'data' => [
                    'bank_id' => $bank->bank_id,
                    'bank_nama' => $bank->bank_nama,
                    'is_aktif' => $bank->is_aktif,
                ]
            ], 201);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['body', $field],
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
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'bank_nama' => 'sometimes|required|string|max:255',
                'is_aktif' => 'sometimes|required|boolean',
            ]);

            $bank = MasterBank::findOrFail($id);
            $bank->update($request->all());

            return response()->json([
                'status' => "200",
                'message' => "Bank updated successfully.",
                'data' => [
                    'bank_id' => $bank->bank_id,
                    'bank_nama' => $bank->bank_nama,
                    'is_aktif' => $bank->is_aktif,
                ]
            ], 200);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['body', $field],
                        'msg' => $message,
                        'type' => 'validation',
                    ];
                }
            }
            return response()->json([
                'detail' => $errors
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Bank not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $bank = MasterBank::findOrFail($id);
            $bank->delete();
            return response()->json([
                'status' => "200",
                'message' => "Bank deleted successfully."
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Bank not found.'
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
