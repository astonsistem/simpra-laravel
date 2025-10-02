<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataRekeningKoran;
use Carbon\Carbon;

class RekeningKoranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates dummy data for testing PB (Pindah Buku) functionality
     * 
     * Data includes:
     * - Bank Jatim records that meet PB criteria (kredit > 0, akun_id/akunls_id/bku_id null)
     * - Other bank records that can be linked to Bank Jatim
     * - Some records that don't meet PB criteria for testing visibility
     */
    public function run(): void
    {
        $baseDate = Carbon::now()->subDays(10);

        // 1. Bank Jatim records that MEET PB criteria (kredit > 0, all IDs null)
        $bankJatimRecords = [
            [
                'no_rc' => 'JATIM-001-2024',
                'tgl_rc' => $baseDate->copy()->addDays(1)->format('Y-m-d'),
                'uraian' => 'Transfer masuk dari BCA - Pembayaran Pasien',
                'bank' => 'JATIM',
                'kredit' => 5000000,
                'debit' => 0,
                'rek_dari' => '1234567890',
                'nama_dari' => 'PT. ABC',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null,
            ],
            [
                'no_rc' => 'JATIM-002-2024',
                'tgl_rc' => $baseDate->copy()->addDays(2)->format('Y-m-d'),
                'uraian' => 'Transfer masuk dari Mandiri - Pembayaran Layanan',
                'bank' => 'JATIM',
                'kredit' => 3500000,
                'debit' => 0,
                'rek_dari' => '9876543210',
                'nama_dari' => 'Yayasan XYZ',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null,
            ],
            [
                'no_rc' => 'JATIM-003-2024',
                'tgl_rc' => $baseDate->copy()->addDays(3)->format('Y-m-d'),
                'uraian' => 'Transfer masuk dari BNI - Pembayaran Rawat Inap',
                'bank' => 'JATIM',
                'kredit' => 7500000,
                'debit' => 0,
                'rek_dari' => '5555666677',
                'nama_dari' => 'CV. Maju Jaya',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null,
            ],
        ];

        // 2. Bank Jatim records that DON'T meet PB criteria (for testing visibility)
        $bankJatimInvalid = [
            [
                'no_rc' => 'JATIM-004-2024',
                'tgl_rc' => $baseDate->copy()->addDays(4)->format('Y-m-d'),
                'uraian' => 'Transfer keluar - Pembayaran Vendor',
                'bank' => 'JATIM',
                'kredit' => 0, // kredit = 0, should NOT show PB menu
                'debit' => 2000000,
                'rek_dari' => '1111222233',
                'nama_dari' => 'PT. Supplier',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null,
            ],
            [
                'no_rc' => 'JATIM-005-2024',
                'tgl_rc' => $baseDate->copy()->addDays(5)->format('Y-m-d'),
                'uraian' => 'Transfer masuk - Sudah terklarifikasi',
                'bank' => 'JATIM',
                'kredit' => 4000000,
                'debit' => 0,
                'rek_dari' => '3333444455',
                'nama_dari' => 'Pasien Umum',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => 1, // akun_id not null, should NOT show PB menu
                'akunls_id' => null,
                'bku_id' => null,
                'klarif_layanan' => 4000000,
                'klarif_lain' => 0,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null,
            ],
        ];

        // 3. Other bank records that CAN be linked (pb is null, bank != JATIM)
        $otherBankRecords = [
            // BCA Records
            [
                'no_rc' => 'BCA-001-2024',
                'tgl_rc' => $baseDate->copy()->format('Y-m-d'),
                'uraian' => 'Pembayaran pasien rawat jalan',
                'bank' => 'BCA',
                'kredit' => 2500000,
                'debit' => 0,
                'rek_dari' => '7777888899',
                'nama_dari' => 'Pasien A',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
            [
                'no_rc' => 'BCA-002-2024',
                'tgl_rc' => $baseDate->copy()->addDays(1)->format('Y-m-d'),
                'uraian' => 'Pembayaran pasien rawat inap',
                'bank' => 'BCA',
                'kredit' => 2500000,
                'debit' => 0,
                'rek_dari' => '8888999900',
                'nama_dari' => 'Pasien B',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
            
            // Mandiri Records
            [
                'no_rc' => 'MANDIRI-001-2024',
                'tgl_rc' => $baseDate->copy()->addDays(1)->format('Y-m-d'),
                'uraian' => 'Pembayaran administrasi',
                'bank' => 'MANDIRI',
                'kredit' => 1500000,
                'debit' => 0,
                'rek_dari' => '4444555566',
                'nama_dari' => 'Pasien C',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
            [
                'no_rc' => 'MANDIRI-002-2024',
                'tgl_rc' => $baseDate->copy()->addDays(2)->format('Y-m-d'),
                'uraian' => 'Pembayaran laboratorium',
                'bank' => 'MANDIRI',
                'kredit' => 2000000,
                'debit' => 0,
                'rek_dari' => '6666777788',
                'nama_dari' => 'Pasien D',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
            
            // BNI Records
            [
                'no_rc' => 'BNI-001-2024',
                'tgl_rc' => $baseDate->copy()->addDays(2)->format('Y-m-d'),
                'uraian' => 'Pembayaran operasi',
                'bank' => 'BNI',
                'kredit' => 5000000,
                'debit' => 0,
                'rek_dari' => '2222333344',
                'nama_dari' => 'Pasien E',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
            [
                'no_rc' => 'BNI-002-2024',
                'tgl_rc' => $baseDate->copy()->addDays(3)->format('Y-m-d'),
                'uraian' => 'Pembayaran farmasi',
                'bank' => 'BNI',
                'kredit' => 2500000,
                'debit' => 0,
                'rek_dari' => '9999000011',
                'nama_dari' => 'Pasien F',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'akun_id' => null,
                'akunls_id' => null,
                'bku_id' => null,
                'mutasi' => false,
                'pb_dari' => null,
                'pb' => null, // Can be linked
            ],
        ];

        // Insert all records
        echo "Seeding Bank Jatim records (valid for PB)...\n";
        foreach ($bankJatimRecords as $record) {
            DataRekeningKoran::create($record);
        }

        echo "Seeding Bank Jatim records (invalid for PB - for testing)...\n";
        foreach ($bankJatimInvalid as $record) {
            DataRekeningKoran::create($record);
        }

        echo "Seeding other bank records (can be linked)...\n";
        foreach ($otherBankRecords as $record) {
            DataRekeningKoran::create($record);
        }

        echo "\n✅ Seeding completed!\n";
        echo "Summary:\n";
        echo "- Bank Jatim (valid for PB): " . count($bankJatimRecords) . " records\n";
        echo "- Bank Jatim (invalid for PB): " . count($bankJatimInvalid) . " records\n";
        echo "- Other banks (can be linked): " . count($otherBankRecords) . " records\n";
        echo "\nTotal: " . (count($bankJatimRecords) + count($bankJatimInvalid) + count($otherBankRecords)) . " records\n";
    }
}
