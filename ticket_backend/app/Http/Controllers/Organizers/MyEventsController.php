<?php

namespace App\Http\Controllers\Organizers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseApi;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MyEventsController extends Controller
{
    private $responseApi;
    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }
    public function getMyEvents(Request $request)
{
    $user = Auth::user();
    // Giả sử user_id là khóa ngoại trong bảng events
    $userId = $user->id; 

    // Nhận tham số
    $keyword = $request->input('keyword');
    $statusFilter = $request->input('status', 'upcoming'); // Filter từ tab frontend (upcoming, past, pending, draft)
    $limit = $request->input('limit', 10);
    $page = $request->input('page', 1);
    $offset = ($page - 1) * $limit;

    // Query cơ bản
    $query = Event::with(['venue', 'ticketTypes'])
        ->withMin('ticketTypes', 'base_price')
        ->withMin('schedules', 'start_datetime')
        ->where('organizer_id', $user->organizer->id) // Hoặc where('user_id', $userId) tùy DB của bạn
        ->orderBy('created_at', 'desc');

    // 1. Lọc theo từ khóa
    if (!empty($keyword)) {
        $query->where('event_name', 'like', "%{$keyword}%");
    }

    // 2. Lọc theo Status (Logic phức tạp kết hợp Thời gian và Trạng thái)
    switch ($statusFilter) {
        case 'pending':
            // Lấy trạng thái Chờ duyệt
            $query->where('status', Event::STATUS_PENDING_REVIEW);
            break;

        case 'draft':
            // Lấy trạng thái Nháp
            $query->where('status', Event::STATUS_DRAFT);
            break;
        
        case 'cancelled':
            $query->where('status', Event::STATUS_CANCELLED);
            break;

        case 'upcoming':
            // Sắp tới: Phải là Đã công bố (Published) VÀ Có lịch diễn > hiện tại
            $query->where('status', Event::STATUS_PUBLISHED)
                  ->whereHas('schedules', function ($q) {
                      $q->where('start_datetime', '>=', Carbon::now());
                  });
            break;

        case 'past':
            // Đã qua: (Đã công bố HOẶC Đã hoàn thành) VÀ Tất cả lịch diễn < hiện tại
            $query->whereIn('status', [Event::STATUS_PUBLISHED, Event::STATUS_COMPLETED])
                  ->whereDoesntHave('schedules', function ($q) {
                      $q->where('start_datetime', '>=', Carbon::now());
                  });
            break;
            
        default:
            // Mặc định lấy tất cả hoặc logic khác tùy bạn
            break;
    }

    // 3. Phân trang & Response
    $total = $query->count();
    $events = $query->skip($offset)->take($limit)->get();

    return $this->responseApi->success([
        'events' => $events,
        'pagination' => [
            'total' => $total,
            'current_page' => (int)$page,
            'per_page' => (int)$limit
        ]
    ]);
}
}
