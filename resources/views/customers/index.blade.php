@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="page-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customers</h3>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">Add customer</a>
    </div>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name or email">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Search</button>
        </div>
    </form>
    <table class="table table-striped">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $customers->links() }}
</div>
@endsection
