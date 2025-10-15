<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MasterJenisBku;
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
use App\Http\Requests\RekeningKoranUpdateRequest;
use App\Http\Resources\RekeningKoranListResource;

class RekeningKoranController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'no_rc' => 'nullable|string',
                'tgl_rc' => 'nullable|string',
                'uraian' => 'nullable|string',
                'akun_data' => 'nullable|string',
                'akunls_data' => 'nullable|string',
                'bank' => 'nullable|string',
                'pb' => 'nullable|string',
                'debit' => 'nullable|numeric',
                'kredit' => 'nullable|numeric',
                'terklarifikasi' => 'nullable|numeric',
                'belum_terklarifikasi' => 'nullable|numeric',
                'rekening_dpa' => 'nullable|string',
                'kualifikasi' => 'nullable|integer',
                'bku_filter' => 'nullable',
                'export' => 'nullable',
                'sort_field' => 'nullable|string',
                'sort_order' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $noRc = $request->input('no_rc');
            $tglRc = $request->input('tgl_rc');
            $uraian = $request->input('uraian');
            $akunData = $request->input('akun_data');
            $akunlsData = $request->input('akunls_data');
            $bank = $request->input('bank');
            $pb = $request->input('pb');
            $debit = $request->input('debit');
            $kredit = $request->input('kredit');
            $terklarifikasi = $request->input('terklarifikasi');
            $belumTerklarifikasi = $request->input('belum_terklarifikasi');
            $rekeningDpa = $request->input('rekening_dpa');
            $kualifikasi = $request->input('kualifikasi');
            $isExport = $request->input('export', false);

            $query = DataRekeningKoran::with(['akunData', 'akunlsData', 'rekeningDpa']);

            // Filter by date range
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }

            // Filter by month range (BULANAN)
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "BULANAN") {
                $query->whereMonth('tgl_rc', '>=', (int)$bulanAwal);
                $query->whereMonth('tgl_rc', '<=', (int)$bulanAkhir);
            }

            // Filter by year
            if (!empty($year)) {
                $query->whereYear('tgl_rc', (int)$year);
            }

            // Column filters
            if (!empty($noRc)) {
                $query->where('no_rc', 'ILIKE', "%$noRc%");
            }
            if (!empty($tglRc)) {
                $query->whereDate('tgl_rc', Carbon::parse($tglRc)->format('Y-m-d'));
            }
            if (!empty($uraian)) {
                $query->where('uraian', "ILIKE", "%$uraian%");
            }
            if (!empty($akunData)) {
                $query->whereHas('akunData', function($q) use ($akunData) {
                    $q->where('akun_nama', 'ILIKE', "%$akunData%");
                });
            }
            if (!empty($akunlsData)) {
                $query->whereHas('akunlsData', function($q) use ($akunlsData) {
                    $q->where('akun_nama', 'ILIKE', "%$akunlsData%");
                });
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($pb)) {
                $query->where('pb', 'ILIKE', "%$pb%");
            }
            if (!empty($debit)) {
                $query->where('debit', $debit);
            }
            if (!empty($kredit)) {
                $query->where('kredit', $kredit);
            }
            if (!empty($terklarifikasi)) {
                $query->whereRaw('(COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0)) = ?', [$terklarifikasi]);
            }
            if (!empty($belumTerklarifikasi)) {
                $query->whereRaw('(COALESCE(kredit, 0) - (COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0))) = ?', [$belumTerklarifikasi]);
            }
            if (!empty($rekeningDpa)) {
                $query->whereHas('rekeningDpa', function($q) use ($rekeningDpa) {
                    $q->where('rek_nama', 'ILIKE', "%$rekeningDpa%");
                });
            }
            if (!empty($kualifikasi) && $kualifikasi == 1) {
                $query->whereNotNull('debit');
            } elseif (!empty($kualifikasi) && $kualifikasi == 2) {
                $query->whereNotNull('kredit');
            }

            // BKU Filter: akunls_id is not null and bku_id is null
            $bkuFilter = $request->input('bku_filter', false);
            if ($bkuFilter == true || $bkuFilter === 'true') {
                $query->whereNotNull('akunls_id')
                      ->whereNull('bku_id');
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

            // Sorting
            if($request->has('sort_field') && $request->has('sort_order')) {
                $sortField = $request->input('sort_field');
                $sortOrder = $request->input('sort_order') == -1 ? 'desc' : 'asc';

                // Handle special sort fields that need joins or raw SQL
                switch($sortField) {
                    case 'akun_data':
                        $query->leftJoin('master_akun as ma', 'data_rekening_koran.akun_id', '=', 'ma.akun_id')
                              ->orderBy('ma.akun_nama', $sortOrder)
                              ->select('data_rekening_koran.*');
                        break;
                    case 'akunls_data':
                        $query->leftJoin('master_akun as ma2', 'data_rekening_koran.akunls_id', '=', 'ma2.akun_id')
                              ->orderBy('ma2.akun_nama', $sortOrder)
                              ->select('data_rekening_koran.*');
                        break;
                    case 'rekening_dpa':
                        $query->leftJoin('master_rekening_v as mrv', 'data_rekening_koran.rek_id', '=', 'mrv.rek_id')
                              ->orderBy('mrv.rek_nama', $sortOrder)
                              ->select('data_rekening_koran.*');
                        break;
                    case 'terklarifikasi':
                        $query->orderByRaw('(COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0)) ' . $sortOrder);
                        break;
                    case 'belum_terklarifikasi':
                        $query->orderByRaw('(COALESCE(kredit, 0) - (COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0))) ' . $sortOrder);
                        break;
                    default:
                        $query->orderBy($sortField, $sortOrder);
                        break;
                }
            }
            else{
                $query->orderBy('sync_at', 'desc');
            }

            // If export, return all data without pagination
            if ($request->has('export')) {
                $data = $query->get();
                return RekeningKoranResource::collection($data);
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
                'bank' => 'nullable|string',
                'tgl_rc' => 'nullable|string',
            ]);

            $query = DataRekeningKoran::query()->where('kredit', '>', 0);

            // FILTER BANK
            $query->when($request->has('bank') && !empty($params['bank']), function ($q) use ($params) {
                $bank = $params['bank'];
                $q->where('bank', $bank);
            });
            // FILTER TGL_RC
            $query->when($request->has('tgl_rc') && !empty($params['tgl_rc'] ), function ($q) use ($params) {
                // if date include / char
                if (strpos($params['tgl_rc'], '/') !== false) {
                    $tgl_rc = Carbon::createFromFormat('d/m/Y', str_replace('-', '/', $params['tgl_rc']));
                } else {
                    $tgl_rc = Carbon::createFromFormat('Y-m-d', $params['tgl_rc']);
                }
                $q->where('tgl_rc', '>=', $tgl_rc->format('Y-m-d'));
            });


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


            Log::info("Raw Sql : ". $query->toSql() ."  ");
            Log::info("Bindings : ". implode(", ", $query->getBindings()) ."  ");

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

    public function pbUncheck(Request $request)
    {
        try {
            $request->validate([
                'tgl_rc' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = DataRekeningKoran::whereNull('pb')
                ->whereNotIn('bank', ['JATIM', 'jatim']);

            // Filter by tgl_rc if provided
            if ($request->has('tgl_rc') && !empty($request->input('tgl_rc'))) {
                $tglRc = $request->input('tgl_rc');
                $query->where('tgl_rc', '<=', $tglRc);
            }

            $query->orderBy('tgl_rc', 'desc')
                  ->orderBy('no_rc', 'asc');

            $perPage = $request->input('per_page', 10);
            $rekeningKoran = $query->paginate($perPage);

            return response()->json($rekeningKoran);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pbCheck(Request $request, string $id)
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

            // Get linked PB records (mutations that are PB to this record)
            $linkedRecords = DataRekeningKoran::where('pb', $id)
                ->orderBy('tgl_rc', 'desc')
                ->orderBy('no_rc', 'asc')
                ->get();

            return response()->json([
                'data' => new RekeningKoranResource($rekeningKoran),
                'linked_records' => RekeningKoranResource::collection($linkedRecords)
            ], 200);
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

    public function update(RekeningKoranUpdateRequest $request, string $id)
    {
        try {
            // Validate ID
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

            // Find rekening koran
            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Data rekening koran tidak ditemukan.'
                ], 404);
            }

            // Check if data is locked
            if ($rekeningKoran->kunci) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah terkunci dan tidak dapat diubah.'
                ], 403);
            }

            // Check if already in BKU
            if ($rekeningKoran->bku_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah masuk BKU dan tidak dapat diubah.'
                ], 403);
            }

            // Get validated data
            $validated = $request->validated();

            // Additional validation: total klarifikasi should not exceed kredit/debit
            $totalKlarifikasi = $validated['klarif_layanan'] + $validated['klarif_lain'];
            $nominal = $rekeningKoran->kredit > 0 ? $rekeningKoran->kredit : $rekeningKoran->debit;

            if ($totalKlarifikasi > $nominal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total klarifikasi tidak boleh melebihi nominal ' . ($rekeningKoran->kredit > 0 ? 'kredit' : 'debit') . '.',
                    'detail' => [
                        [
                            'loc' => ['body', 'klarif_layanan'],
                            'msg' => 'Total klarifikasi melebihi nominal.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            // Update data
            DB::transaction(function () use ($rekeningKoran, $validated) {
                $rekeningKoran->update([
                    'tgl_rc' => $validated['tgl_rc'],
                    'no_rc' => $validated['no_rc'],
                    'akunls_id' => $validated['akunls_id'],
                    'klarif_layanan' => $validated['klarif_layanan'],
                    'klarif_lain' => $validated['klarif_lain'],
                    'rek_id' => $validated['rek_id'] ?? null,
                    'is_web_change' => true,
                ]);
            });

            // Reload with relationships
            $rekeningKoran->load(['akunData', 'akunlsData', 'rekeningDpa']);

            return response()->json([
                'success' => true,
                'message' => 'Data rekening koran berhasil diubah.',
                'data' => new RekeningKoranResource($rekeningKoran)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating rekening koran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePb(Request $request, string $id)
    {
        try {
            // Validate ID
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

            // Validate request data
            $request->validate([
                'mutasi' => 'nullable|boolean',
                'pb_dari' => 'nullable|string',
            ]);

            // Find rekening koran
            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Data rekening koran tidak ditemukan.'
                ], 404);
            }

            // Validate business rules
            if ($rekeningKoran->kredit <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'PB hanya dapat dilakukan pada mutasi dengan kredit > 0.'
                ], 422);
            }

            if ($rekeningKoran->akun_id !== null || $rekeningKoran->akunls_id !== null || $rekeningKoran->bku_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'PB hanya dapat dilakukan pada mutasi yang belum terklarifikasi (akun_id, akunls_id, dan bku_id harus null).'
                ], 422);
            }

            // Update data
            DB::transaction(function () use ($rekeningKoran, $request) {
                $updateData = [];

                if ($request->has('mutasi')) {
                    $updateData['mutasi'] = $request->input('mutasi');
                }

                if ($request->has('pb_dari')) {
                    $updateData['pb_dari'] = $request->input('pb_dari');
                }

                if (!empty($updateData)) {
                    $rekeningKoran->update($updateData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data PB berhasil diubah.',
                'data' => new RekeningKoranResource($rekeningKoran)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating PB: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePbCancel(Request $request, string $id)
    {
        try {
            // Validate ID
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

            // Find rekening koran
            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Data rekening koran tidak ditemukan.'
                ], 404);
            }

            // Update data - set pb to null
            DB::transaction(function () use ($rekeningKoran) {
                $rekeningKoran->update([
                    'pb' => null
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Penandaan PB berhasil dibatalkan.',
                'data' => new RekeningKoranResource($rekeningKoran)
            ]);

        } catch (\Exception $e) {
            Log::error('Error canceling PB: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function linkPb(Request $request, string $id)
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

            $validated = $request->validate([
                'pb_rc_id' => 'required', // The Bank Jatim rc_id to link to
            ]);

            // Find the record to be linked
            $rekeningKoran = DataRekeningKoran::where('rc_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Data rekening koran tidak ditemukan.'
                ], 404);
            }

            // Validate that it's not Bank Jatim
            if (strtoupper($rekeningKoran->bank) === 'JATIM') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menautkan mutasi Bank Jatim.'
                ], 422);
            }

            // Find the Bank Jatim record
            $pbRcId = $validated['pb_rc_id'];
            $bankJatimRecord = DataRekeningKoran::where('rc_id', $pbRcId)->first();

            if (!$bankJatimRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Bank Jatim tidak ditemukan.'
                ], 404);
            }

            // Update data - set pb to the Bank Jatim rc_id
            DB::transaction(function () use ($rekeningKoran, $pbRcId) {
                $rekeningKoran->update([
                    'pb' => $pbRcId
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil ditautkan dengan Bank Jatim.',
                'data' => new RekeningKoranResource($rekeningKoran)
            ]);

        } catch (\Exception $e) {
            Log::error('Error linking PB: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create BKU from Rekening Koran data and send to PAD Online
     */
    public function createBku(Request $request)
    {
        try {
            $validated = $request->validate([
                'rekening_koran_id' => 'required|exists:data_rekening_koran,rc_id',
                'bank' => 'required|string',
                'mutasi' => 'required|boolean',
                'no_bku' => 'required|string|max:255',
                'ket_bku' => 'required|string',
                'bku_type' => 'required|string|in:Penerimaan Kas,Pindah Kas,Pengeluaran Kas'
            ]);

            DB::beginTransaction();

            // Get rekening koran data
            $rekeningKoran = DataRekeningKoran::where('rc_id', $validated['rekening_koran_id'])->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Rekening Koran tidak ditemukan.'
                ], 404);
            }

            // Check business rules: akunls_id is null and bku_id is null
            if ($rekeningKoran->akunls_id !== null && $rekeningKoran->bku_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak memenuhi syarat untuk membuat BKU. akunls_id dan bku_id harus null.'
                ], 422);
            }

            // Get BKU jenis ID 'jenisbku_id' based on type at table `master_jenisbku`
            $jenisBku = MasterJenisBku::where('jenisbku_nama', $validated['bku_type'])->first();
            if (!$jenisBku) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis BKU tidak ditemukan.'
                ], 404);
            }
            $jenisId = $jenisBku->jenisbku_id;

            // Create BKU data
            $bkuData = [
                'tgl_bku' => $rekeningKoran->tgl_rc,
                'jenis' => $jenisId,
                'ket' => $validated['ket_bku'],
                'uraian' => $rekeningKoran->uraian,
                'tgl' => now()
            ];

            // Generate BKU number
            [$noBKU, $noUrut] = $this->generateNoBku($jenisId, $rekeningKoran->tgl_rc);
            $bkuData['no_bku'] = $validated['no_bku']; // Use user input instead of generated
            $bkuData['nourut_bku'] = $noUrut;

            // Create BKU record
            $bku = \App\Models\DataBku::create($bkuData);

            // Update rekening koran with BKU ID
            $rekeningKoran->update([
                'bku_id' => $bku->bku_id,
                'no_bku' => $bku->no_bku,
                'ket_bku' => $validated['ket_bku'],
            ]);

            // Send to PAD Online
            $padResult = $this->sendBkuToPAD($bku, $rekeningKoran, $validated);

            if ($padResult['success']) {
                // Update BKU with PAD response
                $bku->update([
                    'pad_id' => $padResult['pad_id'],
                    'pad_tgl' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BKU berhasil dibuat dan dikirim ke PAD Online.',
                'data' => [
                    'bku_id' => $bku->bku_id,
                    'no_bku' => $bku->no_bku,
                    'pad_id' => $padResult['pad_id'] ?? null,
                    'pad_status' => $padResult['success'] ? 'success' : 'failed'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating BKU from Rekening Koran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send BKU data to PAD Online
     */
    private function sendBkuToPAD($bku, $rekeningKoran, $validated)
    {
        try {
            // Get PAD settings
            $settings = \App\Models\Setting::whereIn('key', [
                'sync_pad_url',
                'sync_pad_user',
                'sync_pad_password',
                'sync_tahun'
            ])->pluck('value', 'key');

            $padUrl = rtrim($settings['sync_pad_url'], '/');
            $padUser = $settings['sync_pad_user'];
            $padPassword = $settings['sync_pad_password'];
            $padTahun = $settings['sync_tahun'];

            // Get PAD token
            $tokenResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($padUrl . '/login', [
                'username' => $padUser,
                'password' => $padPassword,
                'aplikasi' => 'pad',
                'tahun' => $padTahun
            ]);

            if ($tokenResponse->failed()) {
                return [
                    'success' => false,
                    'message' => 'PAD login failed',
                    'error' => $tokenResponse->body()
                ];
            }

            $tokenData = $tokenResponse->json();
            $token = $tokenData['token'];

            // Get kas ke and kas dari from master_jenisbku
            $jenisBku = MasterJenisBku::where('jenisbku_id', $bku->jenis)->first();
            if (!$jenisBku) {
                return [
                    'success' => false,
                    'message' => 'Jenis BKU tidak ditemukan'
                ];
            }

            $padKaske = $jenisBku->bku_kaske;
            $padKasdari = $jenisBku->bku_kasdari;

            // Prepare request body based on debit/credit
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];

            if ($rekeningKoran->debit > 0) {
                $body = [
                    'id_jenis' => 2,
                    'no_bukti' => $validated['no_bku'],
                    'tgl_bukti' => $rekeningKoran->tgl_rc,
                    'dr_kas' => $padKasdari,
                    'ke_kas' => $padKaske,
                    'jml' => $rekeningKoran->debit,
                    'untuk' => $validated['ket_bku']
                ];
            } else {
                $body = [
                    'id_jenis' => 2,
                    'no_bukti' => $validated['no_bku'],
                    'tgl_bukti' => $rekeningKoran->tgl_rc,
                    'dr_kas' => $padKasdari,
                    'ke_kas' => $padKaske,
                    'jml' => $rekeningKoran->kredit,
                    'untuk' => $validated['ket_bku']
                ];
            }

            // Send to PAD
            $padResponse = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->post($padUrl . '/transfer-kas', $body);

            if ($padResponse->failed()) {
                return [
                    'success' => false,
                    'message' => 'Failed to send to PAD',
                    'error' => $padResponse->body()
                ];
            }

            $padData = $padResponse->json();

            return [
                'success' => true,
                'pad_id' => $padData['id'] ?? null,
                'message' => $padData['message'] ?? 'Success'
            ];

        } catch (\Exception $e) {
            Log::error('Error sending BKU to PAD: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending to PAD: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate BKU number (borrowed from BkuController)
     */
    private function generateNoBku(int $jenis, string $tglBKU)
    {
        // ambil tahun & bulan dari tgl_bku
        $tahun = date('Y', strtotime($tglBKU));
        $bulan = date('n', strtotime($tglBKU)); // 1 - 12

        $romawi = [
            '', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII',
            'VIII', 'IX', 'X', 'XI', 'XII'
        ];
        $bulanRomawi = $romawi[$bulan];

        // cari max nourut_bku dengan Eloquent
        $maxNoUrut = \App\Models\DataBku::where('jenis', $jenis)
            ->whereYear('tgl_bku', $tahun)
            ->whereMonth('tgl_bku', $bulan)
            ->max('nourut_bku');

        $newNoUrut = $maxNoUrut ? $maxNoUrut + 1 : 1;

        // format nomor bku -> contoh: BPN.2/0005/VII/2025
        $noBKU = sprintf("BPN.%d/%04d/%s/%d", $jenis, $newNoUrut, $bulanRomawi, $tahun);

        return [$noBKU, $newNoUrut];
    }
}
