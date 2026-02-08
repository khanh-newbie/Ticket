<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * API 1: Stats (Cho 4 cái Card ở trên)
     */
    public function stats()
    {
        return response()->json([
            'events' => Event::count(),
            'orders' => Order::count(),
            'organizers' => Organizer::count(),
            'tickets' => TicketType::count(),
        ]);
    }

    /**
     * API 2: Revenue Chart + Order Status (Dùng chung bộ lọc)
     * Nhận params: filter_type, start_date/end_date, start_month/end_month
     */
    public function revenue(Request $request)
    {
        // 1. Tính toán khoảng thời gian
        $range = $this->calculateDateRange($request);
        $startDate = $range['start'];
        $endDate   = $range['end'];
        $groupBy   = $range['group_by'];

        // 2. Lấy dữ liệu Doanh thu (Line Chart)
        $revenueChart = $this->getRevenueData($startDate, $endDate, $groupBy);

        // 3. Lấy dữ liệu Trạng thái đơn hàng (Pie Chart) - [BỔ SUNG CÁI NÀY]
        $orderChart = $this->getOrderStatusData($startDate, $endDate);

        return response()->json([
            'code' => 200,
            'data' => [
                'revenue_chart' => $revenueChart,
                'order_chart'   => $orderChart // Trả về thêm cái này cho Frontend
            ]
        ]);
    }

    // =========================================================================
    // PRIVATE HELPER FUNCTIONS
    // =========================================================================

    private function calculateDateRange(Request $request)
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end   = Carbon::now()->endOfDay();
        $groupBy = 'day';

        if ($request->filter_type === 'month_range' && $request->start_month && $request->end_month) {
            $start = Carbon::parse($request->start_month)->startOfMonth();
            $end   = Carbon::parse($request->end_month)->endOfMonth();
            $groupBy = 'month';
        }
        else if ($request->filter_type === 'date_range' && $request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->endOfDay();
            $groupBy = 'day';
        }

        return ['start' => $start, 'end' => $end, 'group_by' => $groupBy];
    }

    private function getRevenueData($startDate, $endDate, $groupBy)
    {
        $dates  = [];
        $values = [];
        
        if ($groupBy === 'month') {
            $sqlFormat = "%Y-%m"; 
            $step = 'addMonth';       
            $phpFormat = 'Y-m';       
            $displayFormat = 'm/Y';   
            $prefix = "Thg ";         
        } else {
            $sqlFormat = "%Y-%m-%d";
            $phpFormat = 'Y-m-d';
            $displayFormat = 'd/m';   
            $prefix = "";
            $step = 'addDay';
        }

        $rawData = Order::select(
                DB::raw("DATE_FORMAT(created_at, '$sqlFormat') as time_unit"),
                DB::raw('SUM(final_amount) as total')
            )
            ->where('payment_status', 'paid') 
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('time_unit')
            ->pluck('total', 'time_unit')
            ->toArray();

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $key = $current->format($phpFormat);
            $label = $prefix . $current->format($displayFormat);

            $dates[]  = $label;
            $values[] = $rawData[$key] ?? 0;

            $current->$step(); 
        }

        return ['dates' => $dates, 'values' => $values];
    }

    /**
     * [BỔ SUNG] Helper lấy trạng thái đơn hàng
     */
    private function getOrderStatusData($startDate, $endDate)
    {
        $statusCounts = Order::select('payment_status', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->toArray();

        // Map đúng thứ tự màu Frontend: Xanh (Paid), Vàng (Pending), Đỏ (Canceled)
        return [
            $statusCounts['paid'] ?? 0,
            $statusCounts['pending'] ?? 0,
            $statusCounts['canceled'] ?? 0
        ];
    }
}