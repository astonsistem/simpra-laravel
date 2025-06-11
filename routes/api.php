<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BillingKasirController;
use App\Http\Controllers\BillingSwaController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\CaraBayarController;
use App\Http\Controllers\DataClosingController;
use App\Http\Controllers\InstalasiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PasienBpjsController;
use App\Http\Controllers\PendapatanPelayananController;
use App\Http\Controllers\PenerimaanLainController;
use App\Http\Controllers\PenjaminController;
use App\Http\Controllers\PotensiLainController;
use App\Http\Controllers\PotensiPelayananController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekeningKoranController;
use App\Http\Controllers\SinkronisasiController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\TempPenerimaanSwaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('auth/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});

Route::get('akun', [AkunController::class, 'index']);
Route::get('akun/{id}', [AkunController::class, 'show']);
Route::get('akun/list', [AkunController::class, 'list']);
Route::get('akun/list/akunpotensilain', [AkunController::class, 'listAkunPotensiLain']);
Route::get('akun/list/pendapatan', [AkunController::class, 'listPendapatan']);
Route::post('akun', [AkunController::class, 'store']);
Route::put('akun/{id}', [AkunController::class, 'update']);
Route::delete('akun/{id}', [AkunController::class, 'destroy']);

Route::get('carabayar', [CaraBayarController::class, 'index']);
Route::get('carabayar/list', [CaraBayarController::class, 'list']);

Route::get('penjamin', [PenjaminController::class, 'index']);
Route::get('penjamin/list', [PenjaminController::class, 'list']);
Route::get('penjamin/sync', [PenjaminController::class, 'sync']);

Route::get('instalasi', [InstalasiController::class, 'index']);
Route::get('instalasi/list', [InstalasiController::class, 'list']);
Route::get('instalasi/sync', [InstalasiController::class, 'sync']);

Route::get('kasir', [KasirController::class, 'index']);
Route::get('kasir/list', [KasirController::class, 'list']);
Route::get('kasir/sync', [KasirController::class, 'sync']);

Route::get('loket', [LoketController::class, 'index']);
Route::get('loket/list', [LoketController::class, 'list']);
Route::get('loket/sync', [LoketController::class, 'sync']);

Route::get('syncapi', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/..', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/list/..', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/menu', [RekapController::class, 'pasien_bpjs']);
Route::post('syncapi', [RekapController::class, 'pasien_bpjs']);
Route::put('syncapi', [RekapController::class, 'pasien_bpjs']);

Route::post('sinkronisasi/request', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/kasir', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/penerimaanumum', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/rincianpendapatan', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/save', [SinkronisasiController::class, 'pasien_bpjs']);

Route::get('pendapatan_pelayanan', [PendapatanPelayananController::class, 'index']);
Route::get('pendapatan_pelayanan/{id}', [PendapatanPelayananController::class, 'show']);
Route::get('pendapatan_pelayanan/statisik', [PendapatanPelayananController::class, 'statistik']);

Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir', [BillingKasirController::class, 'index']);

Route::get('billing_swa', [BillingSwaController::class, 'pasien_bpjs']);

Route::get('potensi_pelayanan', [PotensiPelayananController::class, 'pasien_bpjs']);

Route::get('penerimaan_lain', [PenerimaanLainController::class, 'pasien_bpjs']);

Route::get('potensi_lain', [PotensiLainController::class, 'pasien_bpjs']);

Route::get('data_closing', [DataClosingController::class, 'pasien_bpjs']);

Route::get('temp_penerimaan_swa', [TempPenerimaanSwaController::class, 'pasien_bpjs']);

Route::get('rekening_koran', [RekeningKoranController::class, 'pasien_bpjs']);

Route::get('bukti_setor', [BkuController::class, 'pasien_bpjs']);
Route::get('bukti_setor/statistik', [BkuController::class, 'pasien_bpjs']);

Route::get('bku', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/listbku', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/bku', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/statistik', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/bku_post', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/bku_put', [BkuController::class, 'pasien_bpjs']);
Route::get('bku/all_bku', [BkuController::class, 'pasien_bpjs']);

Route::get('statistik/dashboard', [StatistikController::class, 'dashboard']);

Route::get('rekap/pasien_rawat_jalan_bpjs', [RekapController::class, 'pasien_rajal_bpjs']);
Route::get('rekap/pasien_rawat_inap_bpjs', [RekapController::class, 'pasien_ranap_bpjs']);

Route::get('pasienbpjs', [PasienBpjsController::class, 'index']);
Route::get('pasienbpjs/tarik', [PasienBpjsController::class, 'pasien_ranap_bpjs']);
Route::get('pasienbpjs/tarik/tarik', [PasienBpjsController::class, 'pasien_ranap_bpjs']);
