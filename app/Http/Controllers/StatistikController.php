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
        $sumPendapatan = $this->statistikService->getSumPendapatan();
        $countPasien = $this->statistikService->getCountPasien();
        $topInstalasi = $this->statistikService->getTopInstalasi();
        $sumPotensiAll = $this->statistikService->getSumAllPotensi();
        $sumPotensiPelayanan = $this->statistikService->getSumPotensiPelayanan();
        $sumPotensiLain = $this->statistikService->getSumPotensiLain();
        $countPotensi = $this->statistikService->countPotensiPelayanans();
        $pendapatanChart = $this->statistikService->getSumPendapatanChart();
        $anggaranChart = $this->statistikService->getTotalAnggaran();
        $pendapatanPenjamin = $this->statistikService->getPendapatanPelayananPenjamin();
        $sumPenerimaanLain = $this->statistikService->getSumPenerimaanLainTahunini();
        $sumPotensiLayananLain = $sumPotensiPelayanan + $sumPotensiLain;

        $sumPenerimaan = $this->statistikService->getSumTotalByPaymentMethod('LANGSUNG');
        $countPenerimaan = $this->statistikService->getCountTotalByPaymentMethod('LANGSUNG');

        $carabayar = $this->statistikService->getCountCarabayar();
        $penjamin = $this->statistikService->getCountPenjamin();
        $instalasi = $this->statistikService->getCountInstalasi();

        return response()->json([
            'pendapatan' => $sumPendapatan,
            'pasien' => $countPasien,
            'top_instalasi' => $topInstalasi,
            'sum_potensi' => $sumPotensiAll,
            'sumpotensipelayanan' => $sumPotensiPelayanan,
            'sum_penerimaan' => $sumPenerimaan,
            'count_penerimaan' => $countPenerimaan,
            'count_potensi' => $countPotensi,
            'penjamin' => $penjamin,
            'instalasi' => $instalasi,
            'pendapatanchart' => $pendapatanChart,
            'anggaranchart' => $anggaranChart,
            'pendapatanpenjaminchart' => $pendapatanPenjamin,
            'sumpenerimaanpotensi' => $sumPenerimaanLain,
            'sumpotensilayananlain' => $sumPotensiLayananLain
        ]);
    }
}
