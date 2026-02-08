export const authGuard = (to, from, next) => {
    // 1. CẤU HÌNH MAP (QUAN TRỌNG)
    // Map từ ID trong Database sang tên Role trong Router
    const ROLE_MAP = {
        3: 'admin',      // 3 là Admin
        2: 'organizer',  // 2 là Organizer
        1: 'customer'    // 1 là Customer (nếu có)
    };

    // Hàm phụ: Lấy đường dẫn trang chủ dựa theo role
    const getHomeRoute = (roleName) => {
        switch (roleName) {
            case 'admin': return '/admin/dashboard';
            case 'organizer': return '/organizer/events';
            default: return '/'; // Customer hoặc role lạ về trang chủ
        }
    };

    // 2. LẤY THÔNG TIN USER
    const token = sessionStorage.getItem('access_token');
    const userInfoStr = sessionStorage.getItem('user_info');
    let userRoleName = null; // Biến này sẽ lưu 'admin', 'organizer'...

    if (userInfoStr) {
        try {
            const user = JSON.parse(userInfoStr);
            // user.role đang là số (3, 2...) -> Chuyển thành chữ
            if (user.role && ROLE_MAP[user.role]) {
                userRoleName = ROLE_MAP[user.role];
            }
        } catch (e) {
            console.error("Lỗi parse user info", e);
            sessionStorage.clear();
        }
    }

    // 3. LOGIC CHẶN GUEST (Login/Register)
    // Nếu đã đăng nhập mà cố vào trang login -> Đẩy về trang chủ của role đó
    if (to.meta.guestOnly && token) {
        return next(getHomeRoute(userRoleName));
    }

    // 4. LOGIC CHECK QUYỀN (Requires Auth)
    if (to.meta.requiresAuth) {
        // Chưa đăng nhập -> Đá về login
        if (!token) {
            // alert("Vui lòng đăng nhập!"); // Bật nếu muốn
            return next('/login');
        }

        // Đã đăng nhập nhưng sai quyền
        // Ví dụ: User là 'organizer' nhưng vào route yêu cầu 'admin'
        if (to.meta.role && to.meta.role !== userRoleName) {
            alert("Bạn không có quyền truy cập trang này!");
            // Đưa về đúng nơi họ thuộc về
            return next(getHomeRoute(userRoleName));
        }
    }

    // 5. CHO PHÉP ĐI TIẾP
    next();
};