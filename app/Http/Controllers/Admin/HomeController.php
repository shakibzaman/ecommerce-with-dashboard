<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $statusValues = [1, 2, 3, 4];
        $todayDate = Carbon::today()->toDateString();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $today = date("Y-m-d");
        $month = date("F");
        $year = date("Y");

        $activesalesData = Order::whereIn('status_id', $statusValues)
            ->selectRaw('
        COUNT(CASE WHEN status_id = 1 AND DATE(created_at) = ? THEN 1 END) as pending_today_count,
        SUM(CASE WHEN status_id = 1 AND DATE(created_at) = ? THEN total END) as pending_today_total,
        COUNT(CASE WHEN status_id = 1 AND MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as pending_month_count,
        SUM(CASE WHEN status_id = 1 AND MONTH(created_at) = ? AND YEAR(created_at) = ? THEN total END) as pending_month_total,

        COUNT(CASE WHEN status_id = 2 AND DATE(created_at) = ? THEN 1 END) as packaging_today_count,
        SUM(CASE WHEN status_id = 2 AND DATE(created_at) = ? THEN total END) as packaging_today_total,
        COUNT(CASE WHEN status_id = 2 AND MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as packaging_month_count,
        SUM(CASE WHEN status_id = 2 AND MONTH(created_at) = ? AND YEAR(created_at) = ? THEN total END) as packaging_month_total,

        COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_count,
        SUM(CASE WHEN DATE(created_at) = ? THEN total END) as today_total,
        COUNT(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as month_count,
        SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN total END) as month_total
    ', [
                // Pending data (status_id = 1)
                $todayDate,
                $todayDate,
                $currentMonth,
                $currentYear,
                $currentMonth,
                $currentYear,
                // Packaging data (status_id = 2)
                $todayDate,
                $todayDate,
                $currentMonth,
                $currentYear,
                $currentMonth,
                $currentYear,
                // General today and month data (any status_id)
                $todayDate,
                $todayDate,
                $currentMonth,
                $currentYear,
                $currentMonth,
                $currentYear
            ])
            ->first();

        // return $month;
        // Pie Chart 
        $todayDate = Carbon::today();
        $order_id_serial = [1, 2, 3, 4, 5, 6];

        $monthly_online_order = DB::table('orders')
            ->select(DB::raw('SUM(payable_amount) as total'), 'status_id', DB::raw('COUNT(*) as quantity'))
            ->whereMonth('created_at', $todayDate->month)
            ->whereYear('created_at', $todayDate->year)
            ->whereIn('status_id', $order_id_serial)  // Optional filter based on order_id_serial
            ->groupBy('status_id')
            ->get()
            ->keyBy('status_id');
        $status = DB::table('statuses')->get()->keyBy('id');
        $user = Auth::user()->load('roles');

        return view('admin.dashboard', compact('user', 'activesalesData', 'monthly_online_order', 'status'));
    }

    // app/Http/Controllers/DatabaseExportController.php



}
