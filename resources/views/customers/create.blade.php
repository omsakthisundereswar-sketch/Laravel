@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
<div class="page-card p-4">
    <h3>Create Customer</h3>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            </div>
        </div>
        <button class="btn btn-primary mt-4">Save Customer</button>
    </form>
</div>
@endsection
