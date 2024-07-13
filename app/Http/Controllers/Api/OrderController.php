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
                ->addColumn('order_code', function ($data) {
                    $html ='';
                    $html .='<span class="badge badge-sm bg-primary">' . $data->order_code . '</span>';
                    return $html;
                })
                ->addColumn('status_payment', function ($data) {
                    $html ='';
                    switch ($data->status_payment) {
                        case 'Down Payment':
                            $color = 'warning';
                            $title = $data->payment_type;
                            break;
                        case 'Paid':
                            $color = 'success';
                            $title = $data->payment_type;
                            break;
                        default:
                            $color = 'danger';
                            $title ='';
                            break;
                    }
                    $html .='<span class="badge badge-sm bg-' . $color . '" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $title . '">' . $data->status_payment . '</span>';
                    return $html;
                })
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
                    return $data->pay ?? 0;
                })
                ->addColumn('due', function ($data) {
                    return $data->due;
                })
                ->addColumn('order_date', function ($data) {
                    return Carbon::parse($data->order_date)->translatedFormat('d/m/Y');
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
                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5"
                    aria-labelledby="dropdownTable" >
                    <li><a class="dropdown-item border-radius-md" href="' . url('transaction/orders/detail/' . $data->id) . '"><i class="fa fa-eye me-2"></i> Detail </a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=edit&id=' . $data->id . '&name=' . $data->order_code . '" data-bs-toggle="modal"
                            data-bs-target="#mdOrder"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=history&id=' . $data->id . '&name=' . $data->order_code . '" data-bs-toggle="modal"
                    data-bs-target="#mdOrder">
                    <i class="fa fa-history me-2"></i> History</a></li>
                </ul>
            </div>';
                    return $option;
                })
                ->rawColumns(['order_code','status_payment', 'created_at', 'action'])
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
    public function store(Request $request)
    {
        $code_order = $request->order_code;
        $date = $request->date;
        $customer_id = $request->customer_id;
        $product_id = $request->product_id; //array
        $selling_price_id = $request->selling_price_id; //array
        $qty = $request->quantity; //array
        $sub_total = $request->sub_total; //array
        $total = $request->total;
        $discount = $request->discount;
        $payment_status = $request->payment_status;
        $payment_type = $request->payment_type;
        $pay = $request->pay;
        $due = $request->due;
        $note = $request->note;
        try {
            DB::beginTransaction();
            DB::table('orders')->insert([
                'customer_id' => $customer_id,
                'order_code' => $code_order,
                'qty' => array_sum($qty),
                'total' => $total,
                'pay' => $pay,
                'due' => $due,
                'discount' => $discount,
                'note' => $note,
                'order_date' => Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d'),
                'order_month' => Carbon::createFromFormat('d/m/Y', $date)->format('M'),
                'order_year' => Carbon::createFromFormat('d/m/Y', $date)->format('Y'),
                'status_payment' => $payment_status,
                'payment_type' => $payment_type
            ]);
            $order_id = DB::getPdo()->lastInsertId();
            for ($i = 0; $i < count($product_id); $i++) {
                DB::table('order_details')->insert([
                    'order_id' => $order_id,
                    'product_id' => $product_id[$i],
                    'selling_price_id' => $selling_price_id[$i],
                    'pro_quantity' => $qty[$i],
                    'sub_total' => $sub_total[$i]
                ]);
                DB::table('products')->where('id', $product_id[$i])->decrement('total_stock', $qty[$i]);
                $stokinsupplier = DB::table('product_suppliers')->where('product_id', $product_id[$i])
                    ->orderBy('id', 'DESC')
                    ->get();
                $res = 0;
                foreach ($stokinsupplier as $stok) {
                    if ($qty[$i] >= $stok->product_qty) {
                        $res = $qty[$i] - $stok->product_qty;
                        DB::table('product_suppliers')->delete($stok->id);
                    } else {
                        if ($res == 0) {
                            $res =  (int)$stok->product_qty - (int)$qty[$i];
                        } else {
                            $res =  (int)$stok->product_qty - $res;
                        }
                        DB::table('product_suppliers')->where('id', $stok->id)->update([
                            'product_qty' => $res
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
               'status' => 'success',
               'message' => 'Order created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
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
            case 'payment-status':
                return self::select2PaymentStatus($request);
                break;
            case 'payment-type':
                return self::select2PaymentType($request);
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
            ->where('products.total_stock', '>', 0)
            ->where('products.product_name', 'like', '%' . $request->q . '%')
            ->orWhere('categories.category_name', 'like', '%' . $request->q . '%')
            ->select('categories.category_name', 'products.*')
            ->get();
        return response()->json($product);
    }
    protected function select2Price($request)
    {
        $price = ProductSellingPrice::where('product_id', $request->id)->where('type', 'like', '%' . $request->q . '%')->get();
        return response()->json($price);
    }
    protected function selectedProductToTable($request)
    {
        $id = $request->id;
        $data = DB::table('products')
            ->join('categories', 'products.category_id', 'categories.id')
            ->join('product_selling_prices', 'products.id', 'product_selling_prices.product_id')
            ->where('product_selling_prices.id', $id)
            ->select('categories.category_name', 'products.*', 'product_selling_prices.id as selling_price_id', 'product_selling_prices.type', 'product_selling_prices.selling_price')
            ->first();
        return response()->json($data);
    }
    protected function select2PaymentStatus($request)
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM orders WHERE Field = 'status_payment'"))[0]->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $list = explode("','", $matches[1]);
        if ($request->has('q')) {
            $q = strtolower($request->input('q'));
            $list = collect($list)->filter(function ($item) use ($q) {
                return strpos(strtolower($item), $q) !== false;
            })->values();
        }
        return response()->json($list);
    }
    protected function select2PaymentType($request)
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM orders WHERE Field = 'payment_type'"))[0]->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $list = explode("','", $matches[1]);
        if ($request->has('q')) {
            $q = strtolower($request->input('q'));
            $list = collect($list)->filter(function ($item) use ($q) {
                return strpos(strtolower($item), $q) !== false;
            })->values();
        }
        return response()->json($list);
    }
    protected function showModalCreate()
    {
        $html = '';
        $html .= '<form id="fm_addOrder">';
        $html .= csrf_field();
        $html .= '<div class="row g-3">
                <div class="col-md-4">
                    <label for="orderCodeField" class="col-form-label">Code Order: <span class="text-danger">*</span></label>
                    <input type="text" name="order_code" id="orderCodeField" class="form-control form-control-sm" style="cursor: not-allowed" data-bs-toggle="tooltip"  title="Auto generate" readonly required>
                </div>
                <div class="col-md-4">
                    <label for="dateField" class="col-form-label">Date: <span class="text-danger">*</span></label>
                    <input type="text" name="date" id="dateField" class="form-control form-control-sm" placeholder="Choose date" readonly required>
                </div>
                <div class="col-md-4">
                    <label for="customerField" class="col-form-label">Customer: <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerField" class="form-control form-control-sm select-customer" style="width: 100%!important" required>
                        <option label="Choose One"></option>
                    </select>
                    <span id="err_customer_id"></span>
                </div>
                <div class="col-md-6">
                    <label for="productField" class="col-form-label">Product:</label>
                    <select name="product_id" id="productField" class="form-control form-control-sm select-product" style="width: 100%!important">
                    </select>
                    <span id="err_product_id"></span>
                </div>
                <div class="col-md-6">
                    <label for="sellingPriceField" class="col-form-label">Price:</label>
                    <select name="selling_price_id" id="sellingPriceField" class="form-control form-control-sm select-price" style="width: 100%!important">
                    </select>
                    <span id="err_selling_price_id"></span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="form-group col-md-12">
                    <label for="tableField" class="col-form-label">Order Table: <span class="text-danger">*</span></label>
                    <div class="table-responsive p-0">
                    <table id="tb_selectedProductOrder" class="table table-striped align-items-center mb-0" style="width: 100%;">
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
                        <tbody>
                            <tr id="notAvail">
                                <td colspan="6"><center class="text-secondary font-weight-bolder">Data not available</center></td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-4">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Discount</span>
                                <span id="totalDiscount">Rp 0,00</span>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total</span>
                                <span id="totalPrice">Rp 0,00</span>
                                <input type="hidden" name="total" id="total_price" value="">
                                <input type="hidden" name="parameters" id="parameters" value="">
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="discountField" class="col-form-label">Discount:</label>
                    <input type="text" name="discount" id="discountField" class="form-control form-control-sm imask" placeholder="0,00">
                    <span id="err_discount"></span>
                </div>
                <div class="col-md-6">
                    <label for="paymentStatusField" class="col-form-label">Payment Status: <span class="text-danger">*</span></label>
                    <select name="payment_status" id="paymentStatusField" class="form-control form-control-sm select-payment-status" style="width: 100%!important" required>
                    </select>
                    <span id="err_payment_status"></span>
                </div>
            </div>
            <div class="row g-3" id="paymentFieldDiv" style="display:none">
                <div class="col-md-4">
                    <label for="paymentTypeField" class="col-form-label w-100">Payment Type: <span class="text-danger">*</span></label>
                    <select name="payment_type" id="paymentTypeField" class="form-control select-payment-type" style="width: 100%!important" required>
                    </select>
                    <span id="err_payment_type"></span>
                </div>
                <div class="col-md-4">
                    <label for="payField" class="col-form-label">Pay: <span class="text-danger">*</span></label>
                    <input type="text" name="pay" id="payField" class="form-control form-control-sm imask" required>
                    <span id="err_pay"></span>
                </div>
                <div class="col-md-4">
                    <label for="dueField" class="col-form-label">Due:</label>
                    <input type="text" name="due" id="dueField" class="form-control form-control-sm" readonly>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label for="noteField" class="col-form-label">Note: (<span class="text-secondary">Optional</span>)</label>
                    <textarea name="note" id="noteField" class="form-control form-control-sm" rows="3"></textarea>
                </div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Order';
        $idForm = 'fm_addOrder';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm,
            'code_order' => self::generateCodeOrder()
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
    private function generateCodeOrder()
    {
        $data = DB::table('orders')->orderBy('id', 'DESC')->first();
        if ($data == null) {
            return 'OR-00000001';
        } else {
            $id = $data->id;
            $id = $id + 1;
            $id = str_pad($id, 8, "0", STR_PAD_LEFT);
            return 'OR-' . $id;
        }
    }
}
