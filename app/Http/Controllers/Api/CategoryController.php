<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

//-----------------index-------------------------------------------
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories= Category::all();
            $dataTable = DataTables::of($categories)->addIndexColumn()
            ->make(true);
            return $dataTable;
        }
        //DB::table('categories')->get();
        // return response()->json($categories);
        return view('pages.categories.index',[
            'breadcrumb' => '<nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="'.route('dashboard').'">Dashboard</a>
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
        $validatedData = $request->validate([
            'category_name' => 'required|unique:categories|max:255',
        ]);

        $category = new Category;
        $category->category_name = $request->category_name;
        $category->save();

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
        $category=Category::findorfail($id);
        return response()->json($category);
    }

//---------------------------Edit/Update---------------------------------
    public function update(Request $request, $id)
    {
        $data = array();
        $data['category_name']=$request->category_name;
        DB::table('categories')->where('id',$id)->update($data);
        // $category=Category::findorfail($id);
        // $category->save();
    }

//---------------------------Delete--------------------------------------
    public function destroy($id)
    {
        DB::table('categories')->where('id',$id)->delete();
        // $category=Category::findorfail($id);
        // $category->delete();
    }
}
