<?php

namespace App\Http\Controllers;

use App\Http\Resources\RincianPotensiPelayananCollection;
use App\Http\Resources\RincianPotensiPelayananResource;
use App\Http\Requests\RincianPotensiPelayananRequest;
use App\Models\RincianPotensiPelayanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RincianPotensiPelayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'is_daftar' => 'nullable|boolean',
                'piutang_id' => 'nullable|string',
                'penjamin_id' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $isDaftar = $request->input('is_daftar');
            $piutangId = $request->input('piutang_id');
            $penjaminId = $request->input('penjamin_id');

            $query = RincianPotensiPelayanan::query();

            if (!is_null($isDaftar)) {
                $query->where('piutang_id', $piutangId);
            } else {
                $query->whereNull('piutang_id')->where('penjamin_id', $penjaminId);

            }

            $sortField = $request->input('sortField', 'rincian_id');
            $sortOrder = $request->input('sortOrder', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new RincianPotensiPelayananCollection($items, $totalItems, $page, $size, $totalPages)
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
        //
    }

    public function statistik()
    {
        // sum_pendapatan = get_sum_pendapatan(db)
        // jumlah_pasien = get_count_pasien(db)
        // klaim = get_sum_pendapatan_by_status_id(db,2)
        // verif = get_sum_pendapatan_by_status_id(db,3)
        // terima = get_sum_pendapatan_by_status_id(db,4)
        // setor = get_sum_pendapatan_by_status_id(db,5)

        return response()->json([
            'pendapatan' => "sum_pendapatan.total",
            'jumlah_pasien' => "jumlah_pasien.total",
            'pendapatan_klaim' => "klaim.total",
            'pendapatan_verif' => "verif.total",
            'pendapatan_terima' => "terima.total",
            'pendapatan_setor' => "setor.total"
        ], 200);
    }

    public function store(RincianPotensiPelayananRequest $request)
    {
        try {
            $data = $request->validated();
            
            $rincianPotensiPelayananNew = RincianPotensiPelayanan::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil ditambahkan',
                'data' => $rincianPotensiPelayananNew,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function keluarkan(Request $request, $id)
    {
        try {
            $rincianPotensiPelayanan = RincianPotensiPelayanan::where('rincian_id', $id)->first();
            if (!$rincianPotensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if (!$rincianPotensiPelayanan->piutang_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its piutang_id is null.'
                ], 422);
            }

            $rincianPotensiPelayanan->update(['piutang_id' => null]);

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function daftarkan(Request $request, $id)
    {
        try {
            $rincianPotensiPelayanan = RincianPotensiPelayanan::where('rincian_id', $id)->first();
            if (!$rincianPotensiPelayanan) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            if ($rincianPotensiPelayanan->piutang_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its piutang_id not null.'
                ], 422);
            }
            if ($rincianPotensiPelayanan->penjamin_id != $request->penjamin_id) {
                return response()->json([
                    'message' => 'Data cannot be edited because its penjamin_id not same as potensi pelayanan (piutang_id).'
                ], 422);
            }


            $rincianPotensiPelayanan->update(['piutang_id' => $request->piutang_id]);

            return response()->json([
                'status' => 200,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
