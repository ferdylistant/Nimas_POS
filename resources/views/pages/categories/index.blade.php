@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Laravel') }} | Kategori Produk
@endsection
@section('cssRequired')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">
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
                                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v text-secondary"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5"
                                    aria-labelledby="dropdownTable">
                                    <li><a class="dropdown-item border-radius-md" href="javascript:;" data-bs-toggle="modal"
                                            data-bs-target="#mdCreate"><i class="fa fa-plus me-2"></i> Create</a></li>
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
                <div class="card-body px-5 pt-0 pb-2">
                    <div class="table-responsive p-2">
                        <table class="table align-items-center mb-0" id="tb_Category">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="mdCreate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalMessageTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Gallery</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Title:</label>
                                    <input type="text" class="form-control" name="title" id="recipient-name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label" for="fileImg">Upload</label>
                                    <div class="input-group mb-3">
                                        <input type="file" name="image" class="form-control" id="fileImg">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <img src="" alt="" id="image-previewer" class="img-fluid rounded">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn bg-gradient-primary">Send message</button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('jsRequired')
<script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
@endsection
@section('jsNeeded')
    <script src="{{ asset('pages/js/category/index.js') }}"></script>
@endsection
