<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $category = DB::table('categories')->count();
        $product = DB::table('products')->count();
        $supplier = DB::table('suppliers')->count();
        $customer = DB::table('customers')->count();
        return view('pages.dashboard',[
            'breadcrumb' => '<nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                    </li>
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
                </ol>
                <h6 class="font-weight-bolder mb-0">Dashboard</h6>
            </nav>',
            'category' => self::getAmount($category),
            'product' => self::getAmount($product),
            'supplier' => self::getAmount($supplier),
            'customer' => self::getAmount($customer)
        ]);
    }
    protected function getAmount($input)
    {
        $input = number_format($input);
        $input_count = substr_count($input, ',');
        if ($input_count != '0') {
            if ($input_count == '1') {
                return substr($input, 0, -4) . 'k';
            } else if ($input_count == '2') {
                return substr($input, 0, -8) . 'm';
            } else if ($input_count == '3') {
                return substr($input, 0, -12) . 'b';
            } else {
                return;
            }
        } else {
            return $input;
        }
    }
}
