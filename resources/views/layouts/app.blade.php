<!DOCTYPE html>
<html lang="en" class="@yield('html-class')">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Mallow Store')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { background: #f5f7fb; }
            .sidebar { min-height: 100vh; background: #1f2937; }
            .sidebar a { color: #dbeafe; }
            .sidebar a:hover { color: white; }
            .page-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
            .stat-card { border-radius: 16px; background: linear-gradient(135deg, #eff6ff, #ffffff); }
            html.billing-page, .billing-page { overflow-x: hidden; }
            .billing-page { background: #f4f6f9; color: #263248; }
            .billing-page .sidebar { display: none; }
            .billing-page main { flex: 0 0 100%; max-width: 100%; }
            .billing-layout { width: min(1120px, 100%); margin: 18px auto; }
            .billing-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 22px 28px; color: #fff; background: #1f293b; border-radius: 8px; }
            .billing-header h1 { margin: 0; font-size: 1.4rem; font-weight: 700; }
            .billing-header p { margin: 5px 0 0; color: #b9c3d2; font-size: .85rem; }
            .billing-header .billing-note { color: #9eabc0; font-size: .75rem; letter-spacing: .04em; text-align: right; }
            .billing-panel { padding: 22px; background: #fff; border: 1px solid #e1e6ed; border-radius: 10px; }
            .billing-section-title { margin: 0 0 14px; color: #29364b; font-size: 1rem; font-weight: 700; }
            .billing-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; max-width: 720px; }
            .billing-label { display: block; margin-bottom: 6px; color: #39465b; font-size: .82rem; font-weight: 700; }
            .billing-control { width: 100%; min-height: 42px; padding: 9px 12px; color: #283449; background: #fff; border: 1px solid #b8c5d6; border-radius: 6px; }
            .billing-control:focus { border-color: #3b82c4; outline: 3px solid #3b82c426; }
            .billing-control::placeholder { color: #9aa8bc; }
            .billing-content-grid { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(260px, .9fr); gap: 22px; align-items: start; margin-top: 20px; }
            .billing-column { display: grid; gap: 20px; }
            .billing-table-wrap { overflow-x: auto; border: 1px solid #b8c5d6; border-radius: 7px; }
            .billing-table { width: 100%; margin: 0; border-collapse: collapse; }
            .billing-table th, .billing-table td { padding: 10px 12px; border-bottom: 1px solid #dfe5ec; text-align: left; vertical-align: middle; }
            .billing-table th { color: #34445c; background: #e8edf4; font-size: .8rem; }
            .billing-table tr:last-child td { border-bottom: 0; }
            .billing-table .quantity-control { width: 86px; min-height: 38px; }
            .billing-table .product-control { min-width: 210px; }
            .billing-muted { color: #8491a4; font-size: .8rem; }
            .billing-remove { padding: 5px 9px; color: #a53d3d; background: #fff; border: 1px solid #e7bcbc; border-radius: 5px; }
            .billing-add { margin-top: 12px; padding: 9px 15px; color: #fff; background: #347db6; border: 0; border-radius: 6px; font-weight: 700; }
            .billing-stock { min-height: 210px; border: 1px solid #e88b22; background: #fffaf0; }
            .billing-stock .billing-section-title { color: #9a4f15; }
            .billing-stock ul { display: grid; gap: 11px; margin: 0; padding-left: 20px; color: #865124; font-size: .84rem; }
            .billing-payment { background: #f1f5f9; border-color: #b8c5d6; }
            .billing-total-row { display: flex; justify-content: space-between; gap: 16px; padding: 5px 0; font-size: .9rem; }
            .billing-grand-total { margin-top: 3px; padding-top: 10px; border-top: 1px dashed #9aa9bd; font-size: 1rem; font-weight: 800; }
            .billing-summary { width: min(390px, 100%); }
            .billing-change { margin-top: 12px; padding-top: 11px; border-top: 1px dashed #aab6c6; color: #34445c; font-weight: 700; }
            .billing-submit { width: 100%; min-height: 50px; color: #fff; background: #15803d; border: 0; border-radius: 7px; font-weight: 800; }
            .billing-submit:hover { background: #166534; }
            .billing-submit-note { margin: 9px 0 0; color: #718096; font-size: .76rem; }
            .billing-page .alert { max-width: 1120px; margin: 0 auto 15px; }
            @media (max-width: 767.98px) {
                .billing-page main { padding: 14px !important; }
                .billing-layout { margin: 0 auto; }
                .billing-header { flex-direction: column; align-items: flex-start; padding: 18px; }
                .billing-header .billing-note { max-width: none; text-align: left; }
                .billing-fields, .billing-content-grid { grid-template-columns: minmax(0, 1fr); }
                .billing-column, .billing-panel, .billing-table-wrap { min-width: 0; }
                .billing-table { min-width: 620px; }
                .billing-panel { padding: 16px; }
            }
        </style>
    </head>
    <body class="@yield('body-class')">
        <div class="container-fluid">
            <div class="row">
                <aside class="col-md-2 sidebar p-4">
                    <h3 class="text-white mb-4">StoreOps</h3>
                    <nav class="nav flex-column gap-2">
                        <a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-grid"></i> Dashboard</a>
                        <a class="nav-link" href="{{ route('products.index') }}"><i class="bi bi-box-seam"></i> Products</a>
                        <a class="nav-link" href="{{ route('customers.index') }}"><i class="bi bi-people"></i> Customers</a>
                        <a class="nav-link" href="{{ route('orders.index') }}"><i class="bi bi-receipt-cutoff"></i> Orders</a>
                        <a class="nav-link" href="{{ url('/api/products/low-stock') }}" target="_blank"><i class="bi bi-exclamation-triangle"></i> Low Stock API</a>
                    </nav>
                </aside>
                <main class="col-md-10 p-4">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
