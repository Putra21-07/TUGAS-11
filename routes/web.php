<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Tugas Rutin 11 - E-Commerce DB + Secure Auth
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('posts.index');
});

// Breeze (blade) mengarahkan user ke route bernama 'dashboard' setelah login.
Route::get('/dashboard', function () {
    return redirect()->route('posts.index');
})->middleware('auth')->name('dashboard');

// --- Blog CRUD (dari TR10), dengan proteksi role ---

// Harus login untuk membuat post.
// PENTING: ditaruh SEBELUM /posts/{post}, kalau tidak "create" dianggap slug post -> 404.
Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
});

// Siapa saja (termasuk tamu) boleh membaca post
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Hanya admin & editor yang boleh edit/hapus.
// Cek kepemilikan (editor hanya post miliknya) dilakukan PostPolicy via authorize() di controller.
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// Route khusus admin (untuk testing middleware role)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Halaman ini hanya bisa diakses oleh role admin.';
    })->name('admin.dashboard');
});

// Route login/register/logout bawaan Breeze
require __DIR__.'/auth.php';
