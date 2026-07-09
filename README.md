# SISPREVIEW — Sistem Project View

SISPREVIEW adalah Sistem Project View untuk monitoring progress project, kontrak, CR, tiket, dan delivery client.

Aplikasi internal SISPREVIEW berbasis PHP native, PDO MySQL, HTML, CSS, dan JavaScript vanilla. Tidak memakai framework dan tidak memakai CDN.

## Fitur Utama

- Login database dengan session PHP.
- Role-based access control:
  - `super_admin`: lihat semua client, create/edit/inactive CR, kelola user dan akses client.
  - `management_viewer`: view-only sesuai akses client.
  - `developer`: lihat client sesuai akses dan update status/progress/link tiket/Figma.
  - `client_viewer`: view-only sesuai akses client.
- Pembatasan akses per client/RS lewat tabel `clients` dan `user_clients`.
- Dashboard executive dan detail curator tetap derive dari database.
- Link Tiket dan Link Figma tetap terpisah dari judul CR.
- Event log CR mencatat actor login, action, before/after value, IP, dan user agent.

## Struktur

```text
monitoring-cr-php/
  app/
  config/
  database/
    install_fresh.sql
    migration_add_sub_area.sql
    migration_add_is_active.sql
    migration_add_links.sql
    migration_auth_rbac_clients.sql
    seed.sql
  public/
    index.php
    login.php
    logout.php
    change_password.php
    users.php
    user_create.php
    user_edit.php
    user_reset_password.php
    user_access.php
  tools/
    reset_password.php
```

## Migration Existing Database

Jangan menjalankan `install_fresh.sql` pada database yang sudah berisi data operasional.

Urutan migration additive untuk database existing:

```bash
mysql -u root -p monitoring_cr < database/migration_add_sub_area.sql
mysql -u root -p monitoring_cr < database/migration_add_is_active.sql
mysql -u root -p monitoring_cr < database/migration_add_links.sql
mysql -u root -p monitoring_cr < database/migration_auth_rbac_clients.sql
```

Jika tiga migration lama sudah pernah dijalankan, cukup jalankan:

```bash
mysql -u root -p monitoring_cr < database/migration_auth_rbac_clients.sql
```

Migration auth akan:
- membuat tabel `users`, `clients`, dan `user_clients`;
- menambah `client_id` ke `cr_items`;
- mengisi data existing ke client default `RSPAD`;
- menambah kolom audit actor ke `cr_events`;
- membuat seed client RSPAD dan Primaya;
- membuat seed user awal dengan password hash dan `force_password_change = 1`.

## Reset Password Awal

Password plaintext tidak disimpan di README atau SQL. Setelah migration, reset password user seed lewat CLI lokal:

```bash
php tools/reset_password.php andreas
php tools/reset_password.php pak_direktur
php tools/reset_password.php pak_heri
```

Tambahkan opsi `--force` jika user wajib mengganti password saat login berikutnya:

```bash
php tools/reset_password.php pak_heri --force
```

## Seed User Awal

- `andreas`: `super_admin`, akses semua client.
- `pak_handoyo`: `management_viewer`, akses semua client.
- `pak_direktur`: `management_viewer`, akses RSPAD.
- `pak_heri`: `developer`, akses RSPAD.
- `pak_febio`: `developer`, akses RSPAD.
- `pak_morris`: `developer`, akses RSPAD.

## Fresh Install Lokal

Hanya untuk database kosong/reset lokal:

```bash
mysql -u root -p < database/install_fresh.sql
mysql -u root -p monitoring_cr < database/seed.sql
php -S localhost:8000 -t public
```

Lalu reset password `andreas` dengan script CLI di atas.

## Deploy

- Apache/Nginx document root wajib menunjuk ke folder `public/`.
- Folder `app/`, `config/`, `database/`, dan `tools/` tidak boleh bisa diakses browser.
- Sesuaikan koneksi database di `config/database.php`.
- Untuk production, set `APP_ENV=production` dan matikan display error detail dari konfigurasi PHP/web server.

## Catatan Data

- `CR` adalah judul item pekerjaan/perubahan.
- `Ticket No` adalah kode/nomor tiket.
- `Link Tiket` adalah URL menuju tiket.
- `Link Figma` adalah URL desain.
- Jangan memasukkan link ke field `CR`.
- Semua query dashboard/detail/modal difilter berdasarkan akses client user yang login.
- Item nonaktif tidak dihitung dan tidak tampil di dashboard.
