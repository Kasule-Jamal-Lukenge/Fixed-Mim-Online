<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{

    //total number of categories
    public function totalCategories(){
        $totalCategories = Category::count();

        return response()->json([
            'total_categories' => $totalCategories
        ]);
    }

    //total number of products
    public function totalProducts(){
        $totalProducts = Product::count();

        return response()->json([
            'total_products' => $totalProducts
        ]);
    }

    //  Orders Per Week
    public function ordersPerWeek(){
        $data = Order::select(
            DB::raw('DAYNAME(created_at) as day'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])
        ->groupBy('day')
        ->orderByRaw("FIELD(day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
        ->get();

        return response()->json($data);
    }

    // Orders Per Month
    public function ordersPerMonth(){
        $data = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::RAW('COUNT(*) as total_orders')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json($data);
    }

    //Orders Per Year
    public function ordersPerYear(){
        $data = Order::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total_orders')
        )
        ->groupBy('year')
        ->orderBy('year')
        ->get();


        return response()->json($data);
    }

    //Orders By Status
    public function ordersByStatus(){
        $data = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($data);
    }

    //New Users Per Month
     public function usersPerMonth(){
        $data = User::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total_users')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json($data);
    }

    // Total Sales (Delivered Orders)
    public function totalSales(){
        $totalSales = Order::where('status', 'Delivered')->sum('total_price');

        return response()->json([
            'total_sales' => $totalSales
        ]);
    }

    // Total Sales Per Week
    public function salesPerWeek(){
         $data = Order::select(
            DB::raw('DAYNAME(created_at) as day'),
            DB::raw('SUM(total_price) as total')
        )
        ->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])
        ->groupBy('day')
        ->orderByRaw("FIELD(day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
        ->get();

        return response()->json($data);
    }

    // Total Sales Per Month
    public function salesPerMonth()
    {
        $data = Order::select(
            DB::raw('DAY(created_at) as label'),
            DB::raw('SUM(total_price) as value')
        )
        ->whereMonth('created_at', Carbon::now()->month)
        ->groupBy('label')
        ->orderBy('label')
        ->get();

        return response()->json($data);
    }

    // Total Sales Per Year
    public function salesPerYear()
    {
        $data = Order::select(
            DB::raw('MONTHNAME(created_at) as label'),
            DB::raw('SUM(total_price) as value')
        )
        ->whereYear('created_at', Carbon::now()->year)
        ->groupBy('label')
        ->orderByRaw("FIELD(label, 'January','February','March','April','May','June','July','August','September','October','November','December')")
        ->get();

        return response()->json($data);
    }

    // Combined Summary for Dashboard Cards
    public function summary()
    {
        $totalOrders = Order::count();
        $delivered = Order::where('status', 'Delivered')->count();
        $inDelivery = Order::where('status', 'In-Delivery')->count();
        $received = Order::where('status', 'Received')->count();
        $totalUsers = User::count();
        $totalSales = Order::where('status', 'Delivered')->sum('total_price');
        $totalCategories = Category::count();
        $totalProducts = Product::count();

        return response()->json([
            'total_orders' => $totalOrders,
            'delivered_orders' => $delivered,
            'in_delivery_orders' => $inDelivery,
            'received_orders' => $received,
            'total_users' => $totalUsers,
            'total_sales' => $totalSales,
            'total_categories' => $totalCategories,
            'total_products' => $totalProducts
        ]);
    }
}
