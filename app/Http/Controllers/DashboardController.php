<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    public function ajaxChart(Request $request)
    {
        $func = $request->type.'Chart';
        return self::$func();
    }
    protected function barChart()
    {
        $currentYear = date('Y');
        $data = DB::table('orders')
        ->where('order_year',$currentYear)
        ->groupBy('order_month')
        ->orderBy('id','asc')
        ->select('order_month',DB::raw(
            'IFNULL(SUM(total),0) as total'
        ),DB::raw(
            'IFNULL(count(id),0) as count_sales'
        ))->get();
        $month = collect($data->toArray())->map(function($item) {
            return $item->order_month;
        })->all();
        $sales = collect($data->toArray())->map(function($item) {
            return $item->count_sales;
        })->all();
        //Customer Percentage
        $cust = DB::table('orders')
        ->where('order_year',$currentYear)
        ->groupBy('customer_id')->get()->count();
        $totalCustomer = Customer::count();
        $percentCust = ($cust / $totalCustomer) * 100;
        //Total Sales per year
        $totalSales = DB::table('orders')->where('order_year',$currentYear)
        ->sum('total');
        $totalSales = Self::getAmount($totalSales);
        //Total Product Sales
        $prod = DB::table('order_details as od')
        ->leftJoin('orders as o','o.id','=','od.order_id')
        ->where('o.order_year',$currentYear)
        ->groupBy('od.product_id')->get()->count();
        $totalProduct = Product::count();
        $percentProd = ($prod / $totalProduct) * 100;
        return response()->json([
            'month' => $month,
            'sales' => $sales,
            'percent_customer' => $percentCust,
            'customer_sales' => $cust,
            'total_sales' => $totalSales,
            'percent_product' => $percentProd,
            'product_sales' => $prod,
        ]);
    }
    protected function lineChart()
    {
        $currentYear = date('Y');
        $totalProduct = DB::table('order_details as od')
        ->leftJoin('orders as o','o.id','=','od.order_id')
        ->leftJoin('products as p','p.id','=','od.product_id')
        ->where('o.order_year',$currentYear)
        ->groupBy('od.product_id')
        ->orderBy('od.order_id','asc')
        ->select('od.product_id','p.product_name')->get();
        $totalProductByMonth = DB::table('order_details as od')
        ->leftJoin('orders as o','o.id','=','od.order_id')
        ->where('o.order_year',$currentYear)
        ->groupByRaw('od.product_id,o.order_date')
        ->orderBy('o.order_date','asc')
        ->select('od.product_id','o.order_month',DB::raw(
            'IFNULL(SUM(od.pro_quantity),0) as qty'
        ))->get();

        $coll = collect($totalProductByMonth->toArray())->groupBy('order_month')->all();
        foreach ($coll as $key => $value) {
            $has[$key] = collect($value)->groupBy('product_id')->all();
        }
        //SUM
        foreach ($has as $key => $value) {
            foreach ($value as $k => $v) {
                $filtered[$key][$k] = collect($v)->sum('qty');
            }
        }
        $quantity = [];
        foreach($filtered as $key => $value) {
            foreach ($value as $k => $v) {
                $dataset[$k] = $value;
                // foreach ($totalProduct as $i => $item) {
                //     foreach ($month as $s => $m) {
                //         $coba[] = $dataset[$m][$item->product_id];
                //         if ($m == $key) {
                //             // array_push($quantity, [
                //             //     $k => $dataset[$m][$item->product_id]
                //             // ]);
                //             if ($item->product_id == $k) {
                //                 $res[] = [
                //                     'label' => $item->product_name,
                //                     'tension' => 0.4,
                //                     'borderWidth' => 0,
                //                     'pointRadius' => 0,
                //                     'borderColor' => $this->randomHexColor(),
                //                     'borderWidth' => 3,
                //                     'fill' => true,
                //                     'data' => $dataset[$m][$item->product_id],
                //                     'maxBarThickness' => 6
                //                 ];
                //             }
                //         }
                //     }
                // }
            }
        }
        // foreach ($dataset as $key => $value) {
        //     $quantity[] = $key;
        //     foreach ($totalProduct as $i => $item) {
        //         if ($item->product_id == $key) {
        //             $res[] = [
        //                 'label' => $item->product_name,
        //                 'tension' => 0.4,
        //                 'borderWidth' => 0,
        //                 'pointRadius' => 0,
        //                 'borderColor' => $this->randomHexColor(),
        //                 'borderWidth' => 3,
        //                 'fill' => true,
        //                 'data' => $value,
        //                 'maxBarThickness' => 6
        //             ];
        //         }
        //     }
        // }
        dd($dataset);
        $data = [
            'tot_product' => $filtered,
            'by_month' => $totalProductByMonth
        ];
        return $data;
    }
    protected function randomHexColor() {
        $r = $this->randomRgbColor();

        $padR = Str::padLeft($r,2,'0');

        return "#".$padR;
    }
    protected function randomInteger($max) {
        return floor(rand(0,1000)*($max + 1));
    }

    protected function randomRgbColor() {
        $random = $this->randomInteger(255);
        return $random;
    }
}
