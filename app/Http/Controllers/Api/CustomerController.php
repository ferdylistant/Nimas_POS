<?php

namespace App\Http\Controllers\Api;

use Image;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{

    //-----------------index----------------------------------------------------
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('customers')->orderBy('name', 'ASC')->get();
            $dataTables = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('photo', function ($data) {
                    $html = '';
                    $img = asset('storage/customer/img/' . $data->photo);
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
                    <li><a class="dropdown-item border-radius-md" href="?modal=edit&id=' . $data->id . '&name=' . $data->name . '" data-bs-toggle="modal"
                            data-bs-target="#mdCustomer"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=history&id=' . $data->id . '&name=' . $data->name . '" data-bs-toggle="modal"
                    data-bs-target="#mdCustomer">
                    <i class="fa fa-history me-2"></i> History</a></li>
                    <li><a class="dropdown-item border-radius-md text-danger btnDeleteCustomer" href="javascript:;" data-id="' . $data->id . '" data-name="' . $data->name . '"><i
                                class="fa fa-trash me-2"></i> Delete</a></li>
                </ul>
            </div>';
                    return $option;
                })
                ->rawColumns(['photo', 'created_at', 'updated_at', 'action'])
                ->make(true);
            return $dataTables;
        }
        return view('pages.customer.index', [
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm text-primary active" aria-current="page">Customer</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Customer</h6>
        </nav>'
        ]);
    }

    //------------------Insert/Store--------------------------------------------
    public function store(Request $request)
    {
        try {
            $imgCustomer = 'default.jpg';
            if ($request->hasFile('photo')) {
                $imgCustomer = explode('/', $request->file('photo')->store('customer/img/'));
                $imgCustomer = end($imgCustomer);
            }
            DB::beginTransaction();
            $customer = new Customer;
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->address = $request->address;
            $customer->photo = $imgCustomer;
            $customer->save();
            $customer_id = $customer->id;

            DB::table('customer_histories')->insert([
                'customer_id' => $customer_id,
                'type_history' => 'create',
                'content' =>  json_encode(['text' => 'Customer (' . $request->name . ') dibuat.']),
                'created_by' => auth()->user()->id
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully.'
            ]);
        } catch (\Exception $e) {
            if ($imgCustomer != 'default.jpg') {
                Storage::delete('customer/img/' . $imgCustomer);
            }
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }


    //----------------------------Show(id)-------------------------------------------
    public function show($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();
        return response()->json($customer);
    }

    //---------------------------Edit/Update----------------------------------------
    public function update(Request $request)
    {
        try {
            $data = DB::table('customers')->where('id',$request->id)->first();
            $imgCustomer = $data->photo;
            if ($request->has('photo')) {
                if ($request->hasFile('photo')) {
                    if ($imgCustomer != 'default.jpg') {
                        Storage::delete('customer/img/' . $imgCustomer);
                    }
                    $imgCustomer = explode('/', $request->file('photo')->store('customer/img/'));
                    $imgCustomer = end($imgCustomer);
                }
            }
            DB::beginTransaction();
            $update = DB::table('customers')->where('id', $request->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $imgCustomer
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
                    'photo_his' => $imgCustomer === $data->photo ? NULL : $data->photo,
                    'photo_new' => $imgCustomer === $data->photo ? NULL : $imgCustomer
                ];
                DB::table('customer_histories')->insert([
                    'customer_id' => $request->id,
                    'type_history' => 'update',
                    'content' =>  json_encode($content),
                    'created_by' => auth()->user()->id
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully.'
            ]);
        } catch (\Exception $e) {
            if ($imgCustomer != 'default.jpg') {
                Storage::delete('customer/img/' . $imgCustomer);
            }
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    //---------------------------Delete--------------------------------------------
    public function destroy($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();
        $photo = $customer->photo;
        if ($photo) {
            unlink($photo);
            DB::table('customers')->where('id', $id)->delete();
        } else {
            DB::table('customers')->where('id', $id)->delete();
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
        $html .= '<form id="fm_addCustomer">';
        $html .= csrf_field();
        $html .= '
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="nameField" class="col-form-label">Customer Name: <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="nameField" class="form-control form-control-sm" placeholder="Enter Customer Name" required>
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
                <div class="form-group col-md-12">
                    <label for="addressField" class="col-form-label">Address: <span class="text-danger">*</span></label>
                    <textarea name="address" id="addressField" class="form-control form-control-sm" placeholder="Enter Address" required></textarea>
                    <span id="err_address"></span>
                </div>
                <div class="form-group col-md-12">
                    <label for="photoField" class="col-form-label">Photo: <small class="text-secondary">(Opsional)</small></label>
                    <input type="file" name="photo" id="photoField" onchange="onFileSelected(event)" class="form-control form-control-sm">
                    <span id="err_photo"></span>
                </div>
                <div id="image_preview"></div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Customer';
        $idForm = 'fm_addCustomer';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
    protected function showModalEdit($request)
    {
        $data = Customer::find($request->id);
        $html = '';
        $html .= '<form id="fm_editCustomer">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-4">
                    <label for="nameField" class="col-form-label">Customer Name: <span class="text-danger">*</span></label>
                    <input type="hidden" name="id" value="' . $data->id . '">
                    <input type="text" name="name" id="nameField" class="form-control form-control-sm" value="' . $data->name . '" placeholder="Enter Customer Name" required>
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
                <div class="form-group col-md-12">
                    <label for="addressField" class="col-form-label">Address: <span class="text-danger">*</span></label>
                    <textarea name="address" id="addressField" class="form-control form-control-sm" placeholder="Enter Address" required>' . $data->address . '</textarea>
                    <span id="err_address"></span>
                </div>
            <div id="imgEdit">
                <div class="form-group col-md-12">
                    <label for="imageField" class="col-form-label">Photo: <small class="text-secondary">(Opsional)</small></label>
                    <br>
                    <img src="' . asset('storage/customer/img/' . $data->photo) . '" width="200" class="img-thumbnail rounded">
                </div>
                <a href="javascript:void(0)" class="text-gradient text-primary btnChangeImg" title="Change Image"><i class="fas fa-pen"></i> Change Image</a>
                </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Edit Customer';
        $idForm = 'fm_editCustomer';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm,
            'data' => $data
        ];
    }
    protected function showModalHistory($request)
    {
        $customer_id = $request->id;
        $data = DB::table('customer_histories')->where('customer_id', $customer_id)->orderBy('id', 'ASC')->paginate(2);
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
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Nama customer: <b>' . json_decode($value->content)->name_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->name_new . '</b></h6>';
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
                        if (json_decode($value->content)->photo_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Photo: <b>Photo customer diubah</b></h6>';
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
