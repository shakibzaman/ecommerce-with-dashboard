<div class="row">
    <div class="col-md-6">
        <div class="image" style="position: relative;">
            <a href="{{ route('front-product-show',$product->slug) }}">
                <!-- Placeholder image while loading -->
                <img src="{{ asset('images/placeholder.webp') }}" alt="" width="100%" class="product-img"
                    data-src="{{ asset('images/products/thumb/' . $product->image) }}" style="opacity: 0;">
                <!-- Spinner image -->
                <img src="{{ asset('images/spinner.gif') }}" alt="Loading..." class="loading-spinner"
                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; display: block;">
            </a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="product-info">
            <h2>{{ $product->name }}</h2>
            @if($product->discount > 0)
            <p><del>Tk. {{ $product->price }}</del> Tk. {{ $product->discount }}</p>
            @else
            <span>Tk. {{ $product->price }}</span>
            @endif
            <span class="badge bg-info p-2">{{ $product->category->name }}</span>
        </div>
        <div class="cart-option m-2">
            <div class="row">
                <button type="button" class="btn-primary p-2 rounded mb-2 add-to-cart"
                    data-product-id="{{ $product->id }}">
                    <span id="loader_{{ $product->id }}" class="hidden" style="display: none;">
                        <img src="{{ asset('images/loader.gif') }}" width="20">
                    </span>

                    Quick Add
                </button>
                <button type="button" class="btn-success p-2 rounded openModalButton">ক্যাশ অন
                    ডেলিভারিতে অর্ডার
                    করুন</button>
            </div>
        </div>
        <div class="product-description">
            {{ $product->description }}
        </div>
        <div class="call-to-action mt-5">
            <div class="card p-2">
                আমাদের যে কোন পণ্য অর্ডার করতে কল বা WhatsApp করুন:

                <span class="badge bg-info text-dark">
                    {{ $globalSetting->phone }}
                </span>
            </div>
        </div>
    </div>
</div>