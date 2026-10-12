# TugasWeb-P11-EcommerceAuth

Tugas Rutin 11 — Pemrograman Web (3KOM40115), UNIMED, PSIK 25D.

Project ini berdiri sendiri. Modul Post sederhana sudah disertakan (migration, model,
controller, view) sebagai sasaran PostPolicy.

## Langkah Instalasi (project standalone, tanpa blog)

1. Buat project baru:
   ```bash
   composer create-project --prefer-dist laravel/laravel:^12.x TugasWeb-P11-EcommerceAuth
   cd TugasWeb-P11-EcommerceAuth
   code .
   ```
2. Buat database `tugasweb_p11` di phpMyAdmin, lalu ubah `.env` (DB_CONNECTION=mysql, DB_DATABASE=tugasweb_p11, dst).
3. Install Breeze (butuh internet):
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   npm install && npm run build
   ```
4. Copy folder `app/`, `database/`, `resources/`, `routes/` dari zip ini ke project, timpa file yang sama
   (`routes/web.php` sudah memuat `require __DIR__.'/auth.php';` dan route `dashboard`;
   `User.php` sudah berisi kolom `role`; `Controller.php` sudah memuat `AuthorizesRequests`).
   Untuk `bootstrap/app.php`, **jangan ditimpa**; cukup tambahkan di dalam `withMiddleware(...)`:
   ```php
   $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);
   ```
   Jika Breeze menimpa/menambah view atau `User.php`, pastikan `fillable` User tetap punya `'role'`.
5. Migrasi + seeder, lalu jalankan:
   ```bash
   php artisan migrate:fresh --seed
   php artisan serve
   ```

## Requirement Bagian A — Database & Eloquent

1. ✅ **7 migration** dengan foreign key constraint:
   - `add_role_to_users_table` (modifikasi tabel `users`)
   - `create_categories_table`
   - `create_products_table` (FK ke `categories`)
   - `create_carts_table` (FK ke `users`)
   - `create_cart_items_table` (FK ke `carts`, `products`)
   - `create_orders_table` (FK ke `users`)
   - `create_order_items_table` (FK ke `orders`, `products`)
   - *(tambahan)* `add_user_id_to_posts_table` (posts + user_id) untuk keperluan PostPolicy di Bagian B.
2. ✅ **Seeder + factory 50+ produk**: `ProductFactory` + `ProductSeeder`
   membuat 60 produk realistis (nama, harga Rp15rb–Rp2,5jt, stok acak).
3. ✅ **Model + relationships + minimal 1 scope**:
   - `Category hasMany Product`
   - `Product belongsTo Category`, `hasMany CartItem`, `hasMany OrderItem`
   - `Cart belongsTo User`, `hasMany CartItem`
   - `Order belongsTo User`, `hasMany OrderItem`
   - Scope: `Product::scopeInStock()`, `Order::scopePaidOrAbove()`
4. ✅ **Dokumentasi 5 query Tinker**: lihat file `TINKER_QUERIES.md`
   (mahasiswa tinggal jalankan & screenshot).

## Requirement Bagian B — Auth & Security

5. ✅ **Breeze** (login/register/logout) — install manual sesuai langkah 2 di atas.
6. ✅ **Multi-role** (`admin` / `editor` / `user`) via kolom `role` di tabel
   `users` + **custom middleware** `RoleMiddleware` (alias `role:...`).
7. ✅ **PostPolicy** untuk otorisasi edit/delete:
   - `admin` boleh edit/hapus post siapa saja.
   - `editor` hanya boleh edit/hapus post miliknya sendiri.
   - `user` tidak boleh edit/hapus sama sekali.
8. ✅ **Route protection**: lihat `routes/web.php` — route edit/delete post
   dibungkus `middleware(['auth', 'role:admin,editor'])`, ditambah
   pengecekan kepemilikan lewat `$this->authorize()` di controller.

## Testing Manual (Incognito 2 Role)

Tiga akun sudah dibuat otomatis oleh `UserSeeder`:

| Email                | Password | Role   |
|-----------------------|----------|--------|
| admin@example.com     | password | admin  |
| editor@example.com    | password | editor |
| user@example.com      | password | user   |

Langkah testing:

1. Buka browser normal, login sebagai `editor@example.com`. Buat 1 post baru.
   Coba edit/hapus post itu → **berhasil** (karena post miliknya sendiri).
2. Buka **jendela incognito**, login sebagai `user@example.com`. Buka
   halaman `/posts/{slug}/edit` milik editor tadi → harus muncul **403
   Forbidden** (karena role `user` tidak diizinkan sama sekali, dicegah
   middleware `role:admin,editor`).
3. Di jendela incognito yang sama, logout, lalu login sebagai
   `admin@example.com`. Buka halaman edit post milik editor tadi → **berhasil**
   (karena admin boleh edit post siapa saja lewat `PostPolicy`).
4. Screenshot ketiga percobaan di atas sebagai bukti testing role.

## Bonus

- **Filament admin panel**: ikuti langkah di praktikum `Praktik_Laravel_-
  _Filament.pdf` untuk membuat resource `Product` di Filament, supaya admin
  bisa kelola produk lewat panel admin.
- **Eager loading demo**: sudah dicontohkan di Query 1 (`TINKER_QUERIES.md`)
  — `Product::with('category')->...` untuk menghindari N+1 query.

## Bonus Filament (v4, sesuai praktikum)

```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels      # panel ID: admin
php artisan make:filament-user
php artisan make:filament-resource Product
```
Lalu di `app/Models/User.php` implement `FilamentUser` dan batasi akses panel hanya admin
(lebih aman daripada `return true` di praktikum):
```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
```
Field form Product: `category_id` (Select relationship), `name`, `slug`, `description`, `price`, `stock`.
