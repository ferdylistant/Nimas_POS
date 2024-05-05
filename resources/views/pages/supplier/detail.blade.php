@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Detail Produk
@endsection
@section('cssRequired')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.0/css/fixedColumns.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css"
        integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Detail Supplier</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <button onclick="history.back()" class="btn bg-gradient-primary btn-sm"><i
                                class="fa fa-arrow-left"></i>
                            Kembali</button>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-0">
                </div>
                <div class="card-body pt-2">
                    <div class="row px-3">
                        <div class="col-lg-4 mb-3">
                            <div class="row">
                                <h6 class="card-title"><i class="fa fa-box"></i> Basic</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <img src="{{ url('storage/supplier/img/' . $supplier->photo) }}" alt="..."
                                            class="avatar shadow">
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Name</span>
                                        <br>
                                        <span class="text-dark">{{ $supplier->name }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Email</span>
                                        <br>
                                        <span class="text-dark">{{ $supplier->email }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Phone</span>
                                        <br>
                                        <span class="text-dark">{{ $supplier->phone }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Address</span>
                                        <br>
                                        <span class="text-dark">{{ $supplier->address }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row mb-3">
                                <h6 class="card-title"><i class="fas fa-people-carry"></i> Supplier on product</h6>
                                <div class="border border-radius-md">
                                    <div class="table-responsive">
                                        <table id="tableSupplier" class="table" style="width: 100%"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    {{-- @include('pages.customer.include.modalBarcode') --}}
@endsection
@section('jsRequired')
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/fixedColumns.bootstrap5.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"
        integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection
@section('jsNeeded')
    <script src="{{ asset('pages/js/supplier/detail.js') }}"></script>
@endsection
