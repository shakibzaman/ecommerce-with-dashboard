<?php

namespace App\Providers;

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    $globalSetting = GlobalSetting::where('id', 1)->first();

    if ($globalSetting) {
      view()->composer('*', function ($view) {
        $globalSetting = GlobalSetting::where('id', 1)->first();
        $cart = session()->get('cart', []);
        $view->with('globalSetting', $globalSetting)->with('cart', $cart);
      });
    }
    Gate::define('admin-menu', function () {
      if (auth()->guard('web')->user()) {
        return auth()->guard('web')->user()->type == 1;
      }
    });

    Gate::define('customer-menu', function () {
      if (auth()->guard('customer')->user()) {
        return auth()->guard('customer')->user();
      }
    });

    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });

    if ($this->app->environment('production')) {
      URL::forceScheme('https');
    }
  }
}
