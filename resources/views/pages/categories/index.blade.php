@extends('layouts.app')
@section('title')
    Product Category | {{ config('app.name', 'Laravel') }}
@endsection
@section('cssRequired')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('cssNeeded')
    <link rel="stylesheet" href="{{ asset('pages/css/category/index.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Product Category List</h6>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <a class="btn btn-sm btn-primary" href="javascript:;" data-bs-toggle="modal"
                                            data-bs-target="#mdCategory" data-type="add" data-id="" data-name=""><i
                                                class="fa fa-plus me-2"></i>
                                            Create</a>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-0">
                </div>
                <div class="card-body px-5 pt-0 pb-4">
                    <table class="table nowrap align-items-center align-middle mb-0" id="tb_Category">
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
