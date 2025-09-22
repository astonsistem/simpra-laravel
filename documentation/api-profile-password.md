# Dokumentasi API Profil & Ganti Password

## 1. Mengambil Data Profil Pengguna

### Endpoint
```
GET /api/auth/profile
```

### Deskripsi
Mengambil data profil pengguna yang sedang login.

### Headers
```
Authorization: Bearer <token>
```

### Response
```json
{
  "status": 200,
  "message": "Berhasil mengambil data profil",
  "data": {
    "id": "uuid",
    "nama": "string",
    "email": "string",
    "username": "string",
    "role": "string",
    "nip": "string",
    "no_telp": "string",
    "jabatan": "string"
  }
}
```

## 2. Mengganti Password

### Endpoint
```
PUT /api/auth/profile/change-password
```

### Deskripsi
Mengganti password pengguna yang sedang login.

### Headers
```
Authorization: Bearer <token>
Content-Type: application/json
```

### Request Body
```json
{
  "current_password": "string",
  "new_password": "string",
  "new_password_confirmation": "string"
}
```

### Validasi
- `current_password`: required, harus sesuai dengan password saat ini
- `new_password`: required, minimal 6 karakter, harus dikonfirmasi

### Response Sukses
```json
{
  "status": 200,
  "message": "Berhasil mengganti password",
  "data": null
}
```

### Response Error
```json
{
  "status": 400,
  "message": "Password saat ini tidak sesuai",
  "data": null
}
```

## Catatan untuk Tim Frontend

1. Untuk mengambil data profil, panggil endpoint `GET /api/auth/profile` dengan token JWT di header Authorization.
2. Untuk mengganti password, panggil endpoint `PUT /api/auth/profile/change-password` dengan token JWT di header Authorization.
3. Pastikan mengirimkan konfirmasi password baru yang sesuai dengan password baru.
4. Tangani response error dengan menampilkan pesan yang sesuai kepada pengguna.
5. Setelah berhasil mengganti password, pengguna harus login kembali dengan password baru.