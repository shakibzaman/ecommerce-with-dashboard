<!-- Modal Structure -->
<div class="modal fade" id="customModal" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="customModalLabel">
                    ক্যাশ অন ডেলিভারিতে অর্ডার করতে আপনার তথ্য দিন

                </h4>
                <button type="button" class="btn-close" id="closeModalButton" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="order_submit">
                    @csrf
                    <!-- Customer Information -->
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">নাম</label>
                        <input type="text" class="form-control" id="nameInput" name="name"
                            placeholder="আপনার নাম লিখুন">
                    </div>

                    <div class="mb-3">
                        <label for="phoneInput" class="form-label">মোবাইল</label>
                        <input type="text" class="form-control" id="phoneInput" name="phone"
                            placeholder="আপনার মোবাইল নং লিখুন">
                    </div>

                    <div class="mb-3">
                        <label for="addressInput" class="form-label">ঠিকানা</label>
                        <input type="text" class="form-control" id="addressInput" name="address"
                            placeholder="আপনার ঠিকানা লিখুন">
                    </div>

                    <!-- Delivery Options -->
                    <div class="my-4">
                        <h5>ডেলিভারি প্রসেস</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="deliveryOption" value="120"
                                id="outsideDhaka" checked>
                            <label class="form-check-label" for="outsideDhaka">ঢাকা শহরের বাইরে ১২০ টাকা</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="70" name="deliveryOption"
                                id="insideDhaka">
                            <label class="form-check-label" for="insideDhaka">ঢাকা শহরের ভিতর ৭০ টাকা</label>
                        </div>
                    </div>
                    <div class="cart-modal-body"></div>
                    <!-- Order Button -->
                    <button type="submit" class="btn btn-primary w-100 mt-3 p-2 rounded">অর্ডার করুন </button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalFooterButton">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Button to trigger AJAX request and show modal
    var buttons = document.getElementsByClassName('openModalButton');
    Array.from(buttons).forEach(function(button) {
        button.addEventListener('click', function () {     
            $.ajax({
                url: '{{ route("order.modalContent") }}', // Backend route to load modal content
                method: 'GET',
                success: function (response) {
                    console.log(response.modalContent);
                    // Update the modal body with the new content
                    $('#customModal .cart-modal-body').html(response.modalContent);
                    
                    // Show the modal with options to prevent closing on backdrop or ESC key
                    var myModal = new bootstrap.Modal(document.getElementById('customModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();

                    // Rebind any necessary events after refreshing modal content
                    bindRemoveFromCart();
                    bindDeliveryOptionChange();
                },
                error: function (xhr) {
                    alert('Something went wrong! Please try again.');
                }
            });
        });
    });

    // Bind the close button functionality to close the modal
    document.getElementById('closeModalButton').addEventListener('click', function () {
        var myModalEl = document.getElementById('customModal');
        var modalInstance = bootstrap.Modal.getInstance(myModalEl); 
        modalInstance.hide();
    });

    // Bind the footer close button functionality to close the modal
    document.getElementById('closeModalFooterButton').addEventListener('click', function () {
        var myModalEl = document.getElementById('customModal');
        var modalInstance = bootstrap.Modal.getInstance(myModalEl); 
        modalInstance.hide();
    });

    // Function to bind "Remove from Cart" functionality
    function bindRemoveFromCart() {
        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                var itemId = this.getAttribute('data-item-id');

                $.ajax({
                    url: '{{ route("cart.remove") }}', // The route for removing items
                    method: 'POST',
                    data: {
                        item_id: itemId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        console.log('card-remove-response ==>',response);
                        if (response) {
                            // Update the cart section
                            $('.cart').html(response.cart);

                            // Update the total section
                            $('.total').html(response.total);

                            // Rebind event for new cart items
                            bindRemoveFromCart();
                            bindDeliveryOptionChange();

                            // Show a success message if needed
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        alert('Something went wrong!');
                    }
                });
            });
        });
    }

    // Handle the form submission with AJAX
    $('#order_submit').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Collect form data
        var formData = new FormData(this); // Collect the form data including file inputs

        $.ajax({
            url: '{{ route("order.done") }}', // Backend route that handles form submission
            method: 'POST',
            data: formData,
            processData: false,  // Prevent jQuery from automatically processing the data
            contentType: false,  // Set content type to false for proper handling of FormData
            success: function(response) {
                // Optionally, clear the form fields
                $('#order_submit')[0].reset();
                var myModalEl = document.getElementById('customModal');
                var modalInstance = bootstrap.Modal.getInstance(myModalEl); 
                modalInstance.hide();
                // Optionally, display some feedback in the cart-modal-body or elsewhere
                $('.cart-modal-body').html('<p>Thank you for your order! We will contact you soon.</p>');
                Swal.fire("Thank you for your order! We will contact you soon.");
            },
            error: function(xhr) {
                // Handle error response
                alert('Something went wrong! Please try again.');
                console.error(xhr.responseText); // Log the error for debugging
            }
        });
    });
});

</script>
{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Button to trigger AJAX request and show modal
        var buttons = document.getElementsByClassName('openModalButton');
            Array.from(buttons).forEach(function(button) {
                button.addEventListener('click', function () {     
                $.ajax({
                    url: '{{ route("order.modalContent") }}', // Backend route to load modal content
                    method: 'GET',
                    success: function (response) {
                        console.log(response.modalContent);
                        // Update the modal body with the new content
                        $('#customModal .cart-modal-body').html(response.modalContent);
                        
                        // Show the modal
                        var myModal = new bootstrap.Modal(document.getElementById('customModal'));
                        myModal.show();

                        // Rebind any necessary events after refreshing modal content
                        bindRemoveFromCart();
                        bindDeliveryOptionChange();
                    },
                    error: function (xhr) {
                        alert('Something went wrong! Please try again.');
                    }
                });
            });
        })

        // Function to bind "Remove from Cart" functionality
        function bindRemoveFromCart() {
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    var itemId = this.getAttribute('data-item-id');

                    $.ajax({
                        url: '{{ route("cart.remove") }}', // The route for removing items
                        method: 'POST',
                        data: {
                            item_id: itemId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            console.log('card-remove-response ==>',response);
                            if (response) {
                                // Update the cart section
                                $('.cart').html(response.cart);

                                // // Update the total section
                                $('.total').html(response.total);

                                // Rebind event for new cart items
                                bindRemoveFromCart();
                                bindDeliveryOptionChange();


                                // Show a success message if needed
                                alert(response.message);
                            }
                        },
                        error: function (xhr) {
                            alert('Something went wrong!');
                        }
                    });
                });
            });
        }
        // Function to bind delivery option changes and calculate the final total

        $('#order_submit').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Collect form data
        var formData = new FormData(this); // Collect the form data including file inputs

        $.ajax({
            url: '{{ route("order.done") }}', // Backend route that handles form submission
            method: 'POST',
            data: formData,
            processData: false,  // Prevent jQuery from automatically processing the data
            contentType: false,  // Set content type to false for proper handling of FormData
            success: function(response) {
                // Optionally, clear the form fields
                $('#order_submit')[0].reset();
                $('#customModal').hide();
                // Optionally, display some feedback in the cart-modal-body or elsewhere
                $('.cart-modal-body').html('<p>Thank you for your order! We will contact you soon.</p>');
                Swal.fire("Thank you for your order! We will contact you soon.");
            },
            error: function(xhr) {
                // Handle error response
                alert('Something went wrong! Please try again.');
                console.error(xhr.responseText); // Log the error for debugging
            }
        });
    });
    
    });
</script> --}}