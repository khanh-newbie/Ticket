<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseApi; // Nếu bạn có dùng Helper này

class ReportController extends Controller
{
    protected $responseApi;

    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function submitReport(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'type'        => 'required|in:event,review', // Chỉ chấp nhận 'event' hoặc 'review'
            'id'          => 'required|integer',
            'reason'      => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // 2. Chuẩn bị dữ liệu chung
        $data = [
            'user_id'     => Auth::id(),
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending', // Mặc định là chờ xử lý
        ];

        // 3. Xử lý logic điền ID vào đúng cột (event_id hoặc review_id)
        if ($request->type === 'event') {
            // Kiểm tra xem Event có tồn tại không (Optional nhưng nên có)
            $exists = \App\Models\Event::where('id', $request->id)->exists();
            if (!$exists) {
                return response()->json(['code' => 404, 'message' => 'Sự kiện không tồn tại'], 404);
            }

            $data['event_id'] = $request->id;
            $data['review_id'] = null;
        } else {
            // Kiểm tra xem Review có tồn tại không
            $exists = \App\Models\Review::where('id', $request->id)->exists();
            if (!$exists) {
                return response()->json(['code' => 404, 'message' => 'Đánh giá không tồn tại'], 404);
            }

            $data['review_id'] = $request->id;
            $data['event_id'] = null;
        }

        // 4. Lưu vào Database
        try {
            Report::create($data);

            // Nếu dùng ResponseApi helper của bạn
            return $this->responseApi->success([
                'message' => 'Báo cáo đã được gửi thành công. Ban quản trị sẽ xem xét sớm.'
            ]);

            // Hoặc trả về JSON chuẩn nếu chưa cấu hình helper
            // return response()->json([
            //     'code' => 200, 
            //     'message' => 'Báo cáo đã được gửi thành công.'
            // ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }
}
