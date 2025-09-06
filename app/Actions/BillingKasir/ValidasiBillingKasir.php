<?php
namespace App\Actions\BillingKasir;

use App\Models\DataRekeningKoran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DataPenerimaanLayanan;

class ValidasiBillingKasir
{
    public function handle($rcId)
    {
        $billingKasirTableName = (new DataPenerimaanLayanan())->getTable();
        $rekeningKoranTableName = (new DataRekeningKoran())->getTable();

        $klarifLayanan = DB::table($billingKasirTableName)
            ->select(DB::raw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0))'))
            ->where('rc_id', $rcId)
            ->value('sum');

        Log::info("Klarif Layanan: " . $klarifLayanan);

        DB::table($rekeningKoranTableName)
            ->where('rc_id', $rcId)
            ->update([
                'klarif_layanan' => $klarifLayanan,
                'akun_id'        => 1010102,
            ]);
    }
}
