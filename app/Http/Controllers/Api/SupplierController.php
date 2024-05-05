<?php

namespace App\Http\Controllers\Api;

use Image;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\SupplierHistory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{

    //-----------------index------------------------------------------
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::all();
            $dataTables = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('photo', function ($data) {
                    $html = '';
                    $img = asset('storage/supplier/img/' . $data->photo);
                    $html = '<img src="' . $img . '" class="avatar avatar-sm" alt="Image ' . $data->name . '"/>';
                    return $html;
                })
                ->addColumn('created_at', function ($data) {
                    return Carbon::parse($data->created_at)->translatedFormat('d/m/Y H:i');
                })
                ->addColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->translatedFormat('d/m/Y H:i');
                })
                ->addColumn('action', function ($data) {
                    $option = '';
                    $option .= '<div class="dropstart float-lg-end pe-4">
                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" title="" data-bs-original-title="More Actions" aria-expanded="false">
                    <i class="fa fa-list-ul text-secondary  tooltip-wrapper"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5" style="z-index: 999!important;"
                    aria-labelledby="dropdownTable" >
                    <li><a class="dropdown-item border-radius-md" href="' . url('people/supplier/detail/' . $data->id) . '"><i class="fa fa-eye me-2"></i> Detail </a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=edit&id=' . $data->id . '&name=' . $data->name . '" data-bs-toggle="modal"
                            data-bs-target="#mdSupplier"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=history&id=' . $data->id . '&name=' . $data->name . '" data-bs-toggle="modal"
                    data-bs-target="#mdSupplier">
                    <i class="fa fa-history me-2"></i> History</a></li>
                    <li><a class="dropdown-item border-radius-md text-danger btnDeleteSupplier" href="javascript:;" data-id="' . $data->id . '" data-name="' . $data->name . '"><i
                                class="fa fa-trash me-2"></i> Delete</a></li>
                </ul>
            </div>';
                    return $option;
                })
                ->rawColumns(['photo', 'created_at', 'updated_at', 'action'])
                ->make(true);
            return $dataTables;
        }
        return view('pages.supplier.index', [
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm text-primary active" aria-current="page">Supplier</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Supplier</h6>
        </nav>'
        ]);
    }
    public function store(Request $request)
    {
        try {
            $imgSupplier = 'default.jpg';
            if ($request->hasFile('photo')) {
                $imgSupplier = explode('/', $request->file('photo')->store('supplier/img/'));
                $imgSupplier = end($imgSupplier);
            }
            DB::beginTransaction();
            $supplier = new Supplier;
            $supplier->name = $request->name;
            $supplier->email = $request->email;
            $supplier->phone = $request->phone;
            $supplier->address = $request->address;
            $supplier->photo = $imgSupplier;
            $supplier->shopname = $request->shopname;
            $supplier->save();
            $supplier_id = $supplier->id;

            DB::table('supplier_histories')->insert([
                'supplier_id' => $supplier_id,
                'type_history' => 'create',
                'content' =>  json_encode(['text' => 'Supplier (' . $request->name . ') dibuat.']),
                'created_by' => auth()->user()->id
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier created successfully.'
            ]);
        } catch (\Exception $e) {
            if ($imgSupplier != 'default.jpg') {
                Storage::delete('supplier/img/' . $imgSupplier);
            }
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    public function show($id)
    {
        if (request()->ajax()) {
            $data = DB::table('product_suppliers as ps')
            ->join('products as p','ps.product_id','=','p.id')
            ->where('ps.supplier_id',$id)
            ->select('ps.*','p.product_name','p.product_code')
            ->get();
            $dataTables = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('buying_date', function ($data) {
                    return Carbon::parse($data->buying_date)->translatedFormat('d M Y');
                })
                ->addColumn('action', function ($data) {
                    $option = '';
                    $option .= '<a href="'.url('products/product-list/detail/'.$data->product_id).'"><i class="fas fa-eye"></i></a>';
                    return $option;
                })
                ->rawColumns(['action'])
                ->make(true);
            return $dataTables;
        }
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        return view('pages.supplier.detail', [
            'supplier' => $supplier,
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('supplier.index') . '">Supplier</a>
                </li>
                <li class="breadcrumb-item text-sm text-primary active" aria-current="page">Detail</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Detail</h6>
        </nav>'
        ]);
    }
    public function update(Request $request)
    {
        try {
            $data = DB::table('suppliers')->where('id',$request->id)->first();
            $imgSupplier = $data->photo;
            if ($request->has('photo')) {
                if ($request->hasFile('photo')) {
                    if ($imgSupplier != 'default.jpg') {
                        Storage::delete('supplier/img/' . $imgSupplier);
                    }
                    $imgSupplier = explode('/', $request->file('photo')->store('supplier/img/'));
                    $imgSupplier = end($imgSupplier);
                }
            }
            DB::beginTransaction();
            $update = DB::table('suppliers')->where('id', $request->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'shopname' => $request->shopname,
                'photo' => $imgSupplier
            ]);
            if ($update) {
                $content = [
                    'name_his' => $request->name === $data->name ? NULL : $data->name,
                    'name_new' => $request->name === $data->name ? NULL : $request->name,
                    'email_his' => $request->email === $data->email ? NULL : $data->email,
                    'email_new' => $request->email === $data->email ? NULL : $request->email,
                    'phone_his' => $request->phone === $data->phone ? NULL : $data->phone,
                    'phone_new' => $request->phone === $data->phone ? NULL : $request->phone,
                    'address_his' => $request->address === $data->address ? NULL : $data->address,
                    'address_new' => $request->address === $data->address ? NULL : $request->address,
                    'shopname_his' => $request->shopname === $data->shopname ? NULL : $data->shopname,
                    'shopname_new' => $request->shopname === $data->shopname ? NULL : $request->shopname,
                    'photo_his' => $imgSupplier === $data->photo ? NULL : $data->photo,
                    'photo_new' => $imgSupplier === $data->photo ? NULL : $imgSupplier
                ];
                DB::table('supplier_histories')->insert([
                    'supplier_id' => $request->id,
                    'type_history' => 'update',
                    'content' =>  json_encode($content),
                    'created_by' => auth()->user()->id
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier updated successfully.'
            ]);
        } catch (\Exception $e) {
            if ($imgSupplier != 'default.jpg') {
                Storage::delete('supplier/img/' . $imgSupplier);
            }
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    public function destroy($id)
    {
        try {
            $validation = DB::table('product_suppliers')->where('supplier_id', $id)->exists();
            if ($validation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Supplier cannot be deleted because it has assigned to products.'
                ]);
            }
            $supplier = DB::table('suppliers')->where('id', $id)->first();
            $photo = $supplier->photo;
            if($photo != 'default.jpg') {
                Storage::delete('supplier/img/' .  $photo);
            }
            DB::beginTransaction();
            DB::table('suppliers')->where('id', $id)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],$e->getCode());
        }
    }
    public function ajaxModal(Request $request)
    {
        if ($request->ajax()) {
            switch ($request->type) {
                case 'edit':
                    return self::showModalEdit($request);
                    break;
                case 'add':
                    return self::showModalCreate();
                    break;
                case 'history':
                    return self::showModalHistory($request);
                    break;
                default:
                    return abort(404);
                    break;
            }
        }
        return abort(400);
    }
    protected function showModalCreate()
    {
        $html = '';
        $html .= '<form id="fm_addSupplier">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-4">
                    <label for="nameField" class="col-form-label">Supplier Name: <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="nameField" class="form-control form-control-sm" placeholder="Enter Supplier Name" required>
                    <span id="err_name"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="emailField" class="col-form-label">Email: <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="emailField" class="form-control form-control-sm" placeholder="example@gmail.com" required>
                    <span id="err_email"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="phoneField" class="col-form-label">Phone: <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phoneField" class="form-control form-control-sm" placeholder="08*********" required>
                    <span id="err_phone"></span>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="addressField" class="col-form-label">Address: <span class="text-danger">*</span></label>
                    <textarea name="address" id="addressField" class="form-control form-control-sm" placeholder="Enter Address" required></textarea>
                    <span id="err_address"></span>
                </div>
                <div class="form-group col-md-6">
                    <label for="shopnameField" class="col-form-label">Shop Name: <small class="text-secondary">(Opsional)</small></label>
                    <input type="text" name="shopname" id="shopnameField" class="form-control form-control-sm" placeholder="Enter Shop Name">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="photoField" class="col-form-label">Photo: <small class="text-secondary">(Opsional)</small></label>
                    <input type="file" name="photo" id="photoField" onchange="onFileSelected(event)" class="form-control form-control-sm">
                    <span id="err_photo"></span>
                </div>
                <div id="image_preview"></div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Supplier';
        $idForm = 'fm_addSupplier';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
    protected function showModalEdit($request)
    {
        $data = Supplier::find($request->id);
        $html = '';
        $html .= '<form id="fm_editSupplier">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-4">
                    <label for="nameField" class="col-form-label">Supplier Name: <span class="text-danger">*</span></label>
                    <input type="hidden" name="id" value="' . $data->id . '">
                    <input type="text" name="name" id="nameField" class="form-control form-control-sm" value="' . $data->name . '" placeholder="Enter Supplier Name" required>
                    <span id="err_name"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="emailField" class="col-form-label">Email: <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="emailField" class="form-control form-control-sm" value="' . $data->email . '" placeholder="example@gmail.com" required>
                    <span id="err_email"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="phoneField" class="col-form-label">Phone: <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phoneField" class="form-control form-control-sm" value="' . $data->phone . '" placeholder="08*********" required>
                    <span id="err_phone"></span>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="addressField" class="col-form-label">Address: <span class="text-danger">*</span></label>
                    <textarea name="address" id="addressField" class="form-control form-control-sm" placeholder="Enter Address" required>' . $data->address . '</textarea>
                    <span id="err_address"></span>
                </div>
                <div class="form-group col-md-6">
                    <label for="shopnameField" class="col-form-label">Shop Name: <small class="text-secondary">(Opsional)</small></label>
                    <input type="text" name="shopname" id="shopnameField" class="form-control form-control-sm" value="' . $data->shopname . '" placeholder="Enter Shop Name">
                </div>
            </div>
            <div class="row">
            <div id="imgEdit">
                <div class="form-group col-md-12">
                    <label for="imageField" class="col-form-label">Photo: <small class="text-secondary">(Opsional)</small></label>
                    <br>
                    <img src="' . asset('storage/supplier/img/' . $data->photo) . '" width="200" class="img-thumbnail rounded">
                </div>
                <a href="javascript:void(0)" class="text-gradient text-primary btnChangeImg" title="Change Image"><i class="fas fa-pen"></i> Change Image</a>
                </div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Edit Supplier';
        $idForm = 'fm_editSupplier';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm,
            'data' => $data
        ];
    }
    protected function showModalHistory($request)
    {
        $supplier_id = $request->id;
        $data = DB::table('supplier_histories')->where('supplier_id', $supplier_id)->orderBy('id', 'ASC')->paginate(2);
        $title = '<i class="fa fa-history me-2"></i> History (' . $request->name . ')';
        $html = '';
        $htmlSub = '';
        if (!$data->isEmpty()) {
            $html = '<div class="row">
            <div class="col-md-12">
            <div class="timeline timeline-one-side">';
            foreach ($data as $key => $value) {
                switch ($value->type_history) {
                    case 'create':
                        $htmlSub .= '<div class="timeline-block">
                        <span class="timeline-step">
                            <i class="ni ni-money-coins text-dark text-gradient"></i>
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0">' . json_decode($value->content)->text . '</h6>
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . ' - ' . Carbon::parse($value->created_at)->format('d/m/Y H:i') . '</p>
                            <span class="text-xs font-weight-bold mb-0">Dibuat oleh: <span class="text-dark text-xs font-weight-bold mb-0">' . User::find($value->created_by)->name . '</span></span>
                        </div>
                        </div>';
                        break;
                    case 'update':
                        $htmlSub .= '<div class="timeline-block">
                        <span class="timeline-step">
                            <i class="ni ni-bell-55 text-warning text-gradient"></i>
                        </span>
                        <div class="timeline-content">';
                        if (json_decode($value->content)->name_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Nama supplier: <b>' . json_decode($value->content)->name_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->name_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->email_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Email: <b>' . json_decode($value->content)->email_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->email_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->phone_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Nomor Telepon: <b>' . json_decode($value->content)->phone_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->phone_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->address_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Alamat: <b>' . json_decode($value->content)->address_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->address_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->shopname_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Nama Toko/Instansi: <b>' . json_decode($value->content)->shopname_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->shopname_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->photo_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Photo: <b>Photo supplier diubah</b></h6>';
                        }
                        $htmlSub .= '<p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . ' - ' . Carbon::parse($value->created_at)->format('d/m/Y H:i') . '</p>
                            <span class="text-xs font-weight-bold mb-0">Diubah oleh: <span class="text-dark text-xs font-weight-bold mb-0">' . User::find($value->created_by)->name . '</span></span>
                        </div>
                        </div>';
                        break;
                    default:
                        break;
                }
            }
            $html .= $htmlSub;
            $html .= '</div>
            <center class="fixed-bottom bottom-0 position-sticky w-100">
            <div class="text-center mt-5 shadow bg-white d-flex justify-content-center" style="width: 40px; height: 40px;border-radius: 50%;align-items: center;
            text-align: center;">
                <a id="loadMore" class="text-dark position-sticky px-3 py-2 text-xs" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Load More" title="Load More" data-page="2">
                    <i class="ni ni-bold-down py-2"> </i>
                </a>

            </div>
            </center>
            </div>
            </div>';
        } else {
            $html = '<div class="row">
            <div class="col-md-12 text-center">
            <img src="' . asset('assets/img/illustrations/rocket-dark.png') . '" width="200" class="rounded">
            <h6 class="text-dark text-sm font-weight-bold mb-0">Tidak ada riwayat</h6>
            </div>
            </div>';
        }
        return [
            'title' => $title,
            'html' => $html,
            'htmlSub' => $htmlSub,
        ];
    }
}
