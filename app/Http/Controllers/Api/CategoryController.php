<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

//-----------------index-------------------------------------------
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::all();
            $dataTable = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($data) {
                    return $data->category_name;
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->format('d/m/Y H:i');
                })
                ->addColumn('updated_at', function ($data) {
                    return $data->updated_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($data) {
                    $option = '';
                    $option .= '<div class="dropdown float-lg-end pe-4">
                <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" title="" data-bs-original-title="More Actions" aria-expanded="false">
                    <i class="fa fa-list-ul text-secondary"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-sm-n4 ms-n5"
                    aria-labelledby="dropdownTable">';
                    $option .= '<li><a class="dropdown-item border-radius-md" href="javascript:;" data-bs-toggle="modal"
                            data-bs-target="#mdCategory" data-type="edit" data-id="' . $data->id . '" data-name="' . $data->category_name . '"><i class="fa fa-edit me-2"></i> Edit</a></li>
                    <li><a class="dropdown-item border-radius-md text-danger" href="javascript:;"><i
                                class="fa fa-trash me-2"></i> Delete</a></li>';
                    $option .= '</ul>
                </div>';
                    return $option;
                })
                ->rawColumns([
                    'category_name',
                    'created_at',
                    'updated_at',
                    'action',
                ])
                ->make(true);
            return $dataTable;
        }
        //DB::table('categories')->get();
        // return response()->json($categories);
        return view('pages.categories.index', [
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="' . route('dashboard') . '">Dashboard</a>
                </li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Kategori Produk</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">Kategori Produk</h6>
        </nav>'
        ]);
    }

//------------------Insert/Store------------------------------------
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|unique:categories,category_name|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'warning',
                'message' => $validator->messages(),
            ]);
        }
        $category = new Category;
        $category->category_name = $request->category_name;
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
        ]);
        //query builder
        // $data = array();
        // $data['category_name'] = $request->category_name;
        // DB::table('categories')->insert($data);
    }

    //----------------------------Show(id)-----------------------------------
    public function show($id)
    {
        //query builder
        //$categoru=DB::table('categories')->where('id',$id)->first();
        $category = Category::findorfail($id);
        return response()->json($category);
    }

    //---------------------------Edit/Update---------------------------------
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|unique:categories,category_name|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'warning',
                'message' => $validator->messages(),
            ]);
        }
        $data = array();
        $data['category_name'] = $request->category_name;
        DB::table('categories')->where('id', $request->id)->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
        ]);
    }

    //---------------------------Delete--------------------------------------
    public function destroy($id)
    {
        DB::table('categories')->where('id', $id)->delete();
        // $category=Category::findorfail($id);
        // $category->delete();
    }
    //-------------------------------Ajax Modal-------------------------------------
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
    protected function showModalCreate()
    {
        $html = '';
        $html .= '<form id="fm_addCategory">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group">
                    <label for="categoryField" class="col-form-label">Category Name:</label>
                    <input type="text" class="form-control" name="category_name" id="categoryField" placeholder="e.g. Food/Beverage/Dessert/Snack">
                    <span id="err_category_name"></span>
                </div>
            </div>
            </form>';
        $title = '<i class="fa fa-plus me-2"></i> Create Category';
        $idForm = 'fm_addCategory';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
    protected function showModalEdit($request)
    {
        $html = '';
        $html .= '<form id="fm_editCategory">';
        $html .= csrf_field();
        $html .= '<div class="row">
                <div class="form-group">
                    <label for="categoryField" class="col-form-label">Category Name:</label>
                    <input type="hidden" name="id" value="' . $request->id . '">
                    <input type="text" class="form-control" name="category_name" id="categoryField" value="' . $request->name . '" placeholder="e.g. Food/Beverage/Dessert/Snack">
                    <span id="err_category_name"></span>
                </div>
            </div>
            </form>';
        $title = '<i class="fa fa-edit me-2"></i> Edit Category';
        $idForm = 'fm_editCategory';
        return [
            'title' => $title,
            'html' => $html,
            'idForm' => $idForm
        ];
    }
}
