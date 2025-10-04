<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_rekening_koran', function (Blueprint $table) {
            $table->integer('pad_id')->nullable()->comment('PAD Online ID from response');
            $table->date('pad_tgl')->nullable()->comment('Date when data was sent to PAD Online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_rekening_koran', function (Blueprint $table) {
            $table->dropColumn(['pad_id', 'pad_tgl']);
        });
    }
};
