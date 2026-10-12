# Dokumentasi Query Tinker — Tugas Rutin 11 (Bagian A poin 4)

Jalankan `php artisan tinker` di terminal project, lalu jalankan satu per
satu perintah di bawah ini. **Screenshot setiap output** untuk dilampirkan
sebagai bukti tugas.

Pastikan `php artisan migrate:fresh --seed` sudah dijalankan lebih dulu
supaya data (60 produk, 6 kategori, 3 user) sudah tersedia.

## Query 1 — Ambil 5 produk termahal beserta kategorinya (eager loading)

```php
Product::with('category')->orderByDesc('price')->take(5)->get(['id','name','price','category_id']);
```

**Tujuan:** menunjukkan eager loading (`with()`) supaya tidak N+1 query saat
mengakses `$product->category->name`.

## Query 2 — Pakai scope `inStock()` untuk hitung produk yang stoknya tersedia

```php
Product::inStock()->count();
```

**Tujuan:** membuktikan custom scope pada model `Product` bekerja
(`scopeInStock` yang mem-filter `stock > 0`).

## Query 3 — Cari kategori dengan jumlah produk terbanyak

```php
Category::withCount('products')->orderByDesc('products_count')->first();
```

**Tujuan:** menunjukkan relasi `hasMany` (`Category::products()`) dipakai
lewat `withCount()`.

## Query 4 — Buat order baru untuk user tertentu beserta order item-nya

```php
$user = User::where('email', 'user@example.com')->first();
$order = $user->orders()->create(['total' => 0, 'status' => 'pending']);

$product = Product::inStock()->first();
$order->items()->create([
    'product_id' => $product->id,
    'quantity'   => 2,
    'price'      => $product->price,
]);

$order->update(['total' => $product->price * 2]);
$order->load('items.product');
```

**Tujuan:** menunjukkan relasi `Order -> OrderItem -> Product` sekaligus
proses insert data lewat relasi (`$user->orders()->create(...)`).

## Query 5 — Ambil semua order yang statusnya sudah dibayar ke atas (scope) beserta total item

```php
Order::paidOrAbove()->with('items')->get()->map(function ($order) {
    return [
        'order_id'   => $order->id,
        'status'     => $order->status,
        'item_count' => $order->items->sum('quantity'),
    ];
});
```

**Tujuan:** membuktikan scope `paidOrAbove()` pada model `Order` bekerja,
digabung dengan collection method (`->map()`, `->sum()`).

---

**Cara submit:** jalankan kelima query di atas satu per satu di Tinker,
screenshot masing-masing hasilnya (termasuk perintah yang diketik), lalu
lampirkan ke laporan/README repo `TugasWeb-P11-EcommerceAuth`.
