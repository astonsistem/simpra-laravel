<?php

namespace App\Http\Controllers;

use App\Http\Requests\KurangBayar\DataTransaksiStoreRequest;
use App\Http\Resources\Selisih\DataTransaksiResource;
use App\Models\DataPenerimaanSelisih;
use App\Models\DataRekeningKoran;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelisihKasDataTransaksiController extends Controller
{
    public function index (Request $request) {
        try {
            $params = $request->validate([]);

            $query = DataPenerimaanSelisih::query();

            $query->orderBy('created_at', 'desc');

            if( $request->has('export')) {
                return DataTransaksiResource::collection($query->get());
            }

            return DataTransaksiResource::collection($query->paginate());
        }
        catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@index: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], $e->getCode() ?? 500);
        }
    }

    public function create ()
    {
        return new DataTransaksiResource(new DataPenerimaanSelisih());
    }

    public function store(DataTransaksiStoreRequest $request)
    {
        try {
            $validated = $request->validated();

            $data = DataPenerimaanSelisih::create($validated);

            if(!$data) {
                throw new \Exception('Data gagal disimpan', 500);
            }

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@store: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ]);
        }
    }

    // show
    public function show($id)
    {
        try {
            $data = DataPenerimaanSelisih::where('id', $id)->first();

            if(!$data) {
                throw new \Exception('Data tidak ditemukan.[404]', 404);
            }

            return new DataTransaksiResource($data);

        } catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@show: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ]);
        }
    }



    public function update(DataTransaksiStoreRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $data = DataPenerimaanSelisih::where('id', $id)->first();

            if(!$data) {
                throw new \Exception('Data tidak ditemukan.[404]', 404);
            }

            $updated =$data->update($validated);

            if(!$updated) {
                throw new \Exception('Data gagal diubah', 500);
            }

            return response()->json([
                'message' => 'Data berhasil diubah',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@update: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $data = DataPenerimaanSelisih::where('id', $id)->first();

            if (!$data) {
                throw new \Exception('Data tidak ditemukan.[404]', 404);
            }

            $deleted = $data->delete();

            if (!$deleted) {
                throw new \Exception('Data gagal dihapus', 500);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menghapus data'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@destroy: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], $e->getCode() ?? 500);
        }
    }

    public function validasi(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'rc_id' => 'required',
            ]);
            DB::transaction(function () use ($validatedData) {
                $model = DataPenerimaanSelisih::where('id', $validatedData['id'])->first();

                if (!$model) {
                    throw new \Exception('Penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $rcId = ($validatedData['rc_id'] === 0 || $validatedData['rc_id'] === '0') ? null : $validatedData['rc_id'];

                $model->update([
                    'rc_id'     => $rcId,
                ]);

                $modelTable = (new DataPenerimaanSelisih())->getTable();
                $rekeningTable = (new DataRekeningKoran())->getTable();

                $klarifLayanan = DB::table($modelTable)
                    ->select(DB::raw('SUM(COALESCE(jumlah,0) - COALESCE(admin_kredit,0) - COALESCE(admin_debit,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Layanan: " . $klarifLayanan);
                $akun = DB::table($modelTable)->where('rc_id', $rcId)->value('akun_id');

                DB::table($rekeningTable)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_layanan' => $klarifLayanan,
                        'akun_id'     => $akun ?? '1010101',
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil validasi penerimaan lain',
                'status'  => 200,
            ], 200);
        }
        catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@validasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi penerimaan lain.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }

    public function cancelValidasi(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required',
                'rc_id' => 'required',
            ]);
            DB::transaction(function () use ($validatedData) {
                $model = DataPenerimaanSelisih::where('id', $validatedData['id'])->first();

                if (!$model) {
                    throw new \Exception('Penerimaan lain tidak ditemukan atau status tidak valid.');
                }

                $rcId = ($validatedData['rc_id'] === 0 || $validatedData['rc_id'] === '0') ? null : $validatedData['rc_id'];

                $model->update([
                    'rc_id'     => null,
                ]);

                $modelTable = (new DataPenerimaanSelisih())->getTable();
                $rekeningTable = (new DataRekeningKoran())->getTable();

                $klarifLain = DB::table($modelTable)
                    ->select(DB::raw('SUM(COALESCE(jumlah,0) - COALESCE(admin_kredit,0) - COALESCE(admin_debit,0))'))
                    ->where('rc_id', $rcId)
                    ->value('sum');

                Log::info("Klarif Lain: " . $klarifLain);
                $akun = DB::table($modelTable)->where('rc_id', $rcId)->value('akun_id');

                DB::table($rekeningTable)
                    ->where('rc_id', $rcId)
                    ->update([
                        'klarif_lain' => $klarifLain,
                        'akun_id'     => $akun,
                    ]);
            });

            return response()->json([
                'message' => 'Berhasil membatalkan validasi penerimaan lain',
                'status'  => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in SelisihKasDataTransaksiController@cancelValidasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan validasi penerimaan lain.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }
}
