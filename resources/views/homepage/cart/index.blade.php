@extends('layouts/layoutFront')

@section('title', 'Checkout - Front Pages')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
'resources/assets/vendor/libs/rateyo/rateyo.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/wizard-ex-checkout.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/jquery/jquery.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
'resources/assets/vendor/libs/rateyo/rateyo.js',
'resources/assets/vendor/libs/cleavejs/cleave.js',
'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all product images
        let productImages = document.querySelectorAll('.product-img');

        productImages.forEach(function(img) {
            // Show the spinner image initially
            let spinner = img.parentElement.querySelector('.loading-spinner');
            spinner.style.display = 'block';

            // Create a new image object for loading
            let tempImg = new Image();
            tempImg.src = img.getAttribute('data-src');
            
            // Once the image is loaded, replace the src and remove the spinner
            tempImg.onload = function() {
                img.src = tempImg.src;
                img.style.opacity = 1; // Show the image
                spinner.style.display = 'none'; // Hide the spinner once the image is loaded
            }
        });
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

</script>

@endsection
@section('content')
<section class="section-py bg-body first-section-pt">

    <div class="container">
        @include('homepage/cart/content/cart-product-list')
        @include('homepage/cart/order-page')
    </div>
</section>
@endsection