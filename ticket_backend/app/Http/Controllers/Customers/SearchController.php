<?php

namespace App\Http\Controllers\Customers;

use App\Helpers\ResponseApi;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $responseApi;
    
    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function searchEvents(Request $request)
    {
        // 1. Nhận tham số
        $q     = $request->query('q');
        $from  = $request->query('from');
        $to    = $request->query('to');
        $local = $request->query('local'); 
        $price = $request->query('price'); 
        $cate  = $request->query('cate');
        
        // [QUAN TRỌNG] Xử lý phân trang
        $limit  = (int) $request->input('limit', 12); // Mặc định 12
        $page   = (int) $request->input('page', 1);   // Mặc định trang 1
        $offset = ($page - 1) * $limit;

        $query = Event::query()
            ->with(['venue', 'category']) 
            ->withMin('schedules as schedules_min_start_datetime', 'start_datetime')
            ->withMin('ticketTypes as ticket_types_min_base_price', 'base_price')
            ->where('status', Event::STATUS_PUBLISHED);

        // --- CÁC ĐIỀU KIỆN LỌC (GIỮ NGUYÊN) ---
        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('event_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('description', 'LIKE', '%' . $q . '%');
            });
        }

        if (!empty($local) && $local !== 'all') {
            $query->whereHas('venue', function ($v) use ($local) {
                $v->where('city', $local);
            });
        }

        if (!empty($from) || !empty($to)) {
            $query->whereHas('schedules', function ($s) use ($from, $to) {
                if (!empty($from)) $s->whereDate('start_datetime', '>=', $from);
                if (!empty($to))   $s->whereDate('start_datetime', '<=', $to);
            });
        }

        if ($price === 'free') {
            $query->whereHas('ticketTypes', function ($t) {
                $t->where('base_price', 0);
            });
        }

        if (!empty($cate)) {
            $cateIds = explode(',', $cate);
            if (!empty($cateIds)) {
                $query->whereIn('category_id', $cateIds);
            }
        }

        // Sắp xếp
        $query->orderBy('schedules_min_start_datetime', 'asc');

        // [QUAN TRỌNG] Áp dụng phân trang
        $query->skip($offset)->take($limit);

        $events = $query->get();

        // Map data trả về
        $data = $events->map(function (Event $e) {
            return [
                'id' => $e->id,
                'event_name' => $e->event_name,
                'background_image_url' => $e->background_image_url,
                'ticket_types_min_base_price' => (int) $e->ticket_types_min_base_price,
                'schedules_min_start_datetime' => $e->schedules_min_start_datetime,
                'location' => optional($e->venue)->city, 
                'category_id' => optional($e->category)->id,
                'category_name' => optional($e->category)->name,
            ];
        });

        return $this->responseApi->success($data);
    }
}