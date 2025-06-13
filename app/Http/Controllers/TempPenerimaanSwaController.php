<?php

namespace App\Http\Controllers;

use App\Http\Resources\TempPenerimaanSwaCollection;
use App\Http\Resources\TempPenerimaanSwaResource;
use App\Models\TempPenerimaanSwa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TempPenerimaanSwaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tanggal' => 'nullable|string',
                'sumber_transaksi' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tanggal = $request->input('tanggal');
            $sumberTransaksi = $request->input('sumber_transaksi');

            $query = TempPenerimaanSwa::query();

            if (!empty($tanggal)) {
                $query->where('tgl_bayar', $tanggal);
            }
            if (!empty($sumberTransaksi)) {
                $query->where('sumber_transaksi', $sumberTransaksi);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bayar', 'asc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new TempPenerimaanSwaCollection($items, $totalItems, $page, $size, $totalPages)
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
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $tempPenerimaan = TempPenerimaanSwa::where('transaksi_id', $id)->first();

            if (!$tempPenerimaan) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new TempPenerimaanSwaResource($tempPenerimaan)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
