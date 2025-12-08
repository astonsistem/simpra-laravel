<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterPelaporan extends Model
{
    protected $table = 'master_pelaporan';

    protected $appends = ['slug', 'group', 'label', 'to'];

    protected $casts = [
        'params' => 'array',
    ];

    public function getSlugAttribute()
    {
        return \Str::slug($this->nama_laporan, '_');
    }

    public function getGroupAttribute()
    {
        return collect(explode(' ', $this->nama_laporan))
            ->take(3)
            ->implode(' ');
    }

    public function getLabelAttribute()
    {
        return "$this->kode_laporan - $this->nama_laporan";
    }

    public function getToAttribute()
    {
        return '/pelaporan/' . $this->slug;
    }

    public function getResolvedParamsAttribute()
    {
        return collect($this->params)->map(function ($param) {
            // Enrich only if type is 'sql_combo' and has valid SQL
            if ( $param['type'] === 'sql_combo' && isset($param['sql_data'], $param['sql_value'], $param['sql_name']) ) {
                // Execute the raw SQL
                $results = DB::select($param['sql_data']);
                // Map each row to { value: ..., label: ... }
                $param['data'] = collect($results)->map(function ($row) use ($param) {
                    $row = (array) $row; // Convert to array
                    return [
                        'value' => $row[$param['sql_value']],
                        'name' => $row[$param['sql_name']],
                    ];
                })->toArray();
            }
            return $param;
        })->toArray();
    }
}
