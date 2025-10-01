# Template Format Excel untuk Import Bank

## Bank JATIM

### Format Header Excel:
| Post Date | Effective Date | Account | Name | Description | Currency | Debit | Credit | Balance | Reference No |
|-----------|---------------|---------|------|-------------|----------|-------|--------|---------|--------------|

### Contoh Data:
| Post Date | Effective Date | Account | Name | Description | Currency | Debit | Credit | Balance | Reference No |
|-----------|---------------|---------|------|-------------|----------|-------|--------|---------|--------------|
| 2024-01-15 | 2024-01-15 | 1234567890 | PT ABC | Transfer masuk | IDR | 0 | 1000000 | 5000000 | TRX001 |
| 2024-01-16 | 2024-01-16 | 1234567890 | PT XYZ | Transfer keluar | IDR | 500000 | 0 | 4500000 | TRX002 |

### Ketentuan:
1. **Post Date** wajib diisi (format tanggal)
2. **Reference No** wajib diisi (nomor referensi unik)
3. Kolom lain opsional
4. Hanya baris dengan Post Date yang akan diimpor
5. Data dengan Reference No sama akan di-update

### Cara Penggunaan:
1. Siapkan file Excel dengan format di atas
2. Pastikan header sesuai dengan template
3. Isi data sesuai format
4. Upload melalui menu Import Bank Pilihan
5. Pilih Bank JATIM
6. Preview data sebelum sinkronisasi
