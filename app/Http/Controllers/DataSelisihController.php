<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DataSelisihView;
use Illuminate\Support\Facades\Log;
use App\Models\DataPenerimaanSelisih;
use App\Http\Resources\Selisih\DataSelisihResource;
use App\Http\Requests\KurangBayar\DataTransaksiStoreRequest;
use App\Http\Resources\Selisih\DataTransaksiResource;

class DataSelisihController extends Controller
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
                'sumber_transakasi'  => 'nullable|string',
                'bank_tujuan'       => 'nullable|string',
                'cara_pembayaran'   => 'nullable|string',
                'export'            => 'nullable',
            ]);

            $query = DataSelisihView::query();

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
                $query->where('nilai', 'ILIKE', "%{$params['nilai']}%");
            }

            if ($request->filled('jumlah')) {
                $query->where('jumlah', 'ILIKE', "%{$params['jumlah']}%");
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

            $query->withExists('dataTransaksi');

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
                $query->orderBy('tgl_bukti', 'desc');
            }


            if ($request->has('export')) {
                return response()->json($query->get());
            }

            return DataSelisihResource::collection( $query->paginate($params['per_page'] ?? 10) );
        } catch (\Exception $e) {
            Log::error('Error in DataSelisihController@index: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request,string $id)
    {
        try {
            if(Str::isUuid($id === false)) {
                throw new \Exception('ID tidak valid.');
            }

            $dataSelisih = DataSelisihView::where('id', $id)->first();

            if (!$dataSelisih) {
               throw new \Exception('Data selisih tidak ditemukan.');
            }

            $dataTransaksi = DataPenerimaanSelisih::where('sumber_id', $id);



            return response()->json([
                'success' => true,
                'data' => new DataSelisihResource($dataSelisih),
                'exists_data_transaksi' => $dataTransaksi->exists(),
                'data_transaksi' => DataTransaksiResource::collection($dataTransaksi->get())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(DataTransaksiStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // if empty sumber_id
            if (empty($validatedData['sumber_id']) || !$validatedData['sumber_id']) {
                throw new \Exception('Sumber ID tidak boleh kosong.');
            }



            if(isset($validatedData['id'])) {
                unset($validatedData['id']);
            }

            if(isset($validatedData['nilai'])) {
                $validatedData['selisih'] = $validatedData['nilai'];
            }
            Log::info('Validated Data: ' . json_encode($validatedData));

            $dataSelisih = DataPenerimaanSelisih::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => new DataSelisihResource($dataSelisih)
            ]);
        }  catch (\Exception $e) {
            Log::error('Error in DataSelisihController@store: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage() ?? 'Terjadi kesalahan pada server.',
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
}
