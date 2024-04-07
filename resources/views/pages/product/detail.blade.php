@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Detail Produk
@endsection
@section('cssRequired')
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
                            <h6>Detail Produk</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <a href="{{ url('products') }}" class="btn bg-gradient-primary btn-sm"><i
                                    class="fa fa-arrow-left"></i>
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
                                        <img src="{{ url('storage/product/img/' . $product->image) }}" alt="..."
                                            class="avatar shadow">
                                    </li>
                                    <li class="list-group-item  align-items-center">
                                        <span class="text-sm">Product Code</span>
                                        <br>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-dark">{{ $product->product_code }}</span>
                                            <a href="javascript:void(0)" class="text-primary" data-bs-toggle="modal"
                                                data-bs-target="#mdBarcode" title="Print Barcode"><i
                                                    class="fa fa-barcode"></i></a>
                                        </div>
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
                                        <span
                                            class="text-dark">{{ $product->total_stock . ' ' . $product->unit_satuan }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row mb-3">
                                <h6 class="card-title"><i class="fa fa-users"></i> Supplier</h6>
                                <div class="border border-radius-md">
                                    <div class="table-responsive">
                                        <table id="tableSupplier" class="table" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col text-center">Supplier Name</th>
                                                    <th scope="col text-center">Quantity</th>
                                                    <th scope="col text-center">Buying Price</th>
                                                    <th scope="col text-center">Buying Date</th>
                                                    <th scope="col text-center">Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($productSupplier as $i => $psup)
                                                    <tr>
                                                        <td class="text-dark">
                                                            @if ($psup->supplier_count > 1)
                                                                <a class="btn-toggle-collapse"
                                                                    id="btn-toggle-collapse{{ $i + 1 }}"
                                                                    data-bs-toggle="collapse"
                                                                    href="#collapse{{ $i + 1 }}" role="button"
                                                                    aria-expanded="false"
                                                                    aria-controls="collapse{{ $i + 1 }}">
                                                                    <i class="fas fa-plus-circle"></i></a>
                                                            @endif
                                                            {{ $psup->supplier_name }}

                                                        </td>
                                                        <td class="text-dark text-center">
                                                            {{ $psup->product_qty . ' ' . $product->unit_satuan }}</td>
                                                        <td class="text-dark text-center">@format_rupiah($psup->buying_price)</td>
                                                        <td class="text-dark text-center">
                                                            {{ Carbon\Carbon::parse($psup->buying_date)->translatedFormat('d M Y') }}
                                                        </td>
                                                        <td class="text-dark text-center">
                                                            {{ Carbon\Carbon::parse($psup->created_at)->translatedFormat('d M Y') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5">
                                                            <table class="table collapse" id="collapse{{ $i + 1 }}">
                                                                <tbody>
                                                                    @if ($psup->supplier_count > 1)
                                                                        @php
                                                                            $supData = DB::table('product_suppliers as ps')
                                                                                ->join(
                                                                                    'suppliers',
                                                                                    'ps.supplier_id',
                                                                                    'suppliers.id',
                                                                                )
                                                                                ->where('ps.product_id', $psup->product_id)
                                                                                ->where('ps.supplier_id', $psup->supplier_id)
                                                                                ->orderBy('ps.id', 'ASC')
                                                                                ->select(
                                                                                    'ps.*',
                                                                                    'suppliers.name as supplier_name'
                                                                                )
                                                                                ->get();
                                                                        @endphp
                                                                        @foreach ($supData as $k => $sd)
                                                                        @if ($k != 0)

                                                                        <tr>
                                                                            <td class="text-dark text-center"><i class="fa fa-genderless text-sm text-gradient text-primary"></i> {{ $sd->supplier_name }}</td>
                                                                            <td class="text-dark text-center">
                                                                                {{ $sd->product_qty . ' ' . $product->unit_satuan }}</td>
                                                                            <td class="text-dark text-center">@format_rupiah($sd->buying_price)</td>
                                                                            <td class="text-dark text-center">
                                                                                {{ Carbon\Carbon::parse($sd->buying_date)->translatedFormat('d M Y') }}
                                                                            </td>
                                                                            <td class="text-dark text-center">
                                                                                {{ Carbon\Carbon::parse($sd->created_at)->translatedFormat('d M Y') }}
                                                                            </td>
                                                                        </tr>
                                                                        @endif
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                            {{-- <div class="collapse" id="collapse{{$i+1}}">
                                                            <div class="card card-body">
                                                              Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
                                                            </div>
                                                        </div> --}}
                                                        </td>
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
                                            <span class="text-sm">{{ $ps->type }}</span>
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
    @include('pages.product.include.modalBarcode')
@endsection
@section('jsRequired')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"
        integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection
@section('jsNeeded')
    <script src="{{ asset('pages/js/product/detail.js') }}"></script>
@endsection
