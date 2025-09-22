<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuktiSetorCollection;
use App\Http\Resources\BuktiSetorResource;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BuktiSetorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tahun_periode' => 'nullable|string',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|string',
                'total_setor' => 'nullable|array',
                'total_setor.value' => 'nullable|integer',
                'total_setor.matchMode' => 'nullable|string|in:equals,notEquals,gt,gte,lt,lte',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tahunPeriode = $request->input('tahun_periode');
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $uraian = $request->input('uraian');
            $bank = $request->input('bank');
            $totalSetor = data_get($request->input('total_setor'), 'value');

            $query = DataRekeningKoran::withKwitansiSummary();

            if (!empty($tahunPeriode)) {
                $query->whereYear('tgl_rc', $tahunPeriode);
            }
            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_rc', [$startDate, $endDate]);
            }
            if (!empty($uraian)) {
                $query->where('uraian', 'ILIKE', "%$uraian%");
            }
            if (!empty($bank)) {
                $query->where('bank', 'ILIKE', "%$bank%");
            }
            if (!empty($totalSetor)) {
                $matchMode = data_get($request->input('total_setor'), 'matchMode');

                $mapMatchMode = [
                    'equals' => '=',
                    'notEquals' => '!=',
                    'gt' => '>',
                    'gte' => '>=',
                    'lt' => '<',
                    'lte' => '<=',
                ];

                if (isset($mapMatchMode[$matchMode])) {
                    $query->havingRaw(
                        "(SUM(kw.total) - SUM(kw.admin_kredit) + SUM(kw.selisih)) {$mapMatchMode[$matchMode]} ?",
                        [$totalSetor]
                    );
                }
            }

            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder');
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $totalItems = $query->toBase()->getCountForPagination();
            $items = $query->skip(($page - 1) * $size)->take($size)->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new BuktiSetorCollection($items, $totalItems, $page, $size, $totalPages)
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

    public function statistik()
    {
        $currentMonth = Carbon::now()->format('m');

        $sum = DataRekeningKoran::sumBuktiSetor($currentMonth);
        $count = DataRekeningKoran::countBuktiSetor($currentMonth);
        $sumCurrent = DataRekeningKoran::sumBuktiSetorCurrent($currentMonth);
        $countCurrent = DataRekeningKoran::countBuktiSetorCurrent($currentMonth);

        return response()->json([
            'sum' => $sum,
            'count' => $count,
            'sum_current' => $sumCurrent,
            'count_current' => $countCurrent,
        ]);
    }

    public function show(string $id)
    {
        try {
            $buktiSetor = DataRekeningKoran::withKwitansiSummary()->where('data_rekening_koran.rc_id', $id)->first();

            if (!$buktiSetor) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new BuktiSetorResource($buktiSetor)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
