<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataClosingRequest;
use App\Http\Resources\DataClosingCollection;
use App\Http\Resources\DataClosingResource;
use App\Models\DataClosing;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataClosingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'closing_id' => 'nullable|integer',
                'tgl_closing_start' => 'nullable|string',
                'tgl_closing_end' => 'nullable|string',
                'rc_id' => 'nullable|integer',
                'kasir_id' => 'nullable|string',
                'kasir_nama' => 'nullable|string',
                'penyetor_id' => 'nullable|string',
                'penyetor_nama' => 'nullable|string',
                'is_web_change' => 'nullable|boolean',
                'keterangan' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $closingId = $request->input('closing_id');
            $tglClosingStart = $request->input('tgl_closing_start');
            $tglClosingEnd = $request->input('tgl_closing_end');
            $rcId = $request->input('rc_id');
            $kasirId = $request->input('kasir_id');
            $kasirNama = $request->input('kasir_nama');
            $penyetorId = $request->input('penyetor_id');
            $penyetorNama = $request->input('penyetor_nama');
            $webChange = $request->input('is_web_change');
            $keterangan = $request->input('keterangan');

            $query = DataClosing::query();

            if (!empty($closingId)) {
                $query->where('closing_id', $closingId);
            }
            if (!empty($tglClosingStart) && !empty($tglClosingEnd)) {
                $startDate = Carbon::parse($tglClosingStart)->startOfDay();
                $endDate = Carbon::parse($tglClosingEnd)->endOfDay();
                $query->whereBetween('tgl_closing', [$startDate, $endDate]);
            }
            if (!empty($rcId)) {
                $query->where('rc_id', $rcId);
            }
            if (!empty($kasirId)) {
                $query->where('kasir_id', $kasirId);
            }
            if (!empty($kasirNama)) {
                $query->where('kasir_nama', 'ILIKE', "%$kasirNama%");
            }
            if (!empty($penyetorId)) {
                $query->where('penyetor_id', $penyetorId);
            }
            if (!empty($penyetorNama)) {
                $query->where('penyetor_nama', 'ILIKE', "%$penyetorNama%");
            }
            if (!empty($webChange)) {
                $query->where('is_web_change', $webChange);
            }
            if (!empty($keterangan)) {
                $query->where('keterangan', 'ILIKE', "%$keterangan%");
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new DataClosingCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function list(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;

            $query = DataClosing::query();

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new DataClosingCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function store(DataClosingRequest $request)
    {
        try {
            $data = $request->validated();

            $checkData = DataClosing::where('no_closing', $data['no_closing'])->first();
            if ($checkData) {
                return response()->json([
                    'message' => "No Closing sudah ada."
                ], 400);
            }

            DB::beginTransaction();

            $dataClosing = DataClosing::create($data);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menambahkan data sync',
                'data' => new DataClosingResource($dataClosing),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function update(DataClosingRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            $dataClosing = DataClosing::find($id);
            if (!$dataClosing) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            DB::beginTransaction();

            $dataClosing->update($data);

            DB::commit();

            return response()->json(new DataClosingResource($dataClosing), 200);
        } catch (\Exception $e) {
            DB::rollBack();

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
            if (empty($id)) {
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

            $dataClosing = DataClosing::find($id);
            if (!$dataClosing) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            DB::beginTransaction();

            $dataClosing->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => "Data dengan ID $id berhasil dihapus"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
