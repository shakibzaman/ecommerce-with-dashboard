@extends('layouts/contentNavbarLayout')

@section('title', 'Permission List')

@section('vendor-script')
@vite('resources/assets/vendor/libs/masonry/masonry.js')
@endsection

@section('content')
<div class="card-body">
    @if ($message = Session::get('success'))
    <div class="alert alert-primary">
        <p>{{ $message }}</p>
    </div>
    @endif
    <div class="col-lg-3 col-md-6">
        @include('units.includes.create')
    </div>
    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Slug</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($units as $unit)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $unit->name }}</td>
            <td>{{ $unit->abbreviation }}</td>
            <td>

                <div class="btn-group" role="group" aria-label="Role Actions">
                    @include('units.includes.edit', [
                    'unit' => $unit
                    ])
                    @include('units.includes.delete', ['id' => $unit->id])
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    <div>
        {{ $units->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection