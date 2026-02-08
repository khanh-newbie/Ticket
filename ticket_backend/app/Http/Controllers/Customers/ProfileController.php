<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $responseApi;
    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Lấy user hiện tại

        // 1. Validate dữ liệu (quan trọng: check file phải là ảnh)
        $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Tối đa 5MB
        ]);

        // 2. Cập nhật thông tin cơ bản
        $user->fill($request->only(['name', 'phone']));

        // 3. Xử lý Avatar (nếu có gửi lên)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Đặt tên file để tránh trùng lặp (time + tên gốc)
            $filename = time() . '_' . Auth::id();

            // Di chuyển file vào thư mục public/uploads/avatars
            // Đảm bảo thư mục này đã tồn tại trong project của bạn
            $file->move(public_path('uploads/avatars'), $filename);

            // Lưu đường dẫn đầy đủ (URL) vào database
            // Ví dụ: http://localhost:8000/uploads/avatars/123456_image.jpg
            $user->avatar = 'http://localhost:8000/uploads/avatars/' . $filename;
        }

        $user->save();

        // 4. Trả về kết quả kèm URL avatar mới để Frontend cập nhật ngay
        return $this->responseApi->success([
            'user' => $user,
            'avatar_url' => $user->avatar // Trả riêng cái này cho tiện lấy
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = Auth::user();

        if (Hash::check($request->new_password, $user->password)) {
            return $this->responseApi->BadRequest('New password must be different');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->responseApi->success();
    }
}
