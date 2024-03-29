<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\ProductSupplier;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSellingPrice;
use App\Models\{Product, Category};
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    //-----------------index------------------------------------------
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
                ->addColumn('buying_date', function ($data) {
                    return Carbon::parse($data->buying_date)->translatedFormat('d/m/Y');
                })
                ->addColumn('total_stock', function ($data) {
                    return $data->total_stock;
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
                    <li><a class="dropdown-item border-radius-md" href="'.url('products/detail/'.$data->id).'"><i class="fa fa-eye me-2"></i> Detail </a></li>
                    <li><a class="dropdown-item border-radius-md" href="javascript:;" data-bs-toggle="modal"
                            data-bs-target="#mdProduct" data-type="edit" data-id="' . $data->id . '" data-name="' . $data->product_name . '"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md" href="javascript:;"><i class="fa fa-history me-2"></i> History</a></li>
                    <li><a class="dropdown-item border-radius-md text-danger" href="javascript:;"><i
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

    //------------------Insert/Store------------------------------------
    public function store(Request $request)
    {
        try {
            $validators = Validator::make($request->all(), [
                'product_name' => 'required|max:255',
                'product_code' => 'required|unique:products|max:255',
                'category_id' => 'required',
                'supplier_id' => 'required',
                'buying_price' => 'required',
                'selling_price' => 'required',
                'buying_date' => 'required',
                'product_quantity' => 'required',
                'image' => 'mimes:jpg,jpeg,png|max:5048',
            ]);
            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validators->errors()]);
            }
            $imgProduct = NULL;
            $supplierId = $request->supplier_id;
            $buyingPrice = $request->buying_price;
            $productQuantity = $request->product_quantity;
            $sellingPriceType = $request->selling_price_type;
            $sellingPrice = $request->selling_price;
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
            $product->buying_date = Carbon::createFromFormat('d/m/Y',$request->buying_date)->format('Y-m-d');
            $product->image =  $imgProduct;
            $product->total_stock = array_sum($productQuantity);
            $product->save();
            $product_id = $product->id;
            for ($i = 0; $i < count($supplierId); $i++) {
                $product_supplier = new ProductSupplier;
                $product_supplier->product_id = $product_id;
                $product_supplier->supplier_id = $supplierId[$i];
                $product_supplier->buying_price = $buyingPrice[$i];
                $product_supplier->product_qty = $productQuantity[$i];
                $product_supplier->save();
            }
            for ($k=0; $k < count($sellingPriceType); $k++) {
                $product_selling = new ProductSellingPrice;
                $product_selling->product_id = $product_id;
                $product_selling->type = $sellingPriceType[$k];
                $product_selling->selling_price = $sellingPrice[$k];
                $product_selling->save();
            }
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
            ]);
        }

    }

    //----------------------------Show(id)-------------------------------------------
    public function show($id)
    {
        $product = DB::table('products')
        ->join('categories', 'products.category_id', 'categories.id')
        ->where('products.id', $id)
        ->select('categories.category_name', 'products.*')->first();
        $productSupplier = DB::table('product_suppliers as ps')
        ->join('suppliers', 'ps.supplier_id', 'suppliers.id')
        ->where('ps.product_id', $id)
        ->select('ps.*', 'suppliers.name as supplier_name')
        ->get();
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

    //---------------------------Edit/Update----------------------------------------
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'product_name' => 'required|max:255',
            'product_code' => 'required|max:255',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'buying_price' => 'required',
            'root' => 'required',
            'selling_price' => 'required',
            'product_quantity' => 'required',
        ]);

        $data = array();
        $data['product_name'] = $request->product_name;
        $data['product_code'] = $request->product_code;
        $data['category_id'] = $request->category_id;
        $data['supplier_id'] = $request->supplier_id;
        $data['root'] = $request->root;
        $data['buying_price'] = $request->buying_price;
        $data['selling_price'] = $request->selling_price;
        $data['buying_date'] = $request->buying_date;
        $data['product_quantity'] = $request->product_quantity;
        $image = $request->newphoto;
        if ($image) {
            $position = strpos($image, ';');
            $sub = substr($image, 0, $position);
            $ext = explode('/', $sub)[1];
            $name = time() . "." . $ext;
            $img = Image::make($image)->resize(240, 200);
            $upload_path = 'backend/product/';
            $image_url = $upload_path . $name;
            $success = $img->save($image_url);
            if ($success) {
                $data['image'] = $image_url;
                $img = DB::table('products')->where('id', $id)->first();
                $image_path = $img->image;
                if ($image_path) {
                    $done = unlink($image_path);
                }
                $user = DB::table('products')->where('id', $id)->update($data);
            }
        } else {
            $oldlogo = $request->image;
            $data['image'] = $oldlogo;
            DB::table('products')->where('id', $id)->update($data);
        }
    }

    //---------------------------Delete--------------------------------------------
    public function destroy($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        $image = $product->image;
        if ($image) {
            unlink($image);
            DB::table('products')->where('id', $id)->delete();
        } else {
            DB::table('products')->where('id', $id)->delete();
        }
    }
    public function ajaxModal(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == 'edit') {
                return self::showModalEdit($request);
            }
            return self::showModalCreate();
        }
        return abort(404);
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
                <div class="form-group col-md-4">
                    <div class="d-flex justify-content-between">
                        <label for="supplierField" class="col-form-label">Supplier Name: <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-primary btn-sm rounded btnAddSupplier" title="Add Supplier"><i class="fas fa-plus"></i></button>
                    </div>
                    <select name="supplier_id[]" id="supplierField" class="form-control form-control-sm select-supplier" required>
                        <option label="Choose One"></option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="product_amountField" class="col-form-label mb-2">Amount: <span class="text-danger">*</span></label>
                    <input type="number" name="product_quantity[]" id="product_amountField" min="1" class="form-control form-control-sm" placeholder="Enter Amount" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="buying_priceField" class="col-form-label mb-2">Buying Price: <span class="text-danger">*</span></label>
                    <input type="number" name="buying_price[]" id="buying_priceField" min="1" class="form-control form-control-sm" placeholder="Enter Buying Price" required>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="product_nameField" class="col-form-label mb-2">Product Name: <span class="text-danger">*</span></label>
                    <input type="text" name="product_name" id="product_nameField" class="form-control form-control-sm" placeholder="Enter Product Name" required>
                    <span id="err_product_name"></span>
                </div>
                <div class="form-group col-md-6">
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
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="buying_dateField" class="col-form-label mb-2">Buying Date: <span class="text-danger">*</span></label>
                    <input type="text" name="buying_date" id="buying_dateField" class="form-control form-control-sm" placeholder="Pick buying date" readonly required>
                    <span id="err_buying_date"></span>
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
    }
}
