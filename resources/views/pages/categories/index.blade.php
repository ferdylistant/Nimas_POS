@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Kategori Produk
@endsection
@section('cssRequired')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('cssNeeded')
    <style>
        table.dataTable thead th {
            font-size: 0.9em;
        }

        table.dataTable tbody td {
            font-size: 0.9em;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Kategori Produk List</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <div class="dropdown float-lg-end pe-4">
                                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v text-secondary"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5"
                                    aria-labelledby="dropdownTable">
                                    <li><a class="dropdown-item border-radius-md" href="javascript:;" data-bs-toggle="modal"
                                            data-bs-target="#mdCategory" data-type="add" data-id="" data-name=""><i
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
                    <table class="table align-items-center mb-0" id="tb_Category">
                    </table>
                </div>
            </div>
        </div>
        @include('pages.categories.include.modalCategory')
    </div>
@endsection
@section('jsRequired')
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js" integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection
@section('jsNeeded')
    <script src="{{ asset('pages/js/category/index.js') }}"></script>
@endsection
