# Rekening Koran - Filter & Sort Implementation

## Overview
Implementasi lengkap untuk fitur filter dan sort pada modul Rekening Koran.

## Features Implemented

### 1. Filter Data by Periode Tanggal ✅

#### Filter Bulanan
- **Parameter**: `periode=BULANAN`, `year`, `bulan_awal`, `bulan_akhir`
- **Contoh**: Filter data bulan Januari-Maret 2025
  ```
  GET /rekening_koran?periode=BULANAN&year=2025&bulan_awal=1&bulan_akhir=3
  ```

#### Filter Tanggal
- **Parameter**: `periode=TANGGAL`, `tgl_awal`, `tgl_akhir`
- **Contoh**: Filter data tanggal 1-31 Januari 2025
  ```
  GET /rekening_koran?periode=TANGGAL&tgl_awal=2025-01-01&tgl_akhir=2025-01-31
  ```

### 2. Filter Per Column ✅

Semua kolom pada tabel dapat difilter:

| Column | Parameter | Type | Contoh |
|--------|-----------|------|--------|
| No. RC | `no_rc` | String (ILIKE) | `no_rc=RC001` |
| Tgl. RC | `tgl_rc` | Date | `tgl_rc=2025-01-15` |
| Uraian | `uraian` | String (ILIKE) | `uraian=transfer` |
| Klarifikasi Monev | `akun_data` | String (ILIKE) | `akun_data=pendapatan` |
| Verifikasi Langsung | `akunls_data` | String (ILIKE) | `akunls_data=layanan` |
| Bank | `bank` | String (ILIKE) | `bank=BCA` |
| PB dari Bank | `pb` | String (ILIKE) | `pb=PB001` |
| Debit | `debit` | Numeric | `debit=1000000` |
| Kredit | `kredit` | Numeric | `kredit=500000` |
| Terklarifikasi | `terklarifikasi` | Numeric | `terklarifikasi=300000` |
| Belum Terklarifikasi | `belum_terklarifikasi` | Numeric | `belum_terklarifikasi=200000` |
| Rekening DPA | `rekening_dpa` | String (ILIKE) | `rekening_dpa=DPA001` |

**Contoh kombinasi filter:**
```
GET /rekening_koran?bank=BCA&kredit=1000000&tgl_awal=2025-01-01&tgl_akhir=2025-01-31
```

### 3. Sort Order ✅

Semua kolom dapat di-sort ascending atau descending:

- **Parameter**: `sort_field`, `sort_order`
- **Sort Order**: `1` = ascending, `-1` = descending
- **Default**: Sort by `tgl_rc` descending

**Contoh:**
```
GET /rekening_koran?sort_field=no_rc&sort_order=1
GET /rekening_koran?sort_field=kredit&sort_order=-1
```

#### Special Sort Handling

Beberapa kolom memerlukan handling khusus:

1. **akun_data** - Join dengan `master_akun` untuk sort by nama akun
2. **akunls_data** - Join dengan `master_akun` untuk sort by nama akun LS
3. **rekening_dpa** - Join dengan `master_rekening_view` untuk sort by nama rekening
4. **terklarifikasi** - Sort by calculated field `(klarif_layanan + klarif_lain)`
5. **belum_terklarifikasi** - Sort by calculated field `(kredit - klarif_layanan - klarif_lain)`

### 4. Clear All Filters ✅

Tombol "Clear" untuk menghapus semua filter sekaligus:

- **Fungsi**: Menghapus filter periode, filter kolom, dan sort order
- **Reset ke**: Default state (sort by `tgl_rc` descending, no filters)
- **UI**: Tombol "Clear" dengan icon filter-slash di header tabel

**Implementasi:**
- Frontend: `clearFilter()` di `DataRekeningKoran.vue` memanggil `clearTableFilter()` dan `resetFilter()` di FilterDataTable
- Composable: Reset `filters`, `additionalFilters`, `sort`, dan `first` (pagination)

## Backend Implementation

### Controller: `RekeningKoranController.php`

**Method**: `index(Request $request)`

**Validation Rules:**
```php
'page' => 'nullable|integer|min:1',
'per_page' => 'nullable|integer|min:1',
'tgl_awal' => 'nullable|string',
'tgl_akhir' => 'nullable|string',
'bulan_awal' => 'nullable|string',
'bulan_akhir' => 'nullable|string',
'year' => 'nullable|string',
'periode' => 'nullable|string',
'no_rc' => 'nullable|string',
'tgl_rc' => 'nullable|string',
'uraian' => 'nullable|string',
'akun_data' => 'nullable|string',
'akunls_data' => 'nullable|string',
'bank' => 'nullable|string',
'pb' => 'nullable|string',
'debit' => 'nullable|numeric',
'kredit' => 'nullable|numeric',
'terklarifikasi' => 'nullable|numeric',
'belum_terklarifikasi' => 'nullable|numeric',
'rekening_dpa' => 'nullable|string',
'sort_field' => 'nullable|string',
'sort_order' => 'nullable|integer',
```

**Filter Logic:**
1. Period filters (date range, month range, year)
2. Column filters (ILIKE for strings, exact match for numbers)
3. Relationship filters (whereHas for related tables)
4. Global search
5. Sort order

## Frontend Implementation

### Composable: `useRekeningKoran.js`

**Key Functions:**
- `buildFromFilters` - Computed property yang mengkonversi filter object ke query params
- `update(event)` - Handler untuk filter & sort events dari DataTable
- `loadData(params)` - Fetch data dengan filter & sort params

**Filter Handling:**
```javascript
filters.value = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  tgl_rc: { value: null, matchMode: FilterMatchMode.DATE_IS },
  no_rc: { value: null, matchMode: FilterMatchMode.CONTAINS },
  uraian: { value: null, matchMode: FilterMatchMode.CONTAINS },
  akun_data: { value: null, matchMode: FilterMatchMode.CONTAINS },
  akunls_data: { value: null, matchMode: FilterMatchMode.CONTAINS },
  bank: { value: null, matchMode: FilterMatchMode.CONTAINS },
  pb: { value: null, matchMode: FilterMatchMode.CONTAINS },
  debit: { value: null, matchMode: FilterMatchMode.EQUALS },
  kredit: { value: null, matchMode: FilterMatchMode.EQUALS },
  rekening_dpa: { value: null, matchMode: FilterMatchMode.CONTAINS },
}
```

**Sort Handling:**
```javascript
if (event.sortField !== null && event.sortOrder !== null) {
  sort.value.sort_field = event.sortField
  sort.value.sort_order = event.sortOrder
}
```

**Clear Filter:**
```javascript
function clearFilter() {
  initFilters()
  additionalFilters.value = {}
  sort.value = {}
  first.value = 0
  loadData()
}
```

### Component: `DataRekeningKoran.vue`

**DataTable Configuration:**
- `lazy` - Server-side pagination
- `paginator` - Enable pagination
- `sortable` - Enable column sorting
- `filterDisplay="menu"` - Show filter menu per column
- `@filter` - Filter event handler
- `@sort` - Sort event handler

**Column Templates:**
```vue
<Column field="no_rc" header="No. RC" sortable 
        :showFilterMatchModes="false" :showClearButton="true">
  <template #filter="{ filterModel, applyFilter }">
    <InputText v-model="filterModel.value" @keyup.enter="applyFilter" 
               placeholder="Search by No. RC" />
  </template>
</Column>
```

## Testing

### Test Filter Periode Tanggal
```bash
# Filter bulanan
curl "http://localhost:8000/api/rekening_koran?periode=BULANAN&year=2025&bulan_awal=1&bulan_akhir=3"

# Filter tanggal
curl "http://localhost:8000/api/rekening_koran?periode=TANGGAL&tgl_awal=2025-01-01&tgl_akhir=2025-01-31"
```

### Test Filter Per Column
```bash
# Filter by bank
curl "http://localhost:8000/api/rekening_koran?bank=BCA"

# Filter by kredit
curl "http://localhost:8000/api/rekening_koran?kredit=1000000"

# Multiple filters
curl "http://localhost:8000/api/rekening_koran?bank=BCA&kredit=1000000"
```

### Test Sort Order
```bash
# Sort by no_rc ascending
curl "http://localhost:8000/api/rekening_koran?sort_field=no_rc&sort_order=1"

# Sort by kredit descending
curl "http://localhost:8000/api/rekening_koran?sort_field=kredit&sort_order=-1"
```

## Notes

1. **ILIKE vs LIKE**: Backend menggunakan `ILIKE` untuk case-insensitive search (PostgreSQL)
2. **Date Format**: Frontend mengirim format `YYYY-MM-DD`, backend parse dengan Carbon
3. **Pagination**: Default 10 items per page, bisa diubah dengan parameter `per_page`
4. **Export**: Tambahkan parameter `export=true` untuk export semua data tanpa pagination
5. **Calculated Fields**: `terklarifikasi` dan `belum_terklarifikasi` dihitung di Resource layer

## Future Improvements

1. Add filter presets (save & load filter combinations)
2. Add advanced filter UI (range filters for numeric fields)
3. Add filter history
4. Add column visibility toggle
5. Add custom column width settings
