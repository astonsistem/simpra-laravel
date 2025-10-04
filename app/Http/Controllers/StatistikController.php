<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\StatistikService;

class StatistikController extends Controller
{
    protected $statistikService;

    public function __construct(StatistikService $statistikService)
    {
        $this->statistikService = $statistikService;
    }

    public function index(): JsonResponse
    {
        $saldoKas = $this->statistikService->getSaldoKas();
        $penerimaan = $this->statistikService->getPenerimaan();
        $potensiPenerimaan = $this->statistikService->getPotensiPenerimaan();

        $realisasiPendapatanNetto = $this->statistikService->getRealisasiPendapatanNetto();
        $realisasiPendapatanJumlah = $this->statistikService->getRealisasiPendapatanJumlah();

        $monevPenerimaanLainnya = $this->statistikService->getMonevPenerimaanLainnya();
        $monevPenerimaanLainnyaAll = $this->statistikService->getMonevPenerimaanLainnyaAll();
        $monevPenerimaanLayanan = $this->statistikService->getMonevPenerimaanLayanan();
        $monevPenerimaanLayananAll = $this->statistikService->getMonevPenerimaanLayananAll();
        $monevRekeningKoran = $this->statistikService->getMonevRekeningKoran();
        $monevRekeningKoranAll = $this->statistikService->getMonevRekeningKoranAll();
        
        $komposisiTargetPendapatanLayanan = $this->statistikService->getKomposisiTargetPendapatanLayanan();
        $komposisiTargetPendapatanNotLayanan = $this->statistikService->getKomposisiTargetPendapatanNotLayanan();
        $komposisiTargetPendapatanAll = $komposisiTargetPendapatanLayanan + $komposisiTargetPendapatanNotLayanan;

        $jumlahPersonil = $this->statistikService->getJumlahPersonil();
        $jumlahPenjamin = $this->statistikService->getJumlahPenjamin();
        $jumlahLoket = $this->statistikService->getJumlahLoket();
        $jumlahInstalasi = $this->statistikService->getJumlahInstalasi();

        $pendapatanSelainRetribusi = $this->statistikService->getPendapatanSelainRetribusi();

        $pendapatanDokumenPenerimaan = $this->statistikService->getpendapatanDokumenPenerimaan();

        return response()->json([
            'saldoKas' => $saldoKas,
            'penerimaan' => $penerimaan,
            'potensiPenerimaan' => $potensiPenerimaan,
            'realisasiPendapatanNetto' => $realisasiPendapatanNetto,
            'realisasiPendapatanJumlah' => $realisasiPendapatanJumlah,
            'monevPenerimaanLainnya' => $monevPenerimaanLainnya,
            'monevPenerimaanLainnyaAll' => $monevPenerimaanLainnyaAll,
            'monevPenerimaanLayanan' => $monevPenerimaanLayanan,
            'monevPenerimaanLayananAll' => $monevPenerimaanLayananAll,
            'monevRekeningKoran' => $monevRekeningKoran,
            'monevRekeningKoranAll' => $monevRekeningKoranAll,
            'komposisiTargetPendapatanLayanan' => $komposisiTargetPendapatanLayanan,
            'komposisiTargetPendapatanNotLayanan' => $komposisiTargetPendapatanNotLayanan,
            'komposisiTargetPendapatanAll' => $komposisiTargetPendapatanAll,
            'jumlahPersonil' => $jumlahPersonil,
            'jumlahPenjamin' => $jumlahPenjamin,
            'jumlahLoket' => $jumlahLoket,
            'jumlahInstalasi' => $jumlahInstalasi,
            'pendapatanSelainRetribusi' => $pendapatanSelainRetribusi,
            'pendapatanDokumenPenerimaan' => $pendapatanDokumenPenerimaan,
        ]);
    }
}
