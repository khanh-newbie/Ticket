<?php

namespace App\Http\Controllers\Customers;

use App\Helpers\ResponseApi;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{

    private $responseApi;
    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }
    /* 1 nhạc sống
    2 thể thao
    3 sân khấu nghệ thuật
    4 khác
    */

    private function baseEventIndex(Request $request, $categoryId)
    {
        return Event::with(['schedules', 'venue'])
            ->where('status', Event::STATUS_PUBLISHED)
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('event_categories.id', $categoryId);
            })
            // giá thấp nhất của ticket
            ->withMin('ticketTypes', 'base_price')
            // ngày bắt đầu gần nhất trong các schedules
            ->withMin('schedules', 'start_datetime')
            ->whereHas('schedules', function ($q) {
                $q->where('end_datetime', '>=', now());
            })

            // sắp xếp theo ngày gần nhất
            ->orderBy('schedules_min_start_datetime', 'asc')
            // giới hạn 4 cái
            // ->limit(4)
            ->get();
    }

    public function musicEventsIndex(Request $request)
    {
        // Logic to retrieve and return music events
        $musicEvents = $this->baseEventIndex($request, 1);
        return $this->responseApi->success($musicEvents);
    }
    public function artEventsIndex(Request $request)
    {
        // Logic to retrieve and return art events
        $artEvents = $this->baseEventIndex($request, 3);
        return $this->responseApi->success($artEvents);
    }
    public function otherEventsIndex(Request $request)
    {
        // Logic to retrieve and return other events
        $otherEvents = $this->baseEventIndex($request, 4);
        return $this->responseApi->success($otherEvents);
    }

    private function eventsByRange($start, $end, $categoryId = null, $limit = 4)
    {
        return Event::with(['schedules', 'venue'])
            ->where('status', Event::STATUS_PUBLISHED)
            // lọc category nếu truyền vào
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('event_categories.id', $categoryId);
                });
            })

            // giá thấp nhất
            ->withMin('ticketTypes', 'base_price')

            // ngày diễn ra gần nhất
            ->withMin('schedules', 'start_datetime')

            // lọc theo khoảng thời gian mong muốn
            ->havingBetween('schedules_min_start_datetime', [$start, $end])

            // sort theo thời gian diễn ra
            ->orderBy('schedules_min_start_datetime', 'asc')

            // số lượng event muốn lấy
            ->limit($limit)

            ->get();
    }

    public function weekendEventsIndex(Request $request)
    {
        // Lấy thứ 7 & CN của "tuần hiện tại"
        // startOfWeek() mặc định là thứ 2
        $startOfWeek = now()->startOfWeek(); // Monday

        $startDate = $startOfWeek->copy()->addDays(5); // Saturday
        $startDate = $startDate->format('Y-m-d 00:00:00');
        $endDate   = $startOfWeek->copy()->addDays(6); // Sunday
        $endDate   = $endDate->format('Y-m-d 23:59:59');


        $limit  = 10;
        $events = $this->eventsByRange($startDate, $endDate, null, $limit);

        return $this->responseApi->success($events);
    }

    public function endOfMonthEventsIndex(Request $request)
    {
        // Từ hôm nay đến cuối tháng
        $startDate = now();
        $endDate   = now()->endOfMonth();

        $limit  = 7;
        $events = $this->eventsByRange($startDate, $endDate, null, $limit);

        return $this->responseApi->success($events);
    }

    public function trendingEventsIndex()
    {
        $from = now()->subDays(30);
        $to   = now();

        $events = Event::query()
            ->select(
                'events.*',
                DB::raw('SUM(order_items.quantity) as tickets_sold'),
                DB::raw('AVG(reviews.rating) as avg_rating')
            )
            ->join('event_schedules', 'event_schedules.event_id', '=', 'events.id')
            ->join('ticket_types', 'ticket_types.schedule_id', '=', 'event_schedules.id')
            ->join('order_items', 'order_items.ticket_type_id', '=', 'ticket_types.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('reviews', 'reviews.event_id', '=', 'events.id')
            ->where('events.status', Event::STATUS_PUBLISHED) 
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')  // sẽ format lại =))
            ->where('event_schedules.end_datetime', '>=', now())
            ->groupBy('events.id')
            ->orderByDesc('tickets_sold')
            ->limit(10)
            ->get();

        return $this->responseApi->success($events);
    }

    public function search(Request $request)
    {
        $keyword = $request->query('q');

        if (!$keyword) {
            return $this->responseApi->BadRequest('Missing search keyword');
        }

        $events = Event::with(['schedules', 'venue'])
            ->where('events.status', Event::STATUS_PUBLISHED) 
            ->where(function ($query) use ($keyword) {
                $query->where('events.event_name', 'like', "%{$keyword}%")
                    ->orWhere('events.description', 'like', "%{$keyword}%")
                    ->orWhereHas('venue', function ($q) use ($keyword) {
                        $q->where('venues.name', 'like', "%{$keyword}%");
                    });
            })
            ->withMin('ticketTypes', 'base_price')
            ->withMin('schedules', 'start_datetime')
            ->having('schedules_min_start_datetime', '>', now())
            ->orderBy('schedules_min_start_datetime', 'asc')
            ->limit(20)
            ->get();

        if ($events->isEmpty()) {
            return $this->responseApi->dataNotfound();
        }

        return $this->responseApi->success($events);
    }

    public function getCategories() {
        $categories = DB::table('event_categories')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return $this->responseApi->success($categories);
    } 

}
