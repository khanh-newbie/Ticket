<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 4px solid #ea580c; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; color: #333; font-size: 24px; }
        .order-id { color: #666; margin-top: 5px; font-size: 14px; }
        .intro { padding: 20px 0; font-size: 16px; }
        
        .ticket { border: 2px dashed #e5e7eb; padding: 20px; margin-bottom: 25px; border-radius: 12px; background: #fffaf0; position: relative; }
        .ticket-header { font-weight: bold; color: #ea580c; font-size: 18px; margin-bottom: 10px; border-bottom: 1px solid #f3d8c6; padding-bottom: 5px; }
        
        .ticket-info p { margin: 5px 0; }
        
        .qr-code { text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e5e7eb; }
        .qr-code img { display: block; margin: 0 auto; max-width: 150px; height: auto; border: 4px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .qr-note { font-size: 12px; color: #888; margin-top: 8px; font-style: italic; }

        .footer { font-size: 13px; color: #777; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .total-amount { font-size: 18px; font-weight: bold; color: #ea580c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thanh toán thành công!</h1>
            <p class="order-id">Đơn hàng #{{ $order->id }}</p>
        </div>

        <div class="intro">
            <p>Xin chào {{ $order->user->name ?? 'bạn' }},</p>
            <p>Cảm ơn bạn đã đặt vé. Dưới đây là vé điện tử của bạn. Vui lòng đưa mã QR này cho nhân viên soát vé.</p>
        </div>

        @foreach($tickets as $index => $ticket)
            <div class="ticket">
                <div class="ticket-header">
                    VÉ #{{ $index + 1 }}: {{ $ticket['name'] }}
                </div>
                
                <div class="ticket-info">
                    <p><strong>Mã vé:</strong> <span style="font-family: monospace; font-size: 1.1em;">{{ $ticket['code'] }}</span></p>
                    <p><strong>Giá vé:</strong> {{ number_format($ticket['price']) }} VNĐ</p>
                </div>
                
                <div class="qr-code">
                    <img src="{{ $ticket['qr_url'] }}" alt="QR Code">
                    <p class="qr-note">Quét mã này tại cổng soát vé</p>
                </div>
            </div>
        @endforeach

        <div class="footer">
            <p>Tổng thanh toán: <span class="total-amount">{{ number_format($order->total_amount) }} VNĐ</span></p>
            <p>Nếu có vấn đề gì, vui lòng liên hệ bộ phận hỗ trợ.</p>
            <p>&copy; {{ date('Y') }} Hệ thống bán vé.</p>
        </div>
    </div>
</body>
</html>