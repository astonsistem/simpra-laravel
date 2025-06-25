<?php

namespace App\Http\Controllers;

use App\Http\Resources\PotensiLainCollection;
use App\Models\DataPotensiPelayanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PotensiPelayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'id' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'jenis_pelayanan' => 'nullable|string',
                'penjamin' => 'nullable|string',
                'instalasi' => 'nullable|string',
                'status' => 'nullable|string',
                'cara_bayar' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $paramId = $request->input('id');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $jenispelayanan = $request->input('jenis_pelayanan');
            $penjamin = $request->input('penjamin');
            $instalasi = $request->input('instalasi');
            $status = $request->input('status');
            $caraBayar = $request->input('cara_bayar');

            $query = DataPotensiPelayanan::query();

            if (!empty($paramId)) {
                $query->where('id', $paramId);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_dokumen', [$startDate, $endDate]);
            }
            if (!empty($jenispelayanan)) {
                $query->where('jenis_tagihan', $jenispelayanan);
            }
            if (!empty($penjamin)) {
                $query->where('penjamin_id', $penjamin);
            }
            if (!empty($caraBayar)) {
                $query->where('carabayar_id', $caraBayar);
            }
            if (!empty($instalasi)) {
                $query->where('instalasi_id', $instalasi);
            }
            if (!empty($status)) {
                $query->where('status_id', $status);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('pelayanan_id', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PotensiLainCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
