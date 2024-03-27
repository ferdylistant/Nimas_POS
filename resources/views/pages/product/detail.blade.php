@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Detail Produk
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Detail Produk</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <a href="{{ url('products') }}" class="btn bg-gradient-primary btn-sm"><i class="fa fa-arrow-left"></i>
                                Kembali</a>
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
                                        <img src="{{url('storage/product/img/'.$product->image)}}" alt="..." class="avatar shadow">
                                    </li>
                                    <li class="list-group-item  align-items-center">
                                        <span class="text-sm">Product Code</span>
                                        <br>
                                        <span class="text-dark">{{ $product->product_code }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Product Name</span>
                                        <br>
                                        <span class="text-dark">{{ $product->product_name }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Category</span>
                                        <br>
                                        <span class="text-dark">{{ $product->product_name }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Stock</span>
                                        <br>
                                        <span class="text-dark">{{ $product->total_stock }}</span>
                                    </li>
                                    <li class="list-group-item align-items-center">
                                        <span class="text-sm">Buying Date</span>
                                        <br>
                                        <span class="text-dark">{{ Carbon\Carbon::parse($product->buying_date)->translatedFormat('d M Y') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row mb-3">
                                <h6 class="card-title"><i class="fa fa-users"></i> Supplier</h6>
                                <div class="border border-radius-md">
                                    <div class="table-responsive">
                                        <table class="table" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col text-center">Supplier Name</th>
                                                    <th scope="col text-center">Quantity</th>
                                                    <th scope="col text-center">Buying Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($productSupplier as $psup)
                                                <tr>
                                                    <td class="text-dark text-center">{{ $psup->supplier_name }}</td>
                                                    <td class="text-dark text-center">{{ $psup->product_qty }}</td>
                                                    <td class="text-dark text-center">@format_rupiah($psup->buying_price)</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <h6 class="card-title"><i class="fa fa-tag"></i> Selling Price</h6>
                                <ul class="list-group">
                                    @foreach ($productSelling as $ps)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-sm">{{$ps->type}}</span>
                                        <span class="text-dark">@format_rupiah($ps->selling_price)</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
