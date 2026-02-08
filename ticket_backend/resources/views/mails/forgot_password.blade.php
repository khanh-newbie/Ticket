<!DOCTYPE html>
<html>
<head>
    <title>Quên mật khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="color: #16a34a; text-align: center;">Yêu cầu đặt lại mật khẩu</h2>
        <p>Xin chào <strong>{{ $name }}</strong>,</p>
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản TicketBox của bạn.</p>
        <p>Đây là mã xác nhận của bạn:</p>
        
        <div style="text-align: center; margin: 20px 0;">
            <span style="font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333; border: 2px dashed #16a34a; padding: 10px 20px; display: inline-block;">
                {{ $token }}
            </span>
        </div>

        <p>Mã này có hiệu lực trong vòng 15 phút. Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #888; text-align: center;">TicketBox Support Team</p>
    </div>
</body>
</html>