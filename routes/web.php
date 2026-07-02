<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── Auth (público) ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,   'create'])->name('login');
    Route::post('/login',   [LoginController::class,   'store']);
    Route::get('/register', [RegisterController::class,'create'])->name('register');
    Route::post('/register',[RegisterController::class,'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ─── App (requer autenticação) ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Lançamentos
    Route::get('/lancamento',                      [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/lancamento',                     [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/lancamento/{transaction}/editar', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/lancamento/{transaction}',        [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/lancamento/{transaction}',     [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Histórico
    Route::get('/historico', [TransactionController::class, 'history'])->name('history.index');

    // Categorias
    Route::get('/categorias',                   [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categorias/nova',              [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categorias',                  [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categorias/{category}/editar', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categorias/{category}',        [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categorias/{category}',     [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Alertas
    Route::get('/alertas',                 [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alertas/novo',            [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alertas',                [AlertController::class, 'store'])->name('alerts.store');
    Route::delete('/alertas/{alert}',      [AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::post('/alertas/{alert}/toggle', [AlertController::class, 'toggle'])->name('alerts.toggle');

    // Contas a pagar/receber
    Route::get('/contas',                     [BillController::class, 'index'])->name('bills.index');
    Route::get('/contas/nova',                [BillController::class, 'create'])->name('bills.create');
    Route::post('/contas',                    [BillController::class, 'store'])->name('bills.store');
    Route::get('/contas/{bill}/editar',       [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/contas/{bill}',              [BillController::class, 'update'])->name('bills.update');
    Route::post('/contas/{bill}/pagar',       [BillController::class, 'marcarPago'])->name('bills.marcarPago');
    Route::delete('/contas/{bill}',           [BillController::class, 'destroy'])->name('bills.destroy');
    Route::delete('/contas-parcelamento',     [BillController::class, 'destroyParcelamento'])->name('bills.destroyParcelamento');

    // Lembretes
    Route::post('/lembretes',                   [ReminderController::class, 'store'])->name('reminders.store');
    Route::post('/lembretes/{reminder}/toggle', [ReminderController::class, 'toggle'])->name('reminders.toggle');
    Route::delete('/lembretes/{reminder}',      [ReminderController::class, 'destroy'])->name('reminders.destroy');

    // Configurações
    Route::get('/configuracoes',  [SettingController::class, 'show'])->name('settings.show');
    Route::post('/configuracoes', [SettingController::class, 'update'])->name('settings.update');
});
