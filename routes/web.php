<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── Landing pública (SEO) ────────────────────────────────────────────────────
Route::get('/', [MarketingController::class, 'home'])->name('home');

// ─── Auth (público) ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,   'create'])->name('login');
    Route::post('/login',   [LoginController::class,   'store'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class,'create'])->name('register');
    Route::post('/register',[RegisterController::class,'store'])->middleware('throttle:10,1');

    // Recuperação de senha (nomes de rota exigidos pelo notification do Laravel)
    Route::get('/esqueci-senha',           [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/esqueci-senha',          [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/redefinir-senha/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/redefinir-senha',        [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ─── Assinatura (requer autenticação, mas não bloqueia sem assinatura) ────────
Route::middleware('auth')->group(function () {
    Route::get('/assinatura',           [BillingController::class, 'index'])->name('billing.index');
    Route::post('/assinatura/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/assinatura/sucesso',   [BillingController::class, 'success'])->name('billing.success');
    Route::post('/assinatura/portal',   [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/assinatura/resgatar', [BillingController::class, 'redeem'])->name('billing.redeem');
});

// ─── App (requer autenticação + assinatura ativa) ─────────────────────────────
Route::middleware(['auth', 'subscribed'])->group(function () {

    Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard');

    // Lançamentos
    Route::get('/lancamento',                      [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/lancamento',                     [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/lancamento/{transaction}/editar', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/lancamento/{transaction}',        [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/lancamento/{transaction}',     [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/lancamento/{transaction}/repetir', [TransactionController::class, 'repeat'])->name('transactions.repeat');
    Route::delete('/historico/lote',               [TransactionController::class, 'bulkDestroy'])->name('transactions.bulkDestroy');

    // Histórico
    Route::get('/historico',          [TransactionController::class, 'history'])->name('history.index');
    Route::get('/historico/exportar', [TransactionController::class, 'exportCsv'])->name('history.export');

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

    // Relatórios
    Route::get('/relatorios', [ReportController::class, 'index'])->name('reports.index');

    // Contas a pagar/receber
    Route::get('/contas',                     [BillController::class, 'index'])->name('bills.index');
    Route::get('/contas/nova',                [BillController::class, 'create'])->name('bills.create');
    Route::post('/contas',                    [BillController::class, 'store'])->name('bills.store');
    Route::get('/contas/{bill}/editar',       [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/contas/{bill}',              [BillController::class, 'update'])->name('bills.update');
    Route::post('/contas/{bill}/pagar',       [BillController::class, 'marcarPago'])->name('bills.marcarPago');
    Route::post('/contas/{bill}/abater',      [BillController::class, 'pagarParcial'])->name('bills.pagarParcial');
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

// ─── Admin (requer autenticação + is_admin) ────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/vendas', [AdminController::class, 'vendas'])->name('admin.vendas');
});
