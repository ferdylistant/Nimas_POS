<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSellingPrice;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = DB::table('orders')
                ->join('customers', 'orders.customer_id', 'customers.id')
                ->select('customers.name', 'orders.*')
                ->orderBy('orders.id', 'DESC')->get();
            $dataTables = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($data) {
                    return $data->name;
                })
                ->addColumn('qty', function ($data) {
                    return $data->qty;
                })
                ->addColumn('total', function ($data) {
                    return $data->total;
                })
                ->addColumn('pay', function ($data) {
                    return $data->pay;
                })
                ->addColumn('order_date', function ($data) {
                    return $data->order_date;
                })
                ->addColumn('created_at', function ($data) {
                    return Carbon::parse($data->created_at)->translatedFormat('d/m/Y H:i');
                })
                ->addColumn('action', function ($data) {
                    $option = '';
                    $option .= '<div class="dropstart float-lg-end pe-4">
                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" title="" data-bs-original-title="More Actions" aria-expanded="false">
                    <i class="fa fa-list-ul text-secondary  tooltip-wrapper"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5" style="z-index: 999!important;"
                    aria-labelledby="dropdownTable" >
                    <li><a class="dropdown-item border-radius-md" href="' . url('transaction/orders/detail/' . $data->id) . '"><i class="fa fa-eye me-2"></i> Detail </a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=edit&id=' . $data->id . '&name=' . $data->product_name . '" data-bs-toggle="modal"
                            data-bs-target="#mdOrder"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=history&id=' . $data->id . '&name=' . $data->product_name . '" data-bs-toggle="modal"
                    data-bs-target="#mdOrder">
                    <i class="fa fa-history me-2"></i> History</a></li>
                </ul>
            </div>';
                    return $option;
                })
                ->rawColumns(['created_at', 'action'])
                ->make(true);
            return $dataTables;
        }
        return view('pages.order.index', [
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm text-primary active" aria-current="page">Orders</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Orders</h6>
        </nav>'
        ]);
    }
    public function TodayOrder()
    {
        $data = date('d/m/Y');
        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', 'customers.id')
            ->where('orders.order_date', $data)
            ->select('customers.name', 'orders.*')
            ->orderBy('orders.id', 'DESC')->get();
        return response()->json($order);
    }

    public function OrderDetails($id)
    {
        $orders = DB::table('orders')
            ->join('customers', 'orders.customer_id', 'customers.id')
            ->where('orders.id', $id)
            ->select('customers.name', 'customers.phone', 'customers.address', 'orders.*')
            ->first();
        return response()->json($orders);
    }

    public function OrderDetailsAll($id)
    {
        $details = DB::table('order_details')
            ->join('products', 'order_details.product_id', 'products.id')
            ->where('order_details.order_id', $id)
            ->select('products.product_name', 'products.product_code', 'products.image', 'order_details.*')
            ->get();
        return response()->json($details);
    }

    public function SearchOrderDate(Request $request)
    {
        $orderdate = $request->date;
        $newdate = new DateTime($orderdate);
        $done = $newdate->format('d/m/Y');

        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', 'customers.id')
            ->select('customers.name', 'orders.*')
            ->where('orders.order_date', $done)
            ->get();

        return response()->json($order);
    }

    public function SearchMonth(request $request)
    {
        $month = $request->month;
        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', 'customers.id')
            ->select('customers.name', 'orders.*')
            ->where('orders.order_month', $month)
            ->get();

        return response()->json($order);
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
    public function select2(Request $request)
    {
        switch ($request->type) {
            case 'customer':
                return self::select2Customer($request);
                break;
            case 'product':
                return self::select2Product($request);
                break;
            case 'price':
                return self::select2Price($request);
                break;
            case 'get-product-to-table':
                return self::selectedProductToTable($request);
                break;
            default:
                return abort(404);
                break;
        }
    }
    protected function select2Customer($request)
    {
        $customer = Customer::where('name', 'like', '%' . $request->q . '%')->get();
        return response()->json($customer);
    }
    protected function select2Product($request)
    {
        $product = DB::table('products')
        ->join('categories', 'products.category_id', 'categories.id')
        ->where('products.product_name', 'like', '%' . $request->q . '%')
        ->orWhere('categories.category_name', 'like', '%' . $request->q . '%')
        ->select('categories.category_name', 'products.*')
        ->get();
        return response()->json($product);
    }
    protected function select2Price($request)
    {
        $price = ProductSellingPrice::where('product_id',$request->id)->where('type', 'like', '%' . $request->q . '%')->get();
        return response()->json($price);
    }
    protected function selectedProductToTable($request)
    {
        $id = $request->id;
        $data = DB::table('products')
        ->join('categories', 'products.category_id', 'categories.id')
        ->join('product_selling_prices', 'products.id', 'product_selling_prices.product_id')
        ->where('product_selling_prices.id', $id)
        ->select('categories.category_name', 'products.*', 'product_selling_prices.type','product_selling_prices.selling_price')
        ->first();
        return response()->json($data);
    }
    protected function showModalCreate()
    {
        $category = Customer::all();
        $html = '';
        $html .= '<form id="fm_addOrder">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-6">
                    <label for="dateField" class="col-form-label">Date: <span class="text-danger">*</span></label>
                    <input type="text" name="date" id="dateField" class="form-control form-control-sm" placeholder="Pick date" readonly required>
                </div>
                <div class="form-group col-md-6">
                    <label for="customerField" class="col-form-label">Customer: <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerField" class="form-control form-control-sm select-customer" required>
                        <option label="Choose One"></option>
                    </select>
                    <span id="err_customer_id"></span>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="productField" class="col-form-label">Product:</label>
                    <select name="product_id" id="productField" class="form-control form-control-sm select-product">
                    </select>
                    <span id="err_product_id"></span>
                </div>
                <div class="form-group col-md-6">
                    <label for="sellingPriceField" class="col-form-label">Price:</label>
                    <select name="selling_price_id" id="sellingPriceField" class="form-control form-control-sm select-price">
                    </select>
                    <span id="err_selling_price_id"></span>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="table-responsive p-0">
                    <table id="tb_selectedProductOrder" class="table align-items-center mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Net Unit Price</th>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">qty</th>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                                <th scope="col" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Order';
        $idForm = 'fm_addOrder';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
    protected function showModalEdit($request)
    {
        $id = $request->id;
        $product = Product::find($id);
        $category = Category::find($product->category_id);
        $supplier = DB::table('product_suppliers as a')
            ->join('suppliers as b', 'a.supplier_id', '=', 'b.id')
            ->where('a.product_id', $id)->orderBy('a.id', 'ASC')
            ->select(
                'a.*',
                'b.name as supplier_name',
            )->get();
        $sellingPrice = ProductSellingPrice::where('product_id', $id)->get();
        $dataSup = DB::table('product_suppliers as a')
            ->join('suppliers as b', 'a.supplier_id', '=', 'b.id')
            ->where('a.product_id', $id)->orderBy('a.id', 'ASC')
            ->select(
                'a.*',
                'b.name as supplier_name',
            )
            ->get();
        $title = '<i class="fa fa-edit me-2"></i> Edit Product (' . $product->product_name . ')';
        $idForm = 'fm_editProduct';
        $html = '';
        $html .= '<form id="fm_editProduct">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-12">
                    <label for="categoryField" class="col-form-label">Category Name: <span class="text-danger">*</span></label>
                    <input type="hidden" name="id" value="' . $id . '">
                    <select name="category_id" id="categoryField" class="form-control select-category" required>
                        <option label="Choose One"></option>
                    </select>
                    <span id="err_category_id"></span>
                </div>
            </div>
            <div class="row input_fields_wrap">';
        foreach ($dataSup as $key => $value) {
            if ($key == 0) {
                $html .= '<div class="form-group col-md-3" data-sort="1">
                    <div class="d-flex justify-content-between">
                        <label for="supplierField" class="col-form-label">Supplier Name: <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-primary btn-sm rounded btnAddSupplier" title="Add Supplier"><i class="fas fa-plus"></i></button>
                    </div>
                    <select name="supplier_id[]" id="supplierField" class="form-control form-control-sm select-supplier" required>
                        <option label="Choose One"></option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="product_amountField" class="col-form-label mb-2">Amount: <span class="text-danger">*</span></label>
                    <input type="number" name="product_quantity[]" id="product_amountField" min="1" class="form-control form-control-sm" value="' . $value->product_qty . '" placeholder="Enter Amount" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="buying_priceField" class="col-form-label mb-2">Buying Price: <span class="text-danger">*</span></label>
                    <input type="number" name="buying_price[]" id="buying_priceField" min="1" class="form-control form-control-sm" value="' . $value->buying_price . '" placeholder="Enter Buying Price" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="buying_dateField1" class="col-form-label mb-2">Buying Date: <span class="text-danger">*</span></label>
                    <input type="text" name="buying_date[]" id="buying_dateField1" class="form-control form-control-sm buying_date_cls" value="' . Carbon::parse($value->buying_date)->format('d/m/Y') . '" placeholder="Pick buying date" readonly required>
                </div>
                <input type="hidden" name="id_product_supplier[]" value="' . $value->id . '">';
            } else {
                $i = $key + 1;
                $html .= '<div class="row field-more">
                    <div class="form-group col-md-3" data-sort="' . $i . '">
                        <select name="supplier_id[]" id="supplierFieldMore' . $i . '" class="form-control form-control-sm select-supplier" required>
                            <option label="Choose One"></option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <input type="number" name="product_quantity[]" id="product_amountField' . $i . '" min="1" class="form-control form-control-sm" value="' . $value->product_qty . '" placeholder="Enter Amount" required>
                    </div>
                    <div class="form-group col-md-3">
                        <input type="number" name="buying_price[]" id="buying_priceField' . $i . '" min="1" class="form-control form-control-sm" value="' . $value->buying_price . '" placeholder="Enter Buying Price" required>
                    </div>
                    <div class="form-group col-md-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="buying_date[]" id="buying_dateField' . $i . '" class="form-control form-control-sm buying_date_cls" value="' . Carbon::parse($value->buying_date)->format('d/m/Y') . '" placeholder="Pick buying date" readonly required>
                            <div class="input-group-append">
                                <span class="input-group-text"><a href="javascript:void(0)" class="remove_field_supplier text-danger" title="Delete Field"><i class="fas fa-times"></i></a></span>
                            </div>
                        </div>
                    </div>
                    </div>
                    <input type="hidden" name="id_product_supplier[]" value="' . $value->id . '">';
            }
        }
        $html .= '</div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="unit_satuanField" class="col-form-label mb-2">Unit/Satuan: <span class="text-danger">*</span></label>
                    <input type="text" name="unit_satuan" id="unit_satuanField" class="form-control form-control-sm" value="' . $product->unit_satuan . '" placeholder="Enter Unit/Satuan" required>
                    <span id="err_unit_satuan"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="product_nameField" class="col-form-label mb-2">Product Name: <span class="text-danger">*</span></label>
                    <input type="text" name="product_name" id="product_nameField" class="form-control form-control-sm" value="' . $product->product_name . '" placeholder="Enter Product Name" required>
                    <span id="err_product_name"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="kodeField" class="col-form-label">Kode: <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                    <input type="text" name="product_code" id="kodeField" class="form-control form-control-sm" value="' . $product->product_code . '" placeholder="Enter/Scan Product Code" required>
                    <div class="input-group-append">
                        <span class="input-group-text text-primary">
                            <a href="javascript:void(0)" class="btnCheckProductCode" title="Check Product Code"><i class="fas fa-barcode"></i></a>
                        </span>
                    </div>
                    <span id="err_product_code"></span>
                    </div>
                </div>
            </div>
            <div class="row input_fields_wrap_selling">';
        foreach ($sellingPrice as $key => $value) {
            if ($key == 0) {
                $html .= '<div class="form-group col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="selling_price_typeField" class="col-form-label mb-2">Selling Price Type: <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-primary btn-sm rounded btnAddSellingPrice" title="Add Selling Price"><i class="fas fa-plus"></i></button>
                    </div>
                    <input type="text" name="selling_price_type[]" id="selling_price_typeField" class="form-control form-control-sm" value="' . $value->type . '" placeholder="Enter Selling Price Type" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="selling_priceField" class="col-form-label mb-2">Selling Price: <span class="text-danger">*</span></label>
                    <input type="number" name="selling_price[]" id="selling_priceField" min="1" class="form-control form-control-sm" value="' . $value->selling_price . '" placeholder="Enter Selling Price" required>
                </div>
                <input type="hidden" name="id_selling_price[]" value="' . $value->id . '">';
            } else {
                $i = $key + 1;
                $html .= '<div class="row field-more-selling">
                        <div class="form-group col-md-6" data-sortselling="' . $i . '">
                            <input type="text" name="selling_price_type[]" id="selling_price_typeField' . $i . '" class="form-control form-control-sm" value="' . $value->type . '" placeholder="Enter Selling Price Type" required>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="input-group input-group-sm">
                                <input type="number" name="selling_price[]" id="selling_priceField' . $i . '" min="1" class="form-control form-control-sm" value="' . $value->selling_price . '" placeholder="Enter Selling Price" required>
                                <div class="input-group-append">
                                    <span class="input-group-text"><a href="javascript:void(0)" class="remove_field_selling_price text-danger" title="Delete Field"><i class="fas fa-times"></i></a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id_selling_price[]" value="' . $value->id . '">';
            }
        }

        $html .= '</div>
            <div class="row">
                <div id="imgEdit">
                <div class="form-group col-md-12">
                    <label for="imageField" class="col-form-label mb-2">Image: <span class="text-danger">*</span></label>
                    <br>
                    <img src="' . asset('storage/product/img/' . $product->image) . '" width="200" class="img-thumbnail rounded">
                </div>
                <a href="javascript:void(0)" class="text-gradient text-primary btnChangeImg" title="Change Image"><i class="fas fa-pen"></i> Change Image</a>
                </div>
            </div>
            </form>';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm,
            'product' => $product,
            'category' => $category,
            'supplier' => $supplier,
            'dataSup' => $dataSup
        ];
    }
    protected function showModalHistory($request)
    {
        $product_id = $request->id;
        $data = DB::table('product_histories')->where('product_id', $product_id)->orderBy('id', 'ASC')->paginate(2);
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
                        if (json_decode($value->content)->product_name_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Nama produk: <b>' . json_decode($value->content)->product_name_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->product_name_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->product_code_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Kode produk: <b>' . json_decode($value->content)->product_code_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->product_code_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->category_id_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Kategori produk: <b>' . Category::find(json_decode($value->content)->category_id_his)->category_name . '</b>, diubah menjadi: <b>' . Category::find(json_decode($value->content)->category_id_new)->category_name . '</b></h6>';
                        }
                        if (json_decode($value->content)->unit_satuan_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Unit/Satuan: <b>' . json_decode($value->content)->unit_satuan_his . '</b>, diubah menjadi: <b>' . json_decode($value->content)->unit_satuan_new . '</b></h6>';
                        }
                        if (json_decode($value->content)->image_his != null) {
                            $htmlSub .= '<h6 class="text-dark text-sm font-weight-bold mb-0">Gambar produk <b>diubah</b></h6>';
                        }
                        $htmlSub .= '<p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . ' - ' . Carbon::parse($value->created_at)->format('d/m/Y H:i') . '</p>
                            <span class="text-xs font-weight-bold mb-0">Diubah oleh: <span class="text-dark text-xs font-weight-bold mb-0">' . User::find($value->created_by)->name . '</span></span>
                        </div>
                        </div>';
                        break;
                    case 'add_stock':
                        $htmlSub .= '<div class="timeline-block">
                        <span class="timeline-step">
                            <i class="ni ni-basket text-success text-gradient"></i>
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0">' . json_decode($value->content)->text . ' <a href="' . url('products/product-list/detail/' . $value->product_id) . '" class="text-primary text-xs font-weight-bold">Lihat detail</a></h6>
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . ' - ' . Carbon::parse($value->created_at)->format('d/m/Y H:i') . '</p>
                            <span class="text-xs font-weight-bold mb-0">Ditambahkan oleh: <span class="text-dark text-xs font-weight-bold mb-0">' . User::find($value->created_by)->name . '</span></span>
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
