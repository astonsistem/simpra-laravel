# Rekening Koran Seeder

Seeder untuk membuat dummy data rekening koran yang dapat digunakan untuk testing fitur PB (Pindah Buku).

## Data yang Dibuat

### 1. Bank Jatim - Valid untuk PB (3 records)
Records yang **MEMENUHI** kriteria PB:
- ✅ `kredit > 0`
- ✅ `akun_id = null`
- ✅ `akunls_id = null`
- ✅ `bku_id = null`

**Records:**
- `JATIM-001-2024` - Kredit: Rp 5.000.000 (dari BCA)
- `JATIM-002-2024` - Kredit: Rp 3.500.000 (dari Mandiri)
- `JATIM-003-2024` - Kredit: Rp 7.500.000 (dari BNI)

### 2. Bank Jatim - Invalid untuk PB (2 records)
Records yang **TIDAK MEMENUHI** kriteria PB (untuk testing visibility):

**JATIM-004-2024:**
- ❌ `kredit = 0` (debit = 2.000.000)
- Menu PB tidak akan tampil

**JATIM-005-2024:**
- ❌ `akun_id = 1` (sudah terklarifikasi)
- Menu PB tidak akan tampil

### 3. Bank Lain - Dapat Ditautkan (6 records)
Records dari bank lain yang dapat ditautkan ke Bank Jatim:

**BCA (2 records):**
- `BCA-001-2024` - Kredit: Rp 2.500.000
- `BCA-002-2024` - Kredit: Rp 2.500.000

**Mandiri (2 records):**
- `MANDIRI-001-2024` - Kredit: Rp 1.500.000
- `MANDIRI-002-2024` - Kredit: Rp 2.000.000

**BNI (2 records):**
- `BNI-001-2024` - Kredit: Rp 5.000.000
- `BNI-002-2024` - Kredit: Rp 2.500.000

## Cara Menjalankan

### Opsi 1: Jalankan Semua Seeder
```bash
php artisan db:seed
```

### Opsi 2: Jalankan Hanya RekeningKoranSeeder
```bash
php artisan db:seed --class=RekeningKoranSeeder
```

### Opsi 3: Fresh Migration + Seed
```bash
php artisan migrate:fresh --seed
```

## Testing Scenario

### 1. Test Menu PB Visibility
- Buka halaman Data Rekening Koran
- **Expected:** Menu PB hanya tampil pada 3 record Bank Jatim yang valid
- **Expected:** Menu PB TIDAK tampil pada JATIM-004 dan JATIM-005

### 2. Test Tab 1: Ubah ID Transaksi
- Klik PB pada `JATIM-001-2024`
- Edit checkbox Mutasi
- Pilih "BCA" di dropdown PB dari Bank
- Klik Simpan
- **Expected:** Data tersimpan dengan mutasi dan pb_dari terisi

### 3. Test Tab 3: Daftarkan Mutasi
- Klik PB pada `JATIM-001-2024`
- Buka Tab 3 "Mutasi bank yang belum dimasukkan"
- **Expected:** Tampil record BCA-001, BCA-002, MANDIRI-001 (tgl_rc <= JATIM-001)
- Klik "Daftarkan" pada BCA-001
- **Expected:** BCA-001 terhubung ke JATIM-001

### 4. Test Tab 2: Batalkan Mutasi
- Setelah step 3, buka Tab 2 "Mutasi bank yang dimasukkan"
- **Expected:** Tampil BCA-001 yang sudah ditautkan
- Klik "Batalkan" pada BCA-001
- **Expected:** BCA-001 kembali ke Tab 3 (pb = null)

### 5. Test Linking Logic
- Link BCA-001 dan BCA-002 ke JATIM-001
- **Expected:** Total kredit linked = Rp 5.000.000 (sama dengan kredit JATIM-001)
- Link MANDIRI-001 dan MANDIRI-002 ke JATIM-002
- **Expected:** Total kredit linked = Rp 3.500.000 (sama dengan kredit JATIM-002)

## Database Structure

```sql
-- Bank Jatim yang valid untuk PB
SELECT * FROM data_rekening_koran 
WHERE bank = 'JATIM' 
  AND kredit > 0 
  AND akun_id IS NULL 
  AND akunls_id IS NULL 
  AND bku_id IS NULL;

-- Bank lain yang dapat ditautkan
SELECT * FROM data_rekening_koran 
WHERE pb IS NULL 
  AND bank != 'JATIM';

-- Mutasi yang sudah ditautkan ke Bank Jatim tertentu
SELECT * FROM data_rekening_koran 
WHERE pb = 'JATIM-001-2024';
```

## Cleanup

Untuk menghapus data dummy:

```bash
# Hapus semua data rekening koran
php artisan tinker
>>> App\Models\DataRekeningKoran::truncate();

# Atau hapus hanya data dari seeder (berdasarkan no_rc pattern)
>>> App\Models\DataRekeningKoran::where('no_rc', 'LIKE', '%-2024')->delete();
```

## Notes

- Semua tanggal dibuat relatif terhadap hari ini (10 hari yang lalu + offset)
- Data dibuat dengan `tgl_rc` yang berurutan untuk memudahkan testing filter tanggal
- Setiap record memiliki `no_rc` unik untuk menghindari konflik
- Field `tgl` diisi dengan tanggal hari ini
- Field `pb`, `mutasi`, dan `pb_dari` diisi null/false untuk testing awal
