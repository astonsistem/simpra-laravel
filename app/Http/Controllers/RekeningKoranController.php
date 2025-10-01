<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\DataRekeningKoran;
use PhpParser\Node\Stmt\TryCatch;
use App\Services\RequestBankJatim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\RekeningKoranResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\RekeningKoranCollection;
use App\Http\Requests\RekeningKoranImportRequest;
use App\Http\Resources\RekeningKoranListResource;

class RekeningKoranController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|integer',
                'debit' => 'nullable|integer',
                'kredit' => 'nullable|integer',
                'kualifikasi' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $debit = $request->input('debit');
            $kredit = $request->input('kredit');
            $kualifikasi = $request->input('kualifikasi');

            $query = DataRekeningKoran::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_rc', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_rc', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_rc', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', "ILIKE", "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($debit)) {
                $query->where('debit', $debit);
            }
            if (!empty($kredit)) {
                $query->where('kredit', $kredit);
            }
            if (!empty($kualifikasi) && $kualifikasi == 1) {
                $query->whereNotNull('debit');
            } elseif (!empty($kualifikasi) && $kualifikasi == 2) {
                $query->whereNotNull('kredit');
            }

            // search
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('no_rc', 'ILIKE', "%$search%")
                        ->orWhere('rc_id', 'ILIKE', "%$search%")
                        ->orWhere('rek_dari', 'ILIKE', "%$search%")
                        ->orWhere('nama_dari', 'ILIKE', "%$search%")
                        ->orWhere('bank', 'ILIKE', "%$search%");
                });
            }

            if($request->has('sort_field') && $request->has('sort_order')) {
                $query->orderBy($request->input('sort_field'), $request->input('sort_order') == -1 ? 'desc' : 'asc');
            }
            else{
                $query->orderBy('tgl_rc', 'desc');
            }

            return RekeningKoranResource::collection($query->paginate( $request->input('per_page', 10) ));
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
            $params = $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1',
                'search' => 'nullable|string',
                'filters.nominal.min' => 'nullable|integer|min:0',
                'filters.nominal.max' => 'nullable|integer|min:0',
                'filters.no_rc.value' => 'nullable',
                'sortField' => 'nullable|string',
                'sortOrder' => 'nullable|string',
            ]);

            $query = DataRekeningKoran::query();

            // FILTER NOMINAL
            $query->when($request->has('filters.nominal.min') && $request->has('filters.nominal.max'), function ($q) use ($params) {
                $min = $params['filters']['nominal']['min'];
                $max = $params['filters']['nominal']['max'];

                $q->where(function ($q) use ($min, $max) {
                    $q->whereBetween('debit', [$min, $max])
                        ->orWhereBetween('kredit', [$min, $max]);
                });
            });
            // FILTER no_rc
            $query->when($request->has('filters.no_rc.value'), function ($q) use ($params) {
                $no_rc = $params['filters']['no_rc']['value'];
                $q->where('no_rc', 'ILIKE', "%$no_rc%");
            });

            // SEARCH
            $query->when($request->has('search') && !empty($params['search']), function ($q) use ($params) {
                $search = $params['search'];
                $q->where(function ($q) use ($search) {
                    $q->where('no_rc', 'ILIKE', "%$search%")
                        ->orWhere('rc_id', 'ILIKE', "%$search%")
                        ->orWhere('uraian', 'ILIKE', "%$search%");
                });
            });

            if($params['sortField'] == 'nominal') {
                $query->orderBy('kredit', $params['sortOrder'] ?? 'asc')
                ->orderBy('debit', $params['sortOrder'] ?? 'asc');
            }
            else {
                $query->orderBy($params['sortField'] ?? 'no_rc', $params['sortOrder'] ?? 'asc');
            }

            return RekeningKoranListResource::collection(
                $query->paginate( $params['per_page']?? 10)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function requestBankJatim(Request $request)
    {
        $request->validate([
            'tglawal' => 'required',
            'tglakhir' => 'required|after_or_equal:tglawal'
        ]);

        return response()->json( RequestBankJatim::handle($request));
    }

    public function sinkronisasi(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $request->validate([
                    'tglawal' => 'required',
                    'tglakhir' => 'required|after_or_equal:tglawal'
                ]);

                $data = RequestBankJatim::getCacheData($request);

                $collection = collect($data);
                $existingData = DataRekeningKoran::whereIn('no_rc', $collection->pluck('reffno')->toArray())->get();
                $existingDataIds = $existingData->pluck('no_rc')->toArray();
                $items = [];

                foreach($collection as $item) {
                    $insertData = [
                        'tgl_rc'    => $item?->dateTime,
                        'no_rc'     => $item?->reffno,
                        'uraian'    => $item?->description,
                        'tgl'       => date('Y-m-d'),
                        'rek_dari'  => $item?->transactionCode,
                        'bank'      => 'JATIM',
                    ];

                    switch ( strtoupper($item->flag) ) {
                        case 'D':
                            $insertData['kredit'] = 0;
                            $insertData['debit'] = $item?->amount;
                            break;

                        case 'C':
                            $insertData['kredit'] = $item?->amount;
                            $insertData['debit'] = 0;
                            break;

                        default:
                            $insertData['kredit'] = 0;
                            $insertData['debit'] = 0;
                            break;
                    }
                    if(!in_array($insertData['no_rc'], $existingDataIds)) {
                        $items[] = $insertData;
                    }
                    else{
                        DataRekeningKoran::where('no_rc', $insertData['no_rc'])->update($insertData);
                    }
                }

                DataRekeningKoran::insert( $items );


            });

            return response()->json([
                'success' => true,
                'message' => 'Berhasil sinkronisasi data rekening koran'
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sum(Request $request)
    {
        try {
            $validated =$request->validate([
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|integer',
                'debit' => 'nullable|integer',
                'kredit' => 'nullable|integer',
                'kualifikasi' => 'nullable|integer',
                'nominal' => 'nullable|integer',
            ]);

            $tglAwal = $validated['tgl_awal'];
            $tglAkhir = $validated['tgl_akhir'];
            $bulanAwal = $validated['bulan_awal'];
            $bulanAkhir = $validated['bulan_akhir'];
            $year = $validated['year'];
            $periode = $validated['periode'];
            $uraian = $validated['uraian'];
            $bank = $validated['bank'];
            $debit = $validated['debit'];
            $kredit = $validated['kredit'];
            $kualifikasi = $validated['kualifikasi'];
            $nominal = $validated['nominal'];

            $query = DataRekeningKoran::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereMonth('tgl_rc', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_rc', '<=', (int)$bulanAkhir);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_rc', (int)$year);
            }
            if (!empty($uraian)) {
                $query->where('uraian', "ILIKE", "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($debit)) {
                $query->where('debit', $debit);
            }
            if (!empty($kredit)) {
                $query->where('kredit', $kredit);
            }
            if (!empty($nominal)) {
                $query->where(function($q) use ($nominal) {
                    $q->where('debit', $nominal)
                        ->orWhere('kredit', $nominal);
                });
            }
            if (!empty($kualifikasi) && $kualifikasi == 1) {
                $query->whereNotNull('debit');
            } elseif (!empty($kualifikasi) && $kualifikasi == 2) {
                $query->whereNotNull('kredit');
            }

             // search
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('no_rc', 'LIKE', "%$search%");
                });
            }

            $items = $query->orderBy('tgl_rc', 'desc')->orderBy('no_rc', 'asc')->get();

            $totalDebit = 0;
            $totalKredit = 0;
            foreach ($items as $key => $item) {
                $totalDebit += $item->debit ?? 0;
                $totalKredit += $item->kredit ?? 0;
            }

            return response()->json([
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
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

    public function show(Request $request, string $id)
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

            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            if($request->has('simple') && $request->simple)
            {
                return new RekeningKoranListResource($rekeningKoran);
            }

            return response()->json(
                new RekeningKoranResource($rekeningKoran)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistik()
    {
        $currentMonth = Carbon::now()->format('m');

        $debitMutasiAll = DataRekeningKoran::sumDebit();
        $kreditMutasiAll = DataRekeningKoran::sumKredit();

        $debitMutasiNow = DataRekeningKoran::sumDebit($currentMonth);
        $kreditMutasiNow = DataRekeningKoran::sumKredit($currentMonth);

        $debitMutasiMandiri = DataRekeningKoran::sumDebit($currentMonth, 'MANDIRI');
        $kreditMutasiMandiri = DataRekeningKoran::sumKredit($currentMonth, 'MANDIRI');

        $debitMutasiBca = DataRekeningKoran::sumDebit($currentMonth, 'BCA');
        $kreditMutasiBca = DataRekeningKoran::sumKredit($currentMonth, 'BCA');

        $debitMutasiJatim = DataRekeningKoran::sumDebit($currentMonth, "JATIM");
        $kreditMutasiJatim = DataRekeningKoran::sumKredit($currentMonth, "JATIM");

        return response()->json([
            "all" => [
                "debit" => $debitMutasiAll,
                "kredit" => $kreditMutasiAll,
                "selisih" => $debitMutasiAll - $kreditMutasiAll,
            ],
            "now" => [
                "debit" => $debitMutasiNow,
                "kredit" => $kreditMutasiNow,
                "selisih" => $debitMutasiNow - $kreditMutasiNow,
            ],
            "mandiri" => [
                "debit" => $debitMutasiMandiri,
                "kredit" => $kreditMutasiMandiri,
                "selisih" => $debitMutasiMandiri - $kreditMutasiMandiri,
            ],
            "bca" => [
                "debit" =>  $debitMutasiBca,
                "kredit" => $kreditMutasiBca,
                "selisih" => $debitMutasiBca - $kreditMutasiBca,
            ],
            "jatim" => [
                "debit" => $debitMutasiJatim,
                "kredit" => $kreditMutasiJatim,
                "selisih" => $debitMutasiJatim - $kreditMutasiJatim,
            ],
        ], 200);
    }

    public function pbUncheck()
    {
        $rekeningKoran = DataRekeningKoran::whereNull('pb')->where('bank', '!=', "JATIM")->get();

        if (!$rekeningKoran) {
            return response()->json([
                'message' => 'Not found.'
            ], 404);
        }
        return response()->json(
            $rekeningKoran
        );
    }

    public function pbCheck(string $id)
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

            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(new RekeningKoranResource($rekeningKoran), 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buktiSetor(string $id)
    {
        return "";
    }

    public function importBank(RekeningKoranImportRequest $request)
    {
        try {
            $request->validate([
                'data' => 'required|array',
                'data.*.tgl_rc' => 'required|date',
                'data.*.no_rc' => 'required|string',
                'data.*.uraian' => 'nullable|string',
                'data.*.rek_dari' => 'nullable|string',
                'data.*.nama_dari' => 'nullable|string',
                'data.*.bank' => 'required|string',
                'data.*.debit' => 'nullable|numeric|min:0',
                'data.*.kredit' => 'nullable|numeric|min:0',
            ]);

            DB::transaction(function () use ($request) {
                $importData = $request->input('data');
                $existingData = DataRekeningKoran::whereIn('no_rc', collect($importData)->pluck('no_rc')->toArray())->get();
                $existingDataIds = $existingData->pluck('no_rc')->toArray();
                $items = [];

                foreach ($importData as $item) {

                    if (!in_array($item['no_rc'], $existingDataIds)) {
                        $items[] = $item;
                    } else {
                        // Update existing data
                        DataRekeningKoran::where('no_rc', $item['no_rc'])->update($item);
                    }
                }

                // Insert new data
                if (!empty($items)) {
                    DataRekeningKoran::insert($items);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengimpor data rekening koran',
                'imported_count' => count($request->input('data'))
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing bank data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        //
    }
}
