<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\CaraBayarController;
use App\Http\Controllers\InstalasiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PenjaminController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\StatistikController;

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

Route::apiResource('akun', AkunController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::get('akun/list', [AkunController::class, 'list']);
Route::get('akun/list/akunpotensilain', [AkunController::class, 'listAkunPotensiLain']);
Route::get('akun/list/pendapatan', [AkunController::class, 'listPendapatan']);

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

Route::get('bukti_setor', [LoketController::class, 'index']);
Route::get('bukti_setor/statistik', [LoketController::class, 'statistics']);

Route::get('rekening_koran', [RekapController::class, 'pasien_bpjs']);

Route::get('syncapi', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/..', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/list/..', [RekapController::class, 'pasien_bpjs']);
Route::get('syncapi/menu', [RekapController::class, 'pasien_bpjs']);
Route::post('syncapi', [RekapController::class, 'pasien_bpjs']);
Route::put('syncapi', [RekapController::class, 'pasien_bpjs']);

Route::get('sinkronisasi/request', [RekapController::class, 'pasien_bpjs']);
Route::get('sinkronisasi/request/kasir', [RekapController::class, 'pasien_bpjs']);
Route::get('sinkronisasi/request/penerimaanumum', [RekapController::class, 'pasien_bpjs']);
Route::get('sinkronisasi/request/rincianpendapatan', [RekapController::class, 'pasien_bpjs']);
Route::get('sinkronisasi/save', [RekapController::class, 'pasien_bpjs']);

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

Route::get('pasienbpjs', [RekapController::class, 'pasien_bpjs']);
Route::get('pasienbpjs/tarik', [RekapController::class, 'pasien_ranap_bpjs']);
Route::get('pasienbpjs/tarik/tarik', [RekapController::class, 'pasien_ranap_bpjs']);
