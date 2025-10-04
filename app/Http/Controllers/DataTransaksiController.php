<?php

namespace App\Http\Controllers;

use App\Http\Requests\KurangBayar\DataTransaksiStoreRequest;
use App\Http\Resources\Selisih\DataTransaksiResource;
use App\Models\DataPenerimaanSelisih;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataTransaksiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $params = $request->validate([
                'periode'           => 'nullable|string',
                'tgl_awal'          => 'nullable|date',
                'tgl_akhir'         => 'nullable|date',
                'page'              => 'nullable|integer|min:1',
                'per_page'          => 'nullable|integer|min:1',
                'sort_field'        => 'nullable|string',
                'sort_order'        => 'nullable|numeric|in:1,-1',
                //
                'is_valid'          => 'nullable',
                'tgl_setor'         => 'nullable|date',
                'no_buktibayar'     => 'nullable|string',
                'tgl_buktibayar'    => 'nullable|date',
                'penyetor'          => 'nullable|string',
                'jenis'             => 'nullable|string',
                'rekening_dpa'      => 'nullable|string',
                'jumlah'            => 'nullable|numeric',
                'nilai'             => 'nullable|numeric',
                'sumber_transaksi'  => 'nullable|string',
                'bank_tujuan'       => 'nullable|string',
                'cara_pembayaran'   => 'nullable|string',
                'export'            => 'nullable',
            ]);

            $query = DataPenerimaanSelisih::query();

            // Filter periode tanggal
            if ($this->isPeriodeHarian($request)) {
                $query->whereBetween('tgl_setor', [
                    Carbon::parse($params['tgl_awal'])->startOfDay(),
                    Carbon::parse($params['tgl_akhir'])->endOfDay()
                ]);
            } else if ($this->isPeriodeBulanan($request)) {
                $query->whereBetween('tgl_setor', [
                    Carbon::parse($params['tgl_awal'])->startOfMonth(),
                    Carbon::parse($params['tgl_akhir'])->endOfMonth()
                ]);
            }

            if ($request->has('is_valid')) {
                $query->where(function ($query) use ($params) {
                    $validated = $params['is_valid'] ?? null;
                    if ($validated == true) {
                        $query->whereNotNull('rc_id')->where('rc_id', '>', 0);
                    } elseif ($validated == '0') {
                        $query->whereNull('rc_id');
                    }
                });
            };

            if ($request->filled('tgl_setor')) {
                $query->where('tgl_setor', 'ILIKE', "%{$params['tgl_setor']}%");
            }

            if ($request->filled('no_buktibayar')) {
                $query->where('no_buktibayar', 'ILIKE', "%{$params['no_buktibayar']}%");
            }

            if ($request->filled('tgl_buktibayar')) {
                $query->where('tgl_buktibayar', 'ILIKE', "%{$params['tgl_buktibayar']}%");
            }

            if ($request->filled('penyetor')) {
                $query->where('penyetor', 'ILIKE', "%{$params['penyetor']}%");
            }

            if ($request->filled('jenis')) {
                $query->where('jenis', 'ILIKE', "%{$params['jenis']}%");
            }

            if ($request->filled('rekening_dpa')) {
                $query->whereHas('rekening_dpa', function ($sub) use ($params) {
                    $sub->where('rek_nama', 'ILIKE', "%{$params['rekening_dpa']}%");
                });
            }

            if ($request->filled('nilai')) {
                $query->where('nilai', $params['nilai']);
            }

            if ($request->filled('jumlah')) {
                $query->where('jumlah', $params['jumlah']);
            }

            if ($request->filled('sumber_transaksi')) {
                $query->where('sumber_transaksi', 'ILIKE', "%{$params['sumber_transaksi']}%");
            }

            if ($request->filled('bank_tujuan')) {
                $query->where('bank_tujuan', 'ILIKE', "%{$params['bank_tujuan']}%");
            }

            if ($request->filled('cara_pembayaran')) {
                $query->where('cara_pembayaran', 'ILIKE', "%{$params['cara_pembayaran']}%");
            }


            // Sort order
            if ($request->has('sort_field') && $request->has('sort_order')) {
                $sortField = $params['sort_field'];

                switch ($sortField) {
                    case 'is_valid':
                        $sortField = 'rc_id';
                        break;
                }

                $query->orderBy($sortField, $params['sort_order'] == -1 ? 'desc' : 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }


            if ($request->has('export')) {
                return DataTransaksiResource::collection($query->get());
            }

            return DataTransaksiResource::collection($query->paginate($params['per_page'] ?? 10));
        } catch (\Exception $e) {
            Log::error('Error in DataTransaksiController@index: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function isPeriodeBulanan(Request $request)
    {
        return $request->has('periode')
            && $request->has('tgl_awal')
            && $request->has('tgl_akhir')
            && $request->periode === 'BULANAN';
    }

    private function isPeriodeHarian(Request $request)
    {
        return $request->has('periode')
            && $request->has('tgl_awal')
            && $request->has('tgl_akhir')
            && $request->periode === 'TANGGAL';
    }

    public function create()
    {
        return new DataTransaksiResource(new DataPenerimaanSelisih());
    }

    public function store(DataTransaksiStoreRequest $request)
    {
        try {
            $validated = $request->validated();

            $data = DataPenerimaanSelisih::create($validated);

            if (!$data) {
                throw new \Exception('Data gagal disimpan', 500);
            }

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in DataTransaksiController@store: ' . $e->getMessage());
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

            if (!$data) {
                throw new \Exception('Data tidak ditemukan.[404]', 404);
            }

            return new DataTransaksiResource($data);
        } catch (\Exception $e) {
            Log::error('Error in DataTransaksiController@show: ' . $e->getMessage());

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

            if (!$data) {
                throw new \Exception('Data tidak ditemukan.[404]', 404);
            }

            $updated = $data->update($validated);

            if (!$updated) {
                throw new \Exception('Data gagal diubah', 500);
            }

            return response()->json([
                'message' => 'Data berhasil diubah',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in DataTransaksiController@update: ' . $e->getMessage());
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
            Log::error('Error in DataTransaksiController@destroy: ' . $e->getMessage());
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
        } catch (\Exception $e) {
            Log::error('Error in DataTransaksiController@validasi: ' . $e->getMessage());
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
            Log::error('Error in DataTransaksiController@cancelValidasi: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan validasi penerimaan lain.',
                'error'   => $e->getMessage(),
                'status'  => 500,
            ], 500);
        }
    }
}
