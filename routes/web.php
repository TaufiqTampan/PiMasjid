<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('welcome');

// Public TV Display (No Auth Required)
Route::get('/display', [App\Http\Controllers\DisplayController::class, 'index'])
    ->name('display.index');

// Protected Public Features & Internal Routes (Requires Auth)
Route::middleware('auth')->group(function () {
    // Public Pages & Info
    Route::get('/profil/struktur', [App\Http\Controllers\PublicController::class, 'structure'])->name('public.struktur');
    Route::get('/profil/tentang', [App\Http\Controllers\PublicController::class, 'tentang'])->name('public.tentang');
    Route::get('/transparansi/keuangan', [App\Http\Controllers\PublicController::class, 'keuangan'])->name('public.keuangan');
    Route::get('/transparansi/aset', [App\Http\Controllers\PublicController::class, 'aset'])->name('public.aset');
    Route::get('/ibadah/jumat', [App\Http\Controllers\PublicController::class, 'jumat'])->name('public.jumat');
    Route::get('/ibadah/jadwal', [App\Http\Controllers\PublicController::class, 'jadwal'])->name('public.jadwal');
    Route::get('/ibadah/agenda', [App\Http\Controllers\PublicController::class, 'agenda'])->name('public.agenda');
    Route::get('/ibadah/kiblat', [App\Http\Controllers\PublicController::class, 'kiblat'])->name('public.kiblat');
    Route::get('/galeri', [App\Http\Controllers\PublicController::class, 'galeri'])->name('public.galeri');
    Route::get('/berita', [App\Http\Controllers\PublicController::class, 'berita'])->name('public.berita');
    Route::get('/berita/{post:slug}', [App\Http\Controllers\PublicController::class, 'post'])->name('public.post');
    Route::get('/tarbiyah', [App\Http\Controllers\PublicController::class, 'tarbiyah'])->name('public.tarbiyah');

    // Public Al-Quran
    Route::get('/quran', function () {
        return inertia('Public/QuranIndex');
    })->name('public.quran');

    Route::get('/quran/{nomor}', function ($nomor) {
        return inertia('Public/QuranShow', ['nomor' => $nomor]);
    })->name('public.quran.show');

    // Public Kalender Ramadhan & Jadwal Imsakiyah
    Route::get('/ramadhan', [App\Http\Controllers\RamadhanController::class, 'index'])
        ->name('public.ramadhan');
    Route::get('/ramadhan/pdf', [App\Http\Controllers\RamadhanController::class, 'exportPdf'])
        ->name('public.ramadhan.pdf');

    // Public Zakat & Qurban Info Pages
    Route::get('/info/zakat', function () {
        return inertia('Public/Zakat');
    })->name('public.zakat');

    Route::get('/info/qurban', function () {
        return inertia('Public/Qurban');
    })->name('public.qurban');

    Route::post('/info/qurban/register', [App\Http\Controllers\QurbanController::class, 'publicRegister'])
        ->middleware(['throttle:public-forms', 'honeypot'])
        ->name('public.qurban.register');

    // Financial Transparency
    Route::get('/keuangan', [App\Http\Controllers\TransactionController::class, 'publicIndex'])
        ->name('keuangan.index');

    // Lumbung Pangan
    Route::get('/lumbung-pangan', [App\Http\Controllers\FoodBarnController::class, 'publicIndex'])
        ->name('public.lumbung-pangan');

    Route::post('/lumbung-pangan/donate', [App\Http\Controllers\FoodBarnController::class, 'publicDonate'])
        ->middleware(['throttle:public-forms', 'honeypot'])
        ->name('public.lumbung-pangan.donate');

    Route::post('/lumbung-pangan/request', [App\Http\Controllers\FoodBarnController::class, 'publicRequest'])
        ->middleware(['throttle:public-forms', 'honeypot'])
        ->name('public.lumbung-pangan.request');

    // Facilities & Booking
    Route::get('/fasilitas', [App\Http\Controllers\PublicFacilityController::class, 'index'])
        ->name('public.facilities');

    Route::post('/fasilitas/booking', [App\Http\Controllers\PublicFacilityController::class, 'store'])
        ->middleware(['throttle:public-forms', 'honeypot'])
        ->name('public.facilities.book');

    Route::get('/fasilitas/cek-status', [App\Http\Controllers\PublicFacilityController::class, 'checkStatus'])
        ->name('public.facilities.check-status');

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management (super_admin only)
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])
        ->name('users.index');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])
        ->name('users.store');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])
        ->name('users.destroy');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\UserController::class, 'impersonate'])
        ->name('users.impersonate');
    Route::post('/users/stop-impersonation', [App\Http\Controllers\UserController::class, 'stopImpersonation'])
        ->name('users.stopImpersonation');

    // Global Settings
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Component Showcase
    Route::get('/components-showcase', function () {
        return Inertia::render('ComponentShowcase');
    })->name('components.showcase');

    // Transaction Management
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::get('/transactions/export', [App\Http\Controllers\TransactionController::class, 'export'])
        ->name('transactions.export');
    Route::post('/transactions', [App\Http\Controllers\TransactionController::class, 'store'])
        ->name('transactions.store');
    Route::delete('/transactions/{id}', [App\Http\Controllers\TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    // Approval System (Ketua only, enforced by Gates)
    Route::get('/approvals', [App\Http\Controllers\ApprovalController::class, 'index'])
        ->name('approvals.index');
    Route::post('/approvals/{transaction}/approve', [App\Http\Controllers\ApprovalController::class, 'approve'])
        ->name('approvals.approve');
    Route::post('/approvals/{transaction}/reject', [App\Http\Controllers\ApprovalController::class, 'reject'])
        ->name('approvals.reject');

    // Slides Management (Marbot/Super Admin)
    Route::get('/slides', [App\Http\Controllers\SlideController::class, 'index'])
        ->name('slides.index');
    Route::post('/slides', [App\Http\Controllers\SlideController::class, 'store'])
        ->name('slides.store');
    Route::post('/slides/{slide}/toggle', [App\Http\Controllers\SlideController::class, 'toggleActive'])
        ->name('slides.toggle');
    Route::post('/slides/{slide}/update', [App\Http\Controllers\SlideController::class, 'update'])
        ->name('slides.update');
    Route::delete('/slides/{slide}', [App\Http\Controllers\SlideController::class, 'destroy'])
        ->name('slides.destroy');

    // Assets Management (Marbot/Super Admin)
    Route::get('/assets/export', [App\Http\Controllers\AssetController::class, 'export'])
        ->name('assets.export');
    Route::resource('assets', App\Http\Controllers\AssetController::class);

    // Agenda Management (Marbot/Super Admin)
    Route::resource('agendas', App\Http\Controllers\AgendaController::class)
        ->middleware('can:manage_operations');

    // Friday Schedule Management
    Route::resource('friday-schedules', App\Http\Controllers\FridayScheduleController::class);

    // Admin Lumbung Pangan Management
    Route::prefix('admin/lumbung-pangan')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminFoodBarnController::class, 'index'])->name('lumbung-pangan.index');
        Route::post('/programs', [App\Http\Controllers\AdminFoodBarnController::class, 'storeProgram'])->name('lumbung-pangan.programs.store');
        Route::post('/programs/{program}', [App\Http\Controllers\AdminFoodBarnController::class, 'updateProgram'])->name('lumbung-pangan.programs.update');
        Route::delete('/programs/{program}', [App\Http\Controllers\AdminFoodBarnController::class, 'destroyProgram'])->name('lumbung-pangan.programs.destroy');
        Route::patch('/donations/{donation}/status', [App\Http\Controllers\AdminFoodBarnController::class, 'updateDonationStatus'])->name('lumbung-pangan.donations.status');
        Route::delete('/donations/{donation}', [App\Http\Controllers\AdminFoodBarnController::class, 'destroyDonation'])->name('lumbung-pangan.donations.destroy');
        Route::patch('/requests/{requestItem}/status', [App\Http\Controllers\AdminFoodBarnController::class, 'updateRequestStatus'])->name('lumbung-pangan.requests.status');
        Route::delete('/requests/{requestItem}', [App\Http\Controllers\AdminFoodBarnController::class, 'destroyRequest'])->name('lumbung-pangan.requests.destroy');
    });

    // Admin Fasilitas & Booking Masjid Management
    Route::prefix('admin/facilities')->group(function () {
        Route::get('/', [App\Http\Controllers\FacilityController::class, 'index'])->name('facilities.index');
        Route::post('/', [App\Http\Controllers\FacilityController::class, 'store'])->name('facilities.store');
        Route::post('/{facility}', [App\Http\Controllers\FacilityController::class, 'update'])->name('facilities.update');
        Route::delete('/{facility}', [App\Http\Controllers\FacilityController::class, 'destroy'])->name('facilities.destroy');
        Route::patch('/bookings/{booking}/status', [App\Http\Controllers\FacilityController::class, 'updateBookingStatus'])->name('facilities.bookings.status');
        Route::delete('/bookings/{booking}', [App\Http\Controllers\FacilityController::class, 'destroyBooking'])->name('facilities.bookings.destroy');
    });

    // Committee Member Management (Super Admin only for now, or maybe Ketua too? Let's stick to SA as per request)
    Route::resource('committee-members', App\Http\Controllers\CommitteeMemberController::class)
        ->middleware('can:manage_users'); // Using manage_users (Super Admin) permission

    // Wishlist / Kebutuhan Masjid Management
    Route::get('/wishlists', [App\Http\Controllers\WishlistController::class, 'index'])->name('wishlists.index');
    Route::post('/wishlists', [App\Http\Controllers\WishlistController::class, 'store'])->name('wishlists.store');
    Route::put('/wishlists/{wishlist}', [App\Http\Controllers\WishlistController::class, 'update'])->name('wishlists.update');
    Route::delete('/wishlists/{wishlist}', [App\Http\Controllers\WishlistController::class, 'destroy'])->name('wishlists.destroy');

    // Posts Management (Berita & Kegiatan)
    Route::resource('posts', App\Http\Controllers\PostController::class)
        ->middleware('can:manage_posts');

    // Lectures Management (Kajian Umum)
    Route::resource('lectures', App\Http\Controllers\LectureController::class)
        ->middleware('can:manage_lectures');

    // Zakat Management
    Route::prefix('zakat')->group(function () {
        Route::get('/', [App\Http\Controllers\ZakatController::class, 'index'])->name('zakat.index');
        Route::get('/create', [App\Http\Controllers\ZakatController::class, 'create'])->name('zakat.create');
        Route::post('/', [App\Http\Controllers\ZakatController::class, 'store'])->name('zakat.store');
        Route::post('/calculate', [App\Http\Controllers\ZakatController::class, 'calculate'])->name('zakat.calculate');
        Route::get('/distribute', [App\Http\Controllers\ZakatController::class, 'distribute'])->name('zakat.distribute');
        Route::post('/distribute', [App\Http\Controllers\ZakatController::class, 'storeDistribution'])->name('zakat.distribution.store');
        Route::get('/reports', [App\Http\Controllers\ZakatController::class, 'reports'])->name('zakat.reports');
        Route::get('/export', [App\Http\Controllers\ZakatController::class, 'export'])->name('zakat.export'); // New export route
    });

    // Qurban Management
    Route::prefix('qurban')->group(function () {
        Route::get('/', [App\Http\Controllers\QurbanController::class, 'index'])->name('qurban.index');
        Route::get('/create', [App\Http\Controllers\QurbanController::class, 'create'])->name('qurban.create');
        Route::post('/', [App\Http\Controllers\QurbanController::class, 'store'])->name('qurban.store');
        Route::patch('/{qurban}/status', [App\Http\Controllers\QurbanController::class, 'updateStatus'])->name('qurban.status');
        Route::get('/{qurban}/edit', [App\Http\Controllers\QurbanController::class, 'edit'])->name('qurban.edit');
        Route::put('/{qurban}', [App\Http\Controllers\QurbanController::class, 'update'])->name('qurban.update');
        Route::delete('/{qurban}', [App\Http\Controllers\QurbanController::class, 'destroy'])->name('qurban.destroy');
        Route::get('/distribute', [App\Http\Controllers\QurbanController::class, 'distribute'])->name('qurban.distribute');
        Route::post('/distribute', [App\Http\Controllers\QurbanController::class, 'storeDistribution'])->name('qurban.distribution.store');
        Route::get('/reports', [App\Http\Controllers\QurbanController::class, 'reports'])->name('qurban.reports');
        Route::get('/export', [App\Http\Controllers\QurbanController::class, 'export'])->name('qurban.export'); // New export route
    });
});

require __DIR__.'/auth.php';
