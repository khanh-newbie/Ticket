<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Helpers\ResponseApi;
use Illuminate\Support\Facades\Auth;
use App\Services\ContentModeratorService; // [MỚI] Import Service AI

class ReviewController extends Controller
{
    private $responseApi;

    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function submitReview(Request $request, ContentModeratorService $moderator)
    {
        $param = $request->all();
        $userId = Auth::id();

        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'order_id' => 'required',
            'event_id' => 'required',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'required|string|max:1000',
        ]);

        // 2. [MỚI] Sử dụng AI để kiểm tra nội dung comment
        // Hàm checkContent trả về mảng ['is_safe' => bool, 'reason' => string, ...]
        $aiCheck = $moderator->checkContent($param['comment']);

        // Nếu AI bảo an toàn -> active, ngược lại -> pending
        $status = ($aiCheck['is_safe'] ?? true) ? 'active' : 'pending';
        $reason = $aiCheck['reason'] ?? '';

        // 3. Tạo Review vào Database
        $review = Review::create([
            'order_id' => $param['order_id'],
            'event_id' => $param['event_id'],
            'user_id'  => $userId,
            'rating'   => $param['rating'],
            'comment'  => $param['comment'],
            'status'   => $status, // Lưu trạng thái
        ]);

        // 4. Trả về kết quả
        // Nếu là 'active' -> Thông báo thành công bình thường
        if ($status === 'active') {
            return $this->responseApi->success([
                'message' => 'Gửi đánh giá thành công!',
                'data'    => $review
            ]);
        } 
        
        // Nếu là 'pending' -> Thông báo cho user biết đang chờ duyệt
        return $this->responseApi->success([
            'message' => 'Đánh giá đang chờ duyệt do chứa nội dung cần xem xét: ' . $reason,
            'data'    => $review
        ]);
    }
}