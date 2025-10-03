<?php

namespace App\Http\Controllers;

use App\Http\Requests\RincianBkuRequest;
use App\Http\Resources\RincianBkuCollection;
use App\Http\Resources\RincianBkuResource;
use App\Models\MasterRekeningView;
use App\Models\DataRincianBku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RincianBkuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'bku_id' => 'required|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $bkuId = $request->input('bku_id');

            $query = DataRincianBku::query();

            $query->where('bku_id', $bkuId);

            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder');
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $totalItems = $query->toBase()->getCountForPagination();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new RincianBkuCollection($items, $totalItems, $page, $size, $totalPages)
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
            $RincianBKU = DataRincianBku::where('rincian_id', $id)->first();

            if (!$RincianBKU) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new RincianBkuResource($RincianBKU)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list_rekening()
    {
        try {
            $rekening = MasterRekeningView::select('rek_id', 'rek_nama')->get();

            $data = $rekening->map(function ($p) {
                return [
                    'rek_nama' => $p->rek_nama,
                    'rek_id' => $p->rek_id,
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

    public function store(RincianBKURequest $request)
    {
        try {
            $data = $request->validated();

            $RincianBKUNew = DataRincianBku::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => new RincianBkuResource($RincianBKUNew),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function update(RincianBKURequest $request, $id)
    {
        try {
            $data = $request->validated();

            $RincianBKU = DataRincianBku::where('rincian_id', $id)->first();
            if (!$RincianBKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $RincianBKU->update($data);

            return response()->json([
                'message' => 'Berhasil memperbarui data Rincian BKU',
                'data' => new RincianBkuResource($RincianBKU),
            ], 200);
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
            $RincianBKU = DataRincianBku::where('rincian_id', $id)->first();
            if (!$RincianBKU) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $RincianBKU->delete();

            return response()->json([
                'status'  => 200,
                'message' => "Berhasil menghapus data Rincian BKU"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
