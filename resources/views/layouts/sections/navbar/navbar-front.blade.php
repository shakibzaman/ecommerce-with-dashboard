@php
use Illuminate\Support\Facades\Route;
$currentRouteName = Route::currentRouteName();
$activeRoutes = ['front-pages-pricing', 'front-pages-payment', 'front-pages-checkout', 'front-pages-help-center'];
$activeClass = in_array($currentRouteName, $activeRoutes) ? 'active' : '';
@endphp
<!-- Navbar: Start -->
<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
      <!-- Menu logo wrapper: Start -->
      <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-4 me-xl-8">
        <!-- Mobile menu toggle: Start -->
        <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
          aria-label="Toggle navigation">
          <i class="ti ti-menu-2 ti-lg align-middle text-heading fw-medium"></i>
        </button>
        <!-- Mobile menu toggle: End -->
        <a href="/" class="">
          <div class="homepage-logo">
            <img src="{{ asset('storage/' . $globalSetting->logo) }}" width="100">
          </div>
        </a>
      </div>
      <!-- Menu logo wrapper: End -->

      <!-- Menu wrapper: Start -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
        <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl" type="button"
          data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
          aria-expanded="false" aria-label="Toggle navigation">
          <i class="ti ti-x ti-lg"></i>
        </button>

        <!-- Menu Items: Start -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link {{ $currentRouteName == 'pages-home' ? 'active' : '' }}"
              href="{{ route('pages-home') }}">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $currentRouteName == 'product-page' ? 'active' : '' }}"
              href="{{ route('product-page') }}">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $currentRouteName == 'cart-page' ? 'active' : '' }}"
              href="{{ route('cart-page') }}">Cart</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $currentRouteName == 'front-pages-contact' ? 'active' : '' }}"
              href="{{ route('pages-home') }}">Contact Us</a>
          </li>
        </ul>
        <!-- Menu Items: End -->

      </div>
      <div class="landing-menu-overlay d-lg-none"></div>
      <!-- Menu wrapper: End -->

      <!-- Toolbar: Start -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        @if($configData['hasCustomizer'] == true)
        <!-- Style Switcher -->
        <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-1">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class='ti ti-lg'></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                <span class="align-middle"><i class='ti ti-sun me-3'></i>Light</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                <span class="align-middle"><i class="ti ti-moon-stars me-3"></i>Dark</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                <span class="align-middle"><i class="ti ti-device-desktop-analytics me-3"></i>System</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- / Style Switcher -->
        @endif
        <!-- Navbar button: Start -->
        <li>
          <a href="javascript:;" class="btn btn-primary" target="_blank">
            <span class="tf-icons ti ti-login scaleX-n1-rtl me-md-1"></span>
            <span class="d-none d-md-block">Login/Register</span>
          </a>
        </li>
        <!-- Navbar button: End -->
        <li class="nav-item dropdown">
          <a class="nav-link cart-icon openModalButton" id="cartDropdown" role="button">
            <i class="ti ti-shopping-cart ti-lg"></i>
            <span class="badge bg-primary cart-count">0</span> <!-- Total Cart Items -->
            <span class="cart-total-price ms-2">৳ 0.00</span> <!-- Total Cart Price -->
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="cartDropdown">
            <!-- Cart items will go here, you can load it dynamically using AJAX -->
          </ul>
        </li>

      </ul>
      <!-- Toolbar: End -->
    </div>
  </div>
</nav>
<!-- Navbar: End -->