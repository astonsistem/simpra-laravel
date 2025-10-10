<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCaraPembayaran extends Model
{
    protected $table = "master_carapembayaran";
    protected $keyType = 'string';
    protected $primaryKey = 'bayar_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bayar_id',
        'bayar_nama',
        'is_aktif',
    ];

    protected $casts = [
        'bayar_id' => 'string',
    ];

    public static function getValueFromLabel($label)
    {
        if(!$label) return null;

        $value = strtoupper($label);

        switch ($value) {
            case 'S-TAPAY':
                $value = 'STPAY';
                break;
            case 'UE READER':
                $value = 'READER';
                break;
        }

        return $value;
    }
}
