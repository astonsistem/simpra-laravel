<?php

namespace App\Http\Controllers;

use App\Http\Resources\PasienBpjsCollection;
use App\Models\StatusPasienBpjs;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PasienBpjsController extends Controller
{

    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'no_rekam_medik' => 'nullable|string',
                'pasien_nama' => 'nullable|string',
                'no_sep' => 'nullable|string',
                'no_pendaftaran' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 1000) ?? 1000;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $noRekamMedik = $request->input('no_rekam_medik');
            $pasienNama = $request->input('pasien_nama');
            $noSep = $request->input('no_sep');
            $noPendaftaran = $request->input('no_pendaftaran');

            $query = StatusPasienBpjs::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($noRekamMedik)) {
                $query->where('no_rekam_medik', $noRekamMedik);
            }
            if (!empty($pasienNama)) {
                $query->where('pasien_nama', 'ILIKE', $pasienNama);
            }
            if (!empty($noSep)) {
                $query->where('no_sep', 'ILIKE', $noSep);
            }
            if (!empty($noPendaftaran)) {
                $query->where('no_pendaftaran', 'ILIKE', $noPendaftaran);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_pelayanan', 'desc')->orderBy('pasien_nama', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PasienBpjsCollection($items, $totalItems, $page, $size, $totalPages)
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


    public function tarik(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'no_rekam_medik' => 'nullable|string',
                'pasien_nama' => 'nullable|string',
                'no_sep' => 'nullable|string',
                'no_pendaftaran' => 'nullable|string',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 1000) ?? 1000;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $noRekamMedik = $request->input('no_rekam_medik');
            $pasienNama = $request->input('pasien_nama');
            $noSep = $request->input('no_sep');
            $noPendaftaran = $request->input('no_pendaftaran');

            $query = StatusPasienBpjs::query();

            if (!empty($tglAwal) && !empty($tglAkhir)) {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_pelayanan', [$startDate, $endDate]);
            }
            if (!empty($noRekamMedik)) {
                $query->where('no_rekam_medik', $noRekamMedik);
            }
            if (!empty($pasienNama)) {
                $query->where('pasien_nama', 'ILIKE', $pasienNama);
            }
            if (!empty($noSep)) {
                $query->where('no_sep', 'ILIKE', $noSep);
            }
            if (!empty($noPendaftaran)) {
                $query->where('no_pendaftaran', 'ILIKE', $noPendaftaran);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_pelayanan', 'desc')->orderBy('pasien_nama', 'desc')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new PasienBpjsCollection($items, $totalItems, $page, $size, $totalPages)
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
}
