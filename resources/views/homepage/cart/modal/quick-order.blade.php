<div class="cart-modal-body">

    <!-- Cart Section -->
    <div class="cart mb-4">
        @php
        $carts = session()->get('cart', []);
        @endphp

        @if (!empty($carts))
        <h5>My Shopping Bag ({{ count($carts) }} Items)</h5>
        <ul class="list-group">
            @php
            $totalQuantity = 0;
            $totalPrice = 0;
            @endphp

            @foreach ($carts as $key => $item)
            @php
            $totalQuantity += $item['quantity'];
            $totalPrice += $item['price'] * $item['quantity'];
            @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/products/thumb/' . $item['image']) }}" alt="{{ $item['name'] }}"
                        width="50" class="me-3">
                    <div>
                        <p class="mb-0">
                            <strong>{{ $item['name'] }}</strong> (x{{ $item['quantity'] }})<br>
                            <span class="text-primary">{{ $item['price'] }}/</span> <s class="text-muted">300</s>
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-pinned remove-from-cart" aria-label="Close"
                    data-item-id="{{ $key }}"></button>
            </li>
            @endforeach
        </ul>
        @endif
        <!-- Total Calculation -->
        <div class="total">
            <table class="table">
                <tbody>
                    <tr>
                        <th>সাব টোটাল</th>
                        <td>{{ $totalPrice }}</td>
                    </tr>
                    <tr>
                        <th>ডেলিভারি চার্জ</th>
                        <td>{{ $totalPrice }}</td>
                    </tr>
                    <tr>
                        <th>মোট</th>
                        <td id="final_total">{{ $totalPrice }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function bindDeliveryOptionChange() {
        const deliveryOptions = document.querySelectorAll('input[name="deliveryOption"]');
        let totalPrice = parseFloat('{{ $totalPrice }}'); // Fetch the current total price from the PHP variable

        deliveryOptions.forEach(option => {
            option.addEventListener('change', function () {
                let deliveryCharge = 0;

                if (this.id === 'outsideDhaka') {
                    deliveryCharge = 120;
                } else if (this.id === 'insideDhaka') {
                    deliveryCharge = 70;
                }

                const finalTotal = totalPrice + deliveryCharge;
                document.getElementById('final_total').innerText = finalTotal;
            });
        });

        // Trigger change event on page load to ensure the correct delivery option is applied
        const selectedOption = document.querySelector('input[name="deliveryOption"]:checked');
        if (selectedOption) {
            selectedOption.dispatchEvent(new Event('change'));
        }
    }
</script>