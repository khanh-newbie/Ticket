<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    // Lấy danh sách báo cáo
    public function index(Request $request)
    {
        $status = $request->status;

        // Eager Load 'user', 'event' và 'review'
        // Đây là điểm quan trọng để khớp với Model của bạn
        $reports = Report::with(['user', 'event', 'review', 'event.organizer'])
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['code' => 200, 'data' => $reports]);
    }

    // Cập nhật trạng thái (Resolved / Dismissed)
    public function updateReportStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,dismissed'
        ]);

        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);

        return response()->json(['code' => 200, 'message' => 'Cập nhật thành công.']);
    }
}