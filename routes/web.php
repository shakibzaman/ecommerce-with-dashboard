<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\Payments\PaymentController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Product\StockHistoryController;
use App\Http\Controllers\Admin\Supplier\SupplierController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceReturnController;
use App\Http\Controllers\Admin\Orders\OrdersController;
use App\Http\Controllers\Admin\Supplier\TransactionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\Wholesaler\WholesalerController;
use App\Http\Controllers\Admin\Wholesaler\WholesalerInvoiceController;

use App\Http\Controllers\StatusesController;
use App\Http\Controllers\DeliveryCompaniesController;
use App\Http\Controllers\GlobalSettingsController;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Stuff\StuffController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\DepositsController;
use App\Http\Controllers\KycsController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserBehaveController;

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/product/list', [HomePage::class, 'products'])->name('product-page');
Route::get('/cart', [HomePage::class, 'cartPage'])->name('cart-page');

use App\Http\Controllers\SlidersController;

Route::get('/product/{slug}', [HomePage::class, 'productShow'])->name('front-product-show');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart/show', [CartController::class, 'showCart'])->name('cart.show');
Route::get('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/order/done', [OrderController::class, 'orderDone'])->name('order.done');
// Route::post('/order/product/remove', [CartController::class, 'removeFromOrder'])->name('order.product.remove');

Route::get('/quick/order', [CartController::class, 'quickOrder'])->name('quick.order');
Route::get('/order/modal-content', [CartController::class, 'getModalContent'])->name('order.modalContent');
Route::get('/cart/get', [CartController::class, 'getCartData'])->name('cart.get');



Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
Route::get('register/customer', [CustomerController::class, 'create'])->name('register-customers');


// locale
Route::get('/test', [LanguageController::class, 'test']);

use App\Http\Controllers\ExpenseCategoriesController;
use App\Http\Controllers\ExpensesController;

Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/clear-cache', [CacheController::class, 'clearAllCache'])->name('clear.cache');


Route::middleware(['auth', 'verified', 'web'])->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::resource('roles', RoleController::class);
  Route::resource('users', UserController::class);
  Route::resource('permissions', PermissionController::class);
  Route::resource('suppliers', SupplierController::class);
  Route::resource('wholesalers', WholesalerController::class);
  Route::resource('categories', CategoryController::class);
  Route::resource('units', UnitController::class);
  Route::resource('products', ProductController::class);


  Route::get('orders', [OrdersController::class, 'index'])->name('order.list');
  Route::get('order/list', [OrdersController::class, 'indexTest'])->name('order.list.test');
  Route::get('test', [OrdersController::class, 'test'])->name('test');
  Route::get('create/orders', [OrdersController::class, 'createOrder'])->name('order.create');
  Route::get('create/courier/sheet', [OrdersController::class, 'createCourierOrderSheet'])->name('create.courier.order.sheet');
  Route::post('store/courier/sheet', [OrdersController::class, 'storeCourierOrderSheet'])->name('store.courier.order.sheet');
  Route::get('search/order', [OrdersController::class, 'searchOrder'])->name('orders.search');
  Route::post('orders', [InvoiceController::class, 'orderStore'])->name('order.store');
  Route::put('orders/update/{id}', [InvoiceController::class, 'orderUpdate'])->name('order.update');
  Route::put('orders/status/update/{id}', [OrdersController::class, 'orderStatusUpdate'])->name('order.status.update');

  Route::get('order/edit/{id}', [OrdersController::class, 'editOrder'])->name('order.edit');

  Route::get('list/courier/sheet', [OrdersController::class, 'listCourierOrderSheet'])->name('list.courier.order.sheet');

  Route::get('import/payment/sheet', [PaymentController::class, 'createImportOrderpaymentCsv'])->name('create.import.payment.order.csv');
  Route::post('import/payment/sheet', [PaymentController::class, 'importOrderpaymentCsv'])->name('import.payment.order.csv');
  Route::post('process/payment/sheet', [PaymentController::class, 'StoreOrderpaymentCsv'])->name('process.payment.data');

  // Wholesaler 
  Route::get('wholesaler/orders', [WholesalerController::class, 'order'])->name('wholesalers-order-list');

  // Stuff / Admin Manageemnt 

  Route::get('stuffs', [StuffController::class, 'index'])->name('stuff-list');


  Route::get('product/trannsfer/{data}', [ProductController::class, 'transferView'])->name('product.get.stock');
  Route::post('product/trannsfer', [ProductController::class, 'productTransfer'])->name('product.stock.transfer');
  // Route::get('get-store-quantity/{id}', [ProductController::class, 'getStock'])->name('product.get.stock');


  Route::get('supplier/transaction/{id}', [TransactionController::class, 'show'])->name('supplier.transaction.show');
  Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
  Route::get('supplier/invoice', [InvoiceController::class, 'supplierInvoiceCreate'])->name('suppliers-invoice');
  Route::get('supplier/invoice/list/{id}', [InvoiceController::class, 'invoiceList'])->name('supplier.invoices.list');
  Route::get('supplier/invoice/list', [InvoiceController::class, 'supplierinvoiceList'])->name('suppliers-invoices-list');

  Route::get('invoice/product/list/{id}', [InvoiceController::class, 'invoiceProductList'])->name('invoice.product.list');
  Route::get('invoice/product/list/return/{id}', [InvoiceController::class, 'invoiceProductListReturn'])->name('invoice.product.list.return');
  Route::post('invoice/product/list/return', [InvoiceReturnController::class, 'invoiceProductReturnUpdate'])->name('invoice.product.return.update');
  Route::get('supplier/payment/list/{id}', [InvoiceController::class, 'paymentList'])->name('supplier.payment.list');
  Route::post('supplier/invoice', [InvoiceController::class, 'productStore'])->name('supplier.invoice.store');


  Route::get('wholesaler/transaction/{id}', [TransactionController::class, 'show'])->name('wholesaler.transaction.show');
  Route::get('wholesaler/invoice', [WholesalerInvoiceController::class, 'create'])->name('wholesalers-invoice');
  Route::get('wholesaler/invoice/list/{id}', [InvoiceController::class, 'invoiceList'])->name('wholesaler.invoices.list');
  Route::get('wholesaler/payment/list/{id}', [InvoiceController::class, 'paymentList'])->name('wholesaler.payment.list');
  Route::post('wholesaler/invoice', [InvoiceController::class, 'WholesaleProductstore'])->name('wholesaler.invoice.store');
  // 

  Route::get('product/stock/history/{id}', [StockHistoryController::class, 'stockHistory'])->name('product.stock.history');
  Route::get('product/transfer/log/{id}', [StockHistoryController::class, 'transferLog'])->name('product.transfer.log');
  // Route::post('product/stock/transfer', [StockHistoryController::class, 'stockTransfer'])->name('product.stock.transfer');
});

Route::middleware(['auth', 'verified'])->group(function () {

  Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
  Route::get('/export-database', [MigrationController::class, 'export'])->name('database.export');
  Route::get('/database-migration', [MigrationController::class, 'showImportForm'])->name('import.database.form');
  Route::post('/import-database', [MigrationController::class, 'importDatabase'])->name('import.database');
  Route::get('admin/logininfo', [\App\Http\Controllers\Admin\LoginInfoController::class, 'admin'])->name('admin.logininfo');
  Route::get('admin/logininfo/data', [\App\Http\Controllers\Admin\LoginInfoController::class, 'admindata'])->name('admin.logininfo.data');
  Route::get('user/logininfo', [\App\Http\Controllers\Admin\LoginInfoController::class, 'user'])->name('user.logininfo');
  Route::get('user/logininfo/data', [\App\Http\Controllers\Admin\LoginInfoController::class, 'userdata'])->name('user.logininfo.data');
  Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->names('manage-member-customers');
  Route::get('admin/users-datatable', [\App\Http\Controllers\Admin\CustomerController::class, 'datatable'])->name('users.datatable');
  Route::get('users/paid', [\App\Http\Controllers\Admin\CustomerController::class, 'paid'])->name('manage-member-customer-paid');
  Route::get('admin/users-paid', [\App\Http\Controllers\Admin\CustomerController::class, 'paidData'])->name('users.paiddata');
  Route::get('users/monthly-subscriber', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlySubscriber'])->name('manage-member-customer-monthlySubscriber');
  Route::get('users/users-monthly-subscriber', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlySubscriberData'])->name('users.monthlySubscriberData');
  Route::get('users/monthly-subscription-inactive', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlySubscriptionInactive'])->name('manage-member-customer-monthlySubscriber-inactive');
  Route::get('users/users-monthly-subscription-inactive', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlySubscriptionInactiveData'])->name('users.monthlySubscriptionInactiveData');
  Route::get('users/monthly-unsubscriber', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlyUnsubscriber'])->name('manage-member-customer-monthlyUnsubscriber');
  Route::get('users/users-monthly-unsubscriber', [\App\Http\Controllers\Admin\CustomerController::class, 'monthlyUnsubscriberData'])->name('users.monthlyUnsubscriberData');
  Route::get('users/free', [\App\Http\Controllers\Admin\CustomerController::class, 'free'])->name('manage-member-customer-free');
  Route::get('users/users-free', [\App\Http\Controllers\Admin\CustomerController::class, 'freeData'])->name('users.freeData');
  Route::get('users/banned', [\App\Http\Controllers\Admin\CustomerController::class, 'banned'])->name('users.banned');
  Route::get('users/users-banned', [\App\Http\Controllers\Admin\CustomerController::class, 'bannedData'])->name('users.bannedData');
  Route::get('users/email-unverified', [\App\Http\Controllers\Admin\CustomerController::class, 'emailUnverified'])->name('manage-member-customer-emailUnverified');
  Route::get('users/users-email-unverified', [\App\Http\Controllers\Admin\CustomerController::class, 'emailUnverifiedData'])->name('users.emailUnverifiedData');
  Route::get('users/number-unverified', [\App\Http\Controllers\Admin\CustomerController::class, 'numberUnverified'])->name('manage-member-customer-numberUnverified');
  Route::get('users/users-number-unverified', [\App\Http\Controllers\Admin\CustomerController::class, 'numberUnverifiedData'])->name('users.numberUnverifiedData');
  Route::get('users/with-balance', [\App\Http\Controllers\Admin\CustomerController::class, 'withBalance'])->name('manage-member-customer-withBalance');
  Route::get('users/users-with-balance', [\App\Http\Controllers\Admin\CustomerController::class, 'withBalanceData'])->name('users.withBalanceData');
  Route::get('users/top-recruiters', [\App\Http\Controllers\Admin\CustomerController::class, 'topRecruiter'])->name('users.topRecruiter');
  Route::get('users/top-recruiters-data', [\App\Http\Controllers\Admin\CustomerController::class, 'topRecruiterData'])->name('users.topRecruiterData');

  // membership management
  Route::get('users/membership-plan', [\App\Http\Controllers\Admin\MembershipController::class, 'index'])->name('users.membership');
  Route::get('users/switch-membership', [\App\Http\Controllers\Admin\MembershipController::class, 'switchPlan'])->name('users.membership.switch');
  Route::post('users/switch-membership', [\App\Http\Controllers\Admin\MembershipController::class, 'UpdatePlan'])->name('users.membership.update');
  Route::get('users/membership-log', [\App\Http\Controllers\Admin\MembershipController::class, 'log'])->name('users.membership.log');

  // subscription management
  Route::get('users/subscription-package', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('users.subscription');
  Route::get('users/switch-subscription', [\App\Http\Controllers\Admin\SubscriptionController::class, 'switchPlan'])->name('users.subscription.switch');
  Route::post('users/switch-subscription', [\App\Http\Controllers\Admin\SubscriptionController::class, 'UpdatePlan'])->name('users.subscription.update');
  Route::get('users/subscription-log', [\App\Http\Controllers\Admin\SubscriptionController::class, 'log'])->name('users.subscription.log');
  Route::get('users/disable-subscription', [\App\Http\Controllers\Admin\SubscriptionController::class, 'disable'])->name('users.subscription.disable');
  Route::post('users/disable-subscription', [\App\Http\Controllers\Admin\SubscriptionController::class, 'disableSub'])->name('users.subscription.postdisable');

  Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
  // Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::get('password/reset', [PasswordController::class, 'edit'])->name('password.edit');
  Route::post('update/password', [PasswordController::class, 'updatePassword'])->name('update.password');
  Route::get('pending-withdraw-requests', [\App\Http\Controllers\Admin\WithdrawController::class, 'pending'])->name('pending.withdraw.requests');
  Route::get('approved-withdraw-requests', [\App\Http\Controllers\Admin\WithdrawController::class, 'approved'])->name('approved.withdraw.requests');
  Route::get('rejected-withdraw-requests', [\App\Http\Controllers\Admin\WithdrawController::class, 'rejected'])->name('rejected.withdraw.requests');
  Route::get('withdraw-requests-log', [\App\Http\Controllers\Admin\WithdrawController::class, 'all'])->name('all.withdraw.requests');
  Route::post('change-withdraw-ststue', [\App\Http\Controllers\Admin\WithdrawController::class, 'changeStatus'])->name('change-withdraw-status');

  Route::get('online-user', [UserBehaveController::class, 'onlineUser'])->name('userBehave-online-user');
  Route::get('activity-log', [ActivityLogController::class, 'activityLog'])->name('userBehave-activity-log');

  Route::get('admin-login-log', [ActivityLogController::class, 'adminLogingLog'])->name('logingInfo-adminLoging-Log');
  Route::get('customer-login-log', [ActivityLogController::class, 'customerLogingLog'])->name('logingInfo-customerLoging-Log');

  Route::resource('roles', RoleController::class);
  Route::resource('users', UserController::class);
  Route::resource('permissions', PermissionController::class);
});


Route::prefix('user')->middleware(['auth:customer', '2fa'])->group(function () {

  Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
  Route::post('enroll-lifetime-package', [\App\Http\Controllers\Customer\CustomerController::class, 'enroll_lifetime_package'])->name('customer.enroll.lifetime');
  Route::post('enroll-monthly-package', [\App\Http\Controllers\Customer\CustomerController::class, 'enroll_monthly_package'])->name('customer.enroll.monthly');
  Route::get('profile', [\App\Http\Controllers\Customer\CustomerController::class, 'profile'])->name('customer.profile');
  Route::get('subscribers', [\App\Http\Controllers\Customer\CustomerController::class, 'subscribers'])->name('customer.subscribers');
  ROute::get('withdraw-requests', [\App\Http\Controllers\Customer\WithdrawController::class, 'index'])->name('customer.withdraw.index');
  ROute::get('withdraw-request/create', [\App\Http\Controllers\Customer\WithdrawController::class, 'create'])->name('customer.withdraw.create');
  ROute::post('withdraw-request/store', [\App\Http\Controllers\Customer\WithdrawController::class, 'store'])->name('customer.withdraw.store');
  Route::get('deposits/create', [DepositsController::class, 'create'])
    ->name('user.deposits.deposit.create');
  Route::get('deposits/list', [DepositsController::class, 'customerDepositList'])
    ->name('user.deposits.deposit.list');
  Route::get('kycs/create', [KycsController::class, 'create'])
    ->name('customer-kyc-create');
  Route::get('kycs/view', [KycsController::class, 'view'])
    ->name('customer-kyc-view');
});
Route::get('/2fa', [\App\Http\Controllers\Email2faController::class, 'create'])->name('customer.2fa')->middleware('auth:customer');
Route::post('/2fa', [\App\Http\Controllers\Email2faController::class, 'verify'])->name('customer.2fa.verify')->middleware('auth:customer');
Route::post('/2fa-resend', [\App\Http\Controllers\Email2faController::class, 'resend'])->name('customer.2fa.resend')->middleware('auth:customer');
Route::get('/set-locale/{locale}', [LocaleController::class, 'setLocale'])->name('set.locale');

require __DIR__ . '/auth.php';
require __DIR__ . '/customer-auth.php';

Route::group([
  'prefix' => 'global_settings',
], function () {
  Route::get('/', [GlobalSettingsController::class, 'index'])
    ->name('global_settings.global_setting.index');
  Route::get('/create', [GlobalSettingsController::class, 'create'])
    ->name('global_settings.global_setting.create');
  Route::get('/show/{globalSetting}', [GlobalSettingsController::class, 'show'])
    ->name('global_settings.global_setting.show')->where('id', '[0-9]+');
  Route::get('/{globalSetting}/edit', [GlobalSettingsController::class, 'edit'])
    ->name('global_settings.global_setting.edit')->where('id', '[0-9]+');
  Route::post('/', [GlobalSettingsController::class, 'store'])
    ->name('global_settings.global_setting.store');
  Route::put('global_setting/{globalSetting}', [GlobalSettingsController::class, 'update'])
    ->name('global_settings.global_setting.update')->where('id', '[0-9]+');
  Route::delete('/global_setting/{globalSetting}', [GlobalSettingsController::class, 'destroy'])
    ->name('global_settings.global_setting.destroy')->where('id', '[0-9]+');
});

Route::group([
  'prefix' => 'delivery_companies',
], function () {
  Route::get('/', [DeliveryCompaniesController::class, 'index'])
    ->name('delivery_companies.delivery_company.index');
  Route::get('/create', [DeliveryCompaniesController::class, 'create'])
    ->name('delivery_companies.delivery_company.create');
  Route::get('/show/{deliveryCompany}', [DeliveryCompaniesController::class, 'show'])
    ->name('delivery_companies.delivery_company.show')->where('id', '[0-9]+');
  Route::get('/{deliveryCompany}/edit', [DeliveryCompaniesController::class, 'edit'])
    ->name('delivery_companies.delivery_company.edit')->where('id', '[0-9]+');
  Route::post('/', [DeliveryCompaniesController::class, 'store'])
    ->name('delivery_companies.delivery_company.store');
  Route::put('delivery_company/{deliveryCompany}', [DeliveryCompaniesController::class, 'update'])
    ->name('delivery_companies.delivery_company.update')->where('id', '[0-9]+');
  Route::delete('/delivery_company/{deliveryCompany}', [DeliveryCompaniesController::class, 'destroy'])
    ->name('delivery_companies.delivery_company.destroy')->where('id', '[0-9]+');
});

Route::group([
  'prefix' => 'statuses',
], function () {
  Route::get('/', [StatusesController::class, 'index'])
    ->name('statuses.status.index');
  Route::get('/create', [StatusesController::class, 'create'])
    ->name('statuses.status.create');
  Route::get('/show/{status}', [StatusesController::class, 'show'])
    ->name('statuses.status.show')->where('id', '[0-9]+');
  Route::get('/{status}/edit', [StatusesController::class, 'edit'])
    ->name('statuses.status.edit')->where('id', '[0-9]+');
  Route::post('/', [StatusesController::class, 'store'])
    ->name('statuses.status.store');
  Route::put('status/{status}', [StatusesController::class, 'update'])
    ->name('statuses.status.update')->where('id', '[0-9]+');
  Route::delete('/status/{status}', [StatusesController::class, 'destroy'])
    ->name('statuses.status.destroy')->where('id', '[0-9]+');
});

Route::group([
  'prefix' => 'expense_categories',
], function () {
  Route::get('/', [ExpenseCategoriesController::class, 'index'])
    ->name('expense_categories.expense_category.index');
  Route::get('/create', [ExpenseCategoriesController::class, 'create'])
    ->name('expense_categories.expense_category.create');
  Route::get('/show/{expenseCategory}', [ExpenseCategoriesController::class, 'show'])
    ->name('expense_categories.expense_category.show')->where('id', '[0-9]+');
  Route::get('/{expenseCategory}/edit', [ExpenseCategoriesController::class, 'edit'])
    ->name('expense_categories.expense_category.edit')->where('id', '[0-9]+');
  Route::post('/', [ExpenseCategoriesController::class, 'store'])
    ->name('expense_categories.expense_category.store');
  Route::put('expense_category/{expenseCategory}', [ExpenseCategoriesController::class, 'update'])
    ->name('expense_categories.expense_category.update')->where('id', '[0-9]+');
  Route::delete('/expense_category/{expenseCategory}', [ExpenseCategoriesController::class, 'destroy'])
    ->name('expense_categories.expense_category.destroy')->where('id', '[0-9]+');
});

Route::group([
  'prefix' => 'expenses',
], function () {
  Route::get('/', [ExpensesController::class, 'index'])
    ->name('expenses.expense.index');
  Route::get('/create', [ExpensesController::class, 'create'])
    ->name('expenses.expense.create');
  Route::get('/show/{expense}', [ExpensesController::class, 'show'])
    ->name('expenses.expense.show')->where('id', '[0-9]+');
  Route::get('/{expense}/edit', [ExpensesController::class, 'edit'])
    ->name('expenses.expense.edit')->where('id', '[0-9]+');
  Route::post('/', [ExpensesController::class, 'store'])
    ->name('expenses.expense.store');
  Route::put('expense/{expense}', [ExpensesController::class, 'update'])
    ->name('expenses.expense.update')->where('id', '[0-9]+');
  Route::delete('/expense/{expense}', [ExpensesController::class, 'destroy'])
    ->name('expenses.expense.destroy')->where('id', '[0-9]+');
});

Route::group([
  'prefix' => 'sliders',
], function () {
  Route::get('/', [SlidersController::class, 'index'])
    ->name('sliders.slider.index');
  Route::get('/create', [SlidersController::class, 'create'])
    ->name('sliders.slider.create');
  Route::get('/show/{slider}', [SlidersController::class, 'show'])
    ->name('sliders.slider.show')->where('id', '[0-9]+');
  Route::get('/{slider}/edit', [SlidersController::class, 'edit'])
    ->name('sliders.slider.edit')->where('id', '[0-9]+');
  Route::post('/', [SlidersController::class, 'store'])
    ->name('sliders.slider.store');
  Route::put('slider/{slider}', [SlidersController::class, 'update'])
    ->name('sliders.slider.update')->where('id', '[0-9]+');
  Route::delete('/slider/{slider}', [SlidersController::class, 'destroy'])
    ->name('sliders.slider.destroy')->where('id', '[0-9]+');
});
