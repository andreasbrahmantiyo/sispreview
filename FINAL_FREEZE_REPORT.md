# Final Freeze Report

## 1. Ringkasan Aplikasi SISPREVIEW

Nama aplikasi final: **SISPREVIEW — Sistem Project View**

SISPREVIEW adalah Sistem Project View untuk monitoring progress project, kontrak, CR, tiket, dan delivery client.

Status freeze: aplikasi sudah lulus test manual dan siap dijadikan baseline final sebelum deploy public.

Stack aplikasi:
- PHP native dengan PDO MySQL.
- MySQL/MariaDB.
- HTML, CSS, dan JavaScript vanilla.
- Tanpa framework.
- Tanpa CDN.

Fitur utama yang sudah freeze:
- Authentication database.
- Role-based access control.
- Client scope per RS/client.
- View-only mode untuk management viewer.
- Developer scope RSPAD.
- Branding SISPREVIEW.
- Audit/event log dengan actor user login.
- Dashboard executive dan detail curator berbasis data CR existing.

## 2. Daftar Role dan Akses

| Role | Akses |
|---|---|
| `super_admin` | Melihat semua client, create/edit/inactive CR, kelola user, reset password user, dan assign client access. |
| `management_viewer` | View-only dashboard/detail sesuai client access. Tidak boleh create/edit/inactive/update status. |
| `developer` | Melihat client sesuai akses, update progress/status, ticket no, ticket URL, Figma URL, dan today update sesuai client access. |
| `client_viewer` | View-only dashboard/detail sesuai client access. |

Catatan backend:
- Pembatasan akses dilakukan di server-side, bukan hanya hide tombol UI.
- Akses CR client lain via URL langsung wajib ditolak.
- Endpoint action tetap menolak viewer dengan 403.

## 3. Daftar User Final

| Username | Nama | Role | Status | Force Password Change | Client Access |
|---|---|---|---|---|---|
| `andreas` | Andreas | `super_admin` | Aktif | Tidak | PRIMAYA, RSPAD |
| `dev_febio` | Pak Febio | `developer` | Aktif | Ya | RSPAD |
| `dev_heri` | Pak Heri | `developer` | Aktif | Ya | RSPAD |
| `dev_morris` | Pak Morris | `developer` | Aktif | Tidak | RSPAD |
| `dir_handoyo` | Pak Handoyo | `management_viewer` | Aktif | Tidak | PRIMAYA, RSPAD |
| `dir_rspad` | Direktur RSPAD | `management_viewer` | Aktif | Ya | RSPAD |

## 4. Checklist Test Manual Yang Lulus

- Auth/login OK.
- RBAC OK.
- Client scope OK.
- View-only management OK.
- Developer RSPAD OK.
- Branding SISPREVIEW OK.
- Data CR existing tetap tampil.
- Backup DB sudah dibuat.
- PHP lint semua file: passed.
- JS syntax check: passed.

## 5. Daftar File dan Folder Penting

Folder penting:
- `app/`: bootstrap, helper auth/RBAC, query dashboard, dan view aplikasi.
- `config/`: konfigurasi database dan proteksi tambahan.
- `database/`: migration, seed, dan backup database.
- `public/`: document root aplikasi, halaman login/dashboard/action, asset CSS/JS.
- `tools/`: tool CLI internal seperti reset password.

File penting:
- `app/bootstrap.php`
- `app/views_dashboard.php`
- `app/views_login.php`
- `config/database.php`
- `database/migration_auth_rbac_clients.sql`
- `database/backup_monitoring_cr_sispreview_rbac_ok.sql`
- `public/index.php`
- `public/login.php`
- `public/logout.php`
- `public/create.php`
- `public/edit.php`
- `public/users.php`
- `public/user_create.php`
- `public/user_edit.php`
- `public/user_reset_password.php`
- `public/user_access.php`
- `public/assets/style.css`
- `public/assets/app.js`
- `tools/reset_password.php`
- `README.md`

## 6. Command Backup DB

Backup final yang sudah ada:

```bash
database/backup_monitoring_cr_sispreview_rbac_ok.sql
```

Command backup DB untuk membuat ulang backup:

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root monitoring_cr > database\backup_monitoring_cr_sispreview_rbac_ok.sql
```

Jika user MySQL memakai password:

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -p monitoring_cr > database\backup_monitoring_cr_sispreview_rbac_ok.sql
```

## 7. Command Reset Password User

Reset password user melalui CLI lokal:

```bash
C:\xampp\php\php.exe tools\reset_password.php andreas
C:\xampp\php\php.exe tools\reset_password.php dir_handoyo
C:\xampp\php\php.exe tools\reset_password.php dir_rspad
C:\xampp\php\php.exe tools\reset_password.php dev_heri
C:\xampp\php\php.exe tools\reset_password.php dev_febio
C:\xampp\php\php.exe tools\reset_password.php dev_morris
```

Tambahkan `--force` jika user wajib mengganti password saat login berikutnya:

```bash
C:\xampp\php\php.exe tools\reset_password.php dev_heri --force
```

## 8. Checklist Deploy Public

- Pastikan database production sudah dibuat.
- Import migration sesuai urutan yang sudah ada di README.
- Import/restore backup final bila deploy memakai state lokal yang sudah lulus test.
- Sesuaikan `config/database.php` untuk credential production.
- Set `APP_ENV=production`.
- Pastikan PHP extension PDO MySQL aktif.
- Pastikan session PHP bisa menulis ke session save path server.
- Pastikan HTTPS aktif di public domain.
- Pastikan cookie session memakai HttpOnly dan SameSite=Lax.
- Pastikan user seed sudah direset password-nya.
- Pastikan user inactive tidak bisa login.
- Pastikan viewer tidak bisa akses endpoint edit/action.
- Pastikan developer hanya bisa akses client yang di-assign.
- Jalankan lint sebelum upload final.
- Simpan backup DB sebelum dan sesudah deploy.

## 9. Catatan Document Root

Document root web server wajib menunjuk ke folder:

```text
public/
```

Jangan arahkan document root ke root project `monitoring-cr-php/`.

## 10. Catatan Security Folder Privat

Folder berikut tidak boleh bisa diakses publik:

- `app/`
- `config/`
- `database/`
- `tools/`
- `.git/`

Yang boleh menjadi public web root hanya:

- `public/`

Jika memakai Nginx/Apache, pastikan request langsung ke folder privat menghasilkan deny/403 atau tidak pernah ter-route ke folder tersebut.

## Freeze Verification

Tanggal freeze: 2026-07-09

Command verifikasi yang sudah dijalankan:

```bash
C:\xampp\php\php.exe -l <semua file PHP di app, public, tools, config>
node --check public\assets\app.js
```

Hasil:

- PHP lint passed.
- JS syntax check passed.
