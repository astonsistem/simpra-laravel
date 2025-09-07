<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

if (!function_exists('normalize_date')) {
    function normalize_date($date, $format = 'Y-m-d')
    {
        try {
            if (strpos($date, '/') !== false) {
                $dt = Carbon::createFromFormat( "d/m/Y", $date );
            } else{
                $dt = Carbon::parse($date);
            }
            return $dt->format( $format );
        } catch (\Exception $e) {
            return null;
        }
    }
}
