@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Orders
@endsection
@section('cssRequired')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.0/css/fixedColumns.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('vendors/select2/select2-main.css') }}" rel="stylesheet" />
    {{-- <link rel="stylesheet" href="{{ asset('vendors/select2/select2.css') }}" /> --}}
@endsection
@section('cssNeeded')
    <link rel="stylesheet" href="{{asset('pages/css/order/index.css')}}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Order List</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <div class="dropdown float-lg-end pe-4">
                                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v text-secondary"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5"
                                    aria-labelledby="dropdownTable">
                                    <li><a class="dropdown-item border-radius-md" href="?modal=add" data-bs-toggle="modal"
                                            data-bs-target="#mdOrder"><i
                                                class="fa fa-plus me-2"></i>
                                            Create</a></li>
                                    <li><a class="dropdown-item border-radius-md" href="javascript:;"><i
                                                class="fa fa-eye me-2"></i> Preview</a>
                                    </li>
                                    <li><a class="dropdown-item border-radius-md text-danger" href="javascript:;"><i
                                                class="fa fa-trash me-2"></i> Delete All</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-0">
                </div>
                <div class="card-body px-5 pt-0 pb-4">
                    <table class="table align-items-center align-middle" id="tb_Order" style="width:100%">
                    </table>
                </div>
            </div>
        </div>
        @include('pages.order.include.modalOrder')
    </div>
@endsection
@section('jsRequired')
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/fixedColumns.bootstrap5.js"></script>
    <script src="{{ asset('vendors/jquery-validation/dist/jquery.validate.js') }}"></script>
    <script src="{{asset('vendors/jquery-validation/dist/additional-methods.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js" integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://js.bytescale.com/sdk/v3"></script>
@endsection
@section('jsNeeded')
    <script src="{{ asset('pages/js/order/index.js') }}"></script>
@endsection
