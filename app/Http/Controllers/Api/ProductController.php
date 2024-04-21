<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Supplier;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Http\Request;
use App\Models\ProductSupplier;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSellingPrice;
use App\Models\{Product, Category, User};
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('products')
                ->join('categories', 'products.category_id', 'categories.id')
                ->select('categories.category_name', 'products.*')
                ->orderBy('products.id', 'DESC')
                ->get();
            $dataTables = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    $html = '';
                    $img = asset('storage/product/img/' . $data->image);
                    $html = '<img src="' . $img . '" class="avatar avatar-sm" alt="Image ' . $data->product_name . '"/>';
                    return $html;
                })
                ->addColumn('product_code', function ($data) {
                    return $data->product_code;
                })
                ->addColumn('product_name', function ($data) {
                    return $data->product_name;
                })
                ->addColumn('category_name', function ($data) {
                    return $data->category_name;
                })
                // ->addColumn('buying_date', function ($data) {
                //     return Carbon::parse($data->buying_date)->translatedFormat('d/m/Y');
                // })
                ->addColumn('total_stock', function ($data) {
                    return $data->total_stock;
                })
                ->addColumn('unit_satuan', function ($data) {
                    return $data->unit_satuan;
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
                    <li><a class="dropdown-item border-radius-md" href="' . url('products/product-list/detail/' . $data->id) . '"><i class="fa fa-eye me-2"></i> Detail </a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=addStock&id=' . $data->id . '&name=' . $data->product_name . '" data-bs-toggle="modal"
                            data-bs-target="#mdProduct"><i class="fa fa-plus me-2"></i> Add Stock</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=edit&id=' . $data->id . '&name=' . $data->product_name . '" data-bs-toggle="modal"
                            data-bs-target="#mdProduct"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="?modal=history&id=' . $data->id . '&name=' . $data->product_name . '" data-bs-toggle="modal"
                    data-bs-target="#mdProduct">
                    <i class="fa fa-history me-2"></i> History</a></li>
                    <li><a class="dropdown-item border-radius-md text-danger btnDeleteProduct" href="javascript:;" data-id="' . $data->id . '" data-name="' . $data->product_name . '"><i
                                class="fa fa-trash me-2"></i> Delete</a></li>
                </ul>
            </div>';
                    return $option;
                })
                ->rawColumns(['image', 'created_at', 'updated_at', 'action'])
                ->make(true);
            return $dataTables;
        }
        return view('pages.product.index', [
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm text-primary active" aria-current="page">Produk</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Produk</h6>
        </nav>'
        ]);
    }
    public function store(Request $request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'unit_satuan' => 'required|max:6',
                'product_name' => 'required|max:255',
                'product_code' => 'required|unique:products|max:255',
                'category_id' => 'required',
                'supplier_id.*' => 'required',
                'buying_price.*' => 'required',
                'selling_price.*' => 'required',
                'buying_date.*' => 'required',
                'product_quantity.*' => 'required',
                'image' => 'mimes:jpg,jpeg,png|max:5048',
            ]);
            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validators->errors()
                ]);
            }
            $imgProduct = NULL;
            $supplierId = $request->supplier_id;
            $buyingPrice = $request->buying_price;
            $productQuantity = $request->product_quantity;
            $sellingPriceType = $request->selling_price_type;
            $sellingPrice = $request->selling_price;
            $buyingDate = $request->buying_date;
            $unique = array_unique($supplierId);
            if (count($unique) < count($supplierId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Supplier tidak boleh sama'
                ]);
            }
            if (!is_null($request->file('image'))) {
                $imgProduct = explode('/', $request->file('image')->store('product/img/'));
                $imgProduct = end($imgProduct);
            }
            DB::beginTransaction();
            $product = new Product;
            $product->product_name = $request->product_name;
            $product->product_code = $request->product_code;
            $product->category_id = $request->category_id;
            // $product->buying_date = Carbon::createFromFormat('d/m/Y',$request->buying_date)->format('Y-m-d');
            $product->image =  $imgProduct;
            $product->total_stock = array_sum($productQuantity);
            $product->unit_satuan = $request->unit_satuan;
            $product->save();
            $product_id = $product->id;
            for ($i = 0; $i < count($supplierId); $i++) {
                $product_supplier = new ProductSupplier;
                $product_supplier->product_id = $product_id;
                $product_supplier->supplier_id = $supplierId[$i];
                $product_supplier->buying_price = $buyingPrice[$i];
                $product_supplier->product_qty = $productQuantity[$i];
                $product_supplier->buying_date = Carbon::createFromFormat('d/m/Y', $buyingDate[$i])->format('Y-m-d');
                $product_supplier->save();
            }
            for ($k = 0; $k < count($sellingPriceType); $k++) {
                $product_selling = new ProductSellingPrice;
                $product_selling->product_id = $product_id;
                $product_selling->type = $sellingPriceType[$k];
                $product_selling->selling_price = $sellingPrice[$k];
                $product_selling->save();
            }
            DB::table('product_histories')->insert([
                'product_id' => $product_id,
                'type_history' => 'create',
                'content' => json_encode(['text' => 'Produk (' . $request->product_name . ') dibuat.']),
                'created_by' => auth()->user()->id
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
            ]);
        } catch (\Exception $e) {
            Storage::delete('product/img/' . $imgProduct);
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],$e->getCode());
        }
    }
    public function show($id)
    {
        if (request()->ajax()) {
            if (request()->req == 'get-barcode') {
                return self::getModalBarcode($id);
            } elseif (request()->req == 'get-supplier') {
                return self::getCollapseSupplier(request()->supplierId, $id, request()->unitSatuan);
            }
        }
        $product = DB::table('products')
            ->join('categories', 'products.category_id', 'categories.id')
            ->where('products.id', $id)
            ->select('categories.category_name', 'products.*')
            ->first();
        $productSupplier = DB::table('product_suppliers as ps')
            ->join('suppliers', 'ps.supplier_id', 'suppliers.id')
            ->where('ps.product_id', $id)
            ->orderBy('ps.id', 'ASC')
            ->groupBy('ps.supplier_id')
            ->select(
                'ps.*',
                'suppliers.name as supplier_name',
                DB::raw('COUNT(ps.supplier_id) as supplier_count'),
            )
            ->get();
        // dd($productSupplier);
        $productSelling = DB::table('product_selling_prices')->where('product_id', $id)->get();
        return view('pages.product.detail', [
            'product' => $product,
            'productSupplier' => $productSupplier,
            'productSelling' => $productSelling,
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('product.index') . '">Produk</a>
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
            $id = $request->id;
            // dd($id);
            $validators = Validator::make($request->all(), [
                'unit_satuan' => 'required|max:6',
                'product_name' => 'required|max:255',
                'category_id' => 'required',
                'supplier_id.*' => 'required',
                'buying_price.*' => 'required',
                'selling_price.*' => 'required',
                'buying_date.*' => 'required',
                'product_quantity.*' => 'required',
                'image' => 'mimes:jpg,jpeg,png|max:5048',
            ]);

            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validators->errors()
                ]);
            }
            $imgProduct = DB::table('products')->where('id',$id)->first()->image;
            //Table Supplier
            $idProductSupplier = $request->id_product_supplier;
            $supplierId = $request->supplier_id;
            $buyingPrice = $request->buying_price;
            $productQuantity = $request->product_quantity;
            $buyingDate = $request->buying_date;
            //Table Selling Price
            $id_selling_price = $request->id_selling_price;
            $sellingPriceType = $request->selling_price_type;
            $sellingPrice = $request->selling_price;
            $unique = array_unique($supplierId);
            if (count($unique) < count($supplierId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Supplier tidak boleh sama'
                ]);
            }
            if ($request->has('image')) {
                if ($imgProduct) {
                    Storage::delete('prodcut/img/' . $imgProduct);
                }
                $imgProduct = explode('/', $request->file('image')->store('product/img/'));
                $imgProduct = end($imgProduct);
            }
            DB::beginTransaction();
            DB::table('products')->where('id',$id)->update([
                'product_name' => $request->product_name,
                'product_code' => $request->product_code,
                'category_id' => $request->category_id,
                'image' => $imgProduct,
                'unit_satuan' => $request->unit_satuan,
                'total_stock' => array_sum($productQuantity),
            ]);
            DB::table('product_suppliers')->where('product_id', $id)->delete();
            DB::table('product_selling_prices')->where('product_id', $id)->delete();
            for ($i = 0; $i < count($supplierId); $i++) {
                $product_supplier = new ProductSupplier;
                $product_supplier->product_id = $id;
                $product_supplier->supplier_id = $supplierId[$i];
                $product_supplier->buying_price = $buyingPrice[$i];
                $product_supplier->product_qty = $productQuantity[$i];
                $product_supplier->buying_date = Carbon::createFromFormat('d/m/Y', $buyingDate[$i])->format('Y-m-d');
                $product_supplier->save();
            }
            for ($k = 0; $k < count($sellingPriceType); $k++) {
                $product_selling = new ProductSellingPrice;
                $product_selling->product_id = $id;
                $product_selling->type = $sellingPriceType[$k];
                $product_selling->selling_price = $sellingPrice[$k];
                $product_selling->save();
            }
            // $history = [

            // ];
            DB::table('product_histories')->insert([
                'product_id' => $id,
                'type_history' => 'update',
                'content' => json_encode(['text' => 'Produk (' . $request->product_name . ') diubah.']),
                'created_by' => auth()->user()->id
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully',
            ]);
        } catch (\Exception $e) {
            Storage::delete('product/img/' . $imgProduct);
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],$e->getCode());
        }
    }
    public function destroy($id)
    {
        try {
            $product = DB::table('products')->where('id', $id)->first();
            $image = $product->image;
            Storage::delete('prodcut/img/' .  $image);
            DB::beginTransaction();
            DB::table('products')->where('id', $id)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],$e->getCode());
        }
    }
    protected function getModalBarcode($id)
    {
        try {
            $data = DB::table('products')->where('id', $id)->first();
            $barcode = new DNS1D();
            $html = '';
            $html .= '<div class="row">
            <div class="col-12 text-center">
            ' . $barcode->getBarcodeSVG($data->product_code, "C128", 3, 80, 'black') . '
            </div>
            </div>';
            return response()->json([
                'title' => '<i class="fas fa-barcode"></i> Print Barcode',
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],$e->getCode());
        }
    }
    protected function getCollapseSupplier($supId, $prodId, $unitSatuan)
    {
        $html = '';
        $supData = DB::table('product_suppliers as ps')
            ->join(
                'suppliers',
                'ps.supplier_id',
                'suppliers.id',
            )
            ->where('ps.product_id', $prodId)
            ->where('ps.supplier_id', $supId)
            ->orderBy('ps.id', 'ASC')
            ->select(
                'ps.*',
                'suppliers.name as supplier_name'
            )
            ->get();
        $html .= '<table class="table table-striped">
            <tbody>';
        foreach ($supData as $k => $sd) {
            if ($k != 0) {

                $html .= '<tr>
                <td class="text-dark text-center"><i class="fa fa-genderless text-sm text-gradient text-primary"></i> ' . $sd->supplier_name . '</td>
                <td class="text-dark text-center">' . $sd->product_qty . ' ' . $unitSatuan . '</td>
                <td class="text-dark text-center">Rp. ' . number_format($sd->buying_price) . '</td>
                <td class="text-dark text-center">' . Carbon::parse($sd->buying_date)->translatedFormat('d M Y') . '</td>
                <td class="text-dark text-center">' . Carbon::parse($sd->created_at)->translatedFormat('d M Y') . '</td>
            </tr>';
            }
        }
        $html .= '</tbody></table>';
        return $html;
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
                case 'addStock':
                    return self::showModalAddStock($request);
                    break;
                case 'add-stock-product-action':
                    return self::storeAddStockProduct($request);
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
            case 'category':
                return self::select2Category($request);
                break;
            case 'supplier':
                return self::select2Supplier($request);
                break;
            default:
                return abort(404);
                break;
        }
    }
    protected function select2Category($request)
    {
        $category = Category::where('category_name', 'like', '%' . $request->q . '%')->get();
        return response()->json($category);
    }
    protected function select2Supplier($request)
    {
        $supplier = Supplier::where('name', 'like', '%' . $request->q . '%')->get();
        return response()->json($supplier);
    }
    protected function showModalCreate()
    {
        $category = Category::all();
        $html = '';
        $html .= '<form id="fm_addProduct">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group col-md-12">
                    <label for="categoryField" class="col-form-label">Category Name: <span class="text-danger">*</span></label>
                    <select name="category_id" id="categoryField" class="form-control select-category" required>
                        <option label="Choose One"></option>
                    </select>
                    <span id="err_category_id"></span>
                </div>
            </div>
            <div class="row input_fields_wrap">
                <div class="form-group col-md-3" data-sort="1">
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
                    <input type="number" name="product_quantity[]" id="product_amountField" min="1" class="form-control form-control-sm" placeholder="Enter Amount" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="buying_priceField" class="col-form-label mb-2">Buying Price: <span class="text-danger">*</span></label>
                    <input type="number" name="buying_price[]" id="buying_priceField" min="1" class="form-control form-control-sm" placeholder="Enter Buying Price" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="buying_dateField" class="col-form-label mb-2">Buying Date: <span class="text-danger">*</span></label>
                    <input type="text" name="buying_date[]" id="buying_dateField" class="form-control form-control-sm buying_date_cls" placeholder="Pick buying date" readonly required>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="unit_satuanField" class="col-form-label mb-2">Unit/Satuan: <span class="text-danger">*</span></label>
                    <input type="text" name="unit_satuan" id="unit_satuanField" class="form-control form-control-sm" placeholder="Enter Unit/Satuan" required>
                    <span id="err_unit_satuan"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="product_nameField" class="col-form-label mb-2">Product Name: <span class="text-danger">*</span></label>
                    <input type="text" name="product_name" id="product_nameField" class="form-control form-control-sm" placeholder="Enter Product Name" required>
                    <span id="err_product_name"></span>
                </div>
                <div class="form-group col-md-4">
                    <label for="kodeField" class="col-form-label">Kode: <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                    <input type="text" name="product_code" id="kodeField" class="form-control form-control-sm" placeholder="Enter/Scan Product Code" required>
                    <div class="input-group-append">
                        <span class="input-group-text text-primary">
                            <a href="javascript:void(0)" class="btnCheckProductCode" title="Check Product Code"><i class="fas fa-barcode"></i></a>
                        </span>
                    </div>
                    <span id="err_product_code"></span>
                    </div>
                </div>
            </div>
            <div class="row input_fields_wrap_selling">
                <div class="form-group col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="selling_price_typeField" class="col-form-label mb-2">Selling Price Type: <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-primary btn-sm rounded btnAddSellingPrice" title="Add Selling Price"><i class="fas fa-plus"></i></button>
                    </div>
                    <input type="text" name="selling_price_type[]" id="selling_price_typeField" class="form-control form-control-sm" placeholder="Enter Selling Price Type" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="selling_priceField" class="col-form-label mb-2">Selling Price: <span class="text-danger">*</span></label>
                    <input type="number" name="selling_price[]" id="selling_priceField" min="1" class="form-control form-control-sm" placeholder="Enter Selling Price" required>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="imageField" class="col-form-label mb-2">Image: <span class="text-danger">*</span></label>
                    <input type="file" name="image" id="imageField" onchange="onFileSelected(event)" class="form-control form-control-sm" required>
                    <span id="err_image"></span>
                </div>
                <div id="image_preview"></div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Product';
        $idForm = 'fm_addProduct';
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
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . '</p>
                            <span class="text-xs font-weight-bold mb-0">Dibuat oleh: <span class="text-dark text-xs font-weight-bold mb-0">' . User::find($value->created_by)->name . '</span></span>
                        </div>
                        </div>';
                        break;
                    case 'update':

                        break;
                    case 'add_stock':
                        $htmlSub .= '<div class="timeline-block">
                        <span class="timeline-step">
                            <i class="ni ni-basket text-success text-gradient"></i>
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0">' . json_decode($value->content)->text . ' <a href="' . url('products/product-list/detail/' . $value->product_id) . '" class="text-primary text-xs font-weight-bold">Lihat detail</a></h6>
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">' . Carbon::parse($value->created_at)->diffForHumans() . '</p>
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
    protected function showModalAddStock($request)
    {
        $id = $request->id;
        $name = $request->name;
        $prod = Product::find($id);
        $data = DB::table('product_suppliers as a')
            ->join('suppliers as b', 'a.supplier_id', '=', 'b.id')
            ->where('a.product_id', $id)->orderBy('a.id', 'ASC')
            ->select(
                'a.*',
                'b.name as supplier_name',
            )
            ->get();
        $title = '<i class="fa fa-plus me-2"></i> Add Stock (' . $name . ')';
        $html = '';
        $html = '<div class="row">
        <h6>Riwayat</h6>
        <div class="col-md-12 table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Supplier Name</th>
                        <th class="text-center">Buying Price</th>
                        <th class="text-center">Buying Date</th>
                        <th class="text-center">Created At</th>
                        <th class="text-center">Quantity</th>

                    </tr>
                </thead>
                <tbody>';
        foreach ($data as $key => $value) {
            $html .= '<tr>
                        <td class="text-center">' . $value->supplier_name . '</td>
                        <td class="text-center">Rp.' . number_format($value->buying_price) . '</td>
                        <td class="text-center">' . Carbon::parse($value->buying_date)->format('d F Y') . '</td>
                        <td class="text-center">' . Carbon::parse($value->created_at)->format('d F Y') . '</td>
                        <td class="text-center">' . $value->product_qty . ' ' . $prod->unit_satuan . '</td>
                    </tr>';
        }
        $html .= '</tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-bold text-end text-uppercase">Total:</td>
                    <td class="text-bold text-center">' . $prod->total_stock . ' ' . $prod->unit_satuan . '</td>
                </tr>
            </tfoot>
            </table>
        </div>
        </div>';
        $html .= '<form id="fm_addStockProduct">
        <input type="hidden" name="product_id" value="' . $id . '">
        <input type="hidden" name="product_name" value="' . $prod->product_name . '">
        <input type="hidden" name="unit_satuan" value="' . $prod->unit_satuan . '">
        <div class="row input_fields_wrap">
        <div class="form-group col-md-3">
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
            <input type="number" name="product_quantity[]" id="product_amountField" min="1" class="form-control form-control-sm" placeholder="Enter Amount" required>
        </div>
        <div class="form-group col-md-3">
            <label for="buying_priceField" class="col-form-label mb-2">Buying Price: <span class="text-danger">*</span></label>
            <input type="number" name="buying_price[]" id="buying_priceField" min="1" class="form-control form-control-sm" placeholder="Enter Buying Price" required>
        </div>
        <div class="form-group col-md-3">
            <label for="buying_dateField" class="col-form-label mb-2">Buying Date: <span class="text-danger">*</span></label>
            <input type="text" name="buying_date[]" id="buying_dateField" class="form-control form-control-sm" placeholder="Pick buying date" readonly required>
        </div>
        </div>
        </form>';
        $idForm = 'fm_addStockProduct';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
    protected function storeAddStockProduct($request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'supplier_id.*' => 'required',
                'buying_price.*' => 'required',
                'buying_date.*' => 'required',
                'product_quantity.*' => 'required',
            ]);
            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validators->errors()
                ]);
            }
            $supplierId = $request->supplier_id;
            $buyingPrice = $request->buying_price;
            $productQuantity = $request->product_quantity;
            $buyingDate = $request->buying_date;
            $unique = array_unique($supplierId);
            if (count($unique) < count($supplierId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Supplier tidak boleh sama'
                ]);
            }
            DB::beginTransaction();
            for ($i = 0; $i < count($supplierId); $i++) {
                $product_supplier = new ProductSupplier;
                $product_supplier->product_id = $request->product_id;
                $product_supplier->supplier_id = $supplierId[$i];
                $product_supplier->buying_price = $buyingPrice[$i];
                $product_supplier->product_qty = $productQuantity[$i];
                $product_supplier->buying_date = Carbon::createFromFormat('d/m/Y', $buyingDate[$i])->format('Y-m-d');
                $product_supplier->save();
            }
            DB::table('products')->where('id', $request->product_id)->increment('total_stock', array_sum($productQuantity));
            DB::table('product_histories')->insert([
                'product_id' => $request->product_id,
                'type_history' => 'add_stock',
                'content' => json_encode([
                    'text' => 'Jumlah stok (' . $request->product_name . ') ditambahkan sebesar ' . array_sum($productQuantity) . '' . $request->unit_satuan . '.',
                ]),
                'created_by' => auth()->user()->id,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product stock successfully added'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return abort($e->getCode(), $e->getMessage());
        }
    }
}
