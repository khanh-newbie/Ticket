<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function getAllReviews(Request $request)
    {
        $status = $request->status;
        $keyword = $request->keyword; // 1. Lấy từ khóa từ Frontend gửi lên

        $reviews = Review::with(['user', 'event'])
            // Lọc theo trạng thái (Pending, Active, Hidden)
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            // 2. Logic Tìm kiếm nâng cao
            ->when($keyword, function ($q) use ($keyword) {
                return $q->where(function ($subQ) use ($keyword) {
                    // Tìm trong bảng Users (Tên hoặc ID)
                    $subQ->whereHas('user', function ($userQ) use ($keyword) {
                        $userQ->where('name', 'like', "%{$keyword}%")
                              ->orWhere('id', $keyword); // Tìm chính xác ID
                    })
                    // Hoặc tìm trong bảng Events (Tên sự kiện)
                    ->orWhereHas('event', function ($eventQ) use ($keyword) {
                        $eventQ->where('event_name', 'like', "%{$keyword}%");
                    })
                    // Hoặc tìm trong nội dung Comment
                    ->orWhere('comment', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['code' => 200, 'data' => $reviews]);
    }

    public function updateReviewStatus(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => $request->status]);
        return response()->json(['code' => 200, 'message' => 'Success']);
    }
}