@extends('layouts/contentNavbarLayout')

@section('title', 'Order List')

@section('vendor-script')
@vite('resources/assets/vendor/libs/masonry/masonry.js')
@endsection

@section('content')
<div class="card p-2">
    <h5 class="card-header">Order List</h5>
    <div class="p-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style2 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('wholesalers.index') }}">Orders</a>
                </li>
                <li class="breadcrumb-item active text-danger">List</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Delivery</th>
                <th>Total</th>
                <th>Discount</th>
                <th>Delivery Charge</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at }}</td>
                <td>{{ $order->customer->name }}</td>
                <td>{{ $order->customer->phone }}</td>
                <td>{{ $order->status->name }}</td>
                <td>{{ $order->delivery->name }}</td>
                <td>{{ $order->total }}</td>
                <td>{{ $order->discount }}</td>
                <td>{{ $order->delivery_charge }}</td>
                <td>{{ $order->paid ?? 0}}</td>
                <td>{{ $order->due ?? 0}}</td>
                <td>{{ $order->creator->name ?? 0}}</td>
                <td>
                    @can("order-change")
                    @include('orders.modals.status', ['order' => $order,'statuses'=>$statuses])
                    @endcan

                    @can("order-payment")

                    @include('orders.modals.payment', ['order' => $order])
                    @endcan

                    @can("order-edit")
                    @if($order->status_id == config('status.pending'))
                    <a class="btn btn-primary" href="{{ route('order.edit',$order->id) }}">Edit</a>
                    @endif
                    @endcan
                    @include('orders.modals.invoice', ['order' => $order])
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- Modal Structure -->
<div class="modal fade" id="unavailableProductModal" tabindex="-1" aria-labelledby="unavailableProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog card">
        <div class="unavailable-product-modal-content">
            <div class="modal-header">
                {{-- <h5 class="modal-title" id="unavailableProductModalLabel">Un-Available Product Details</h5> --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="unavailable-product-modal-content">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
<!--/ Contextual Classes -->
@endsection

<style>
    .btn-group .btn {
        margin-right: 5px;
        /* Adjust spacing between buttons */
    }

    .btn-group .btn:last-child {
        margin-right: 0;
        /* Remove right margin from the last button */
    }
</style>