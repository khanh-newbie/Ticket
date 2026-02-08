<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseTestNew extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'], // Kiểm tra nếu email tồn tại thì không tạo trùng
            [
                'name'              => 'Super Administrator',
                'password'          => Hash::make('123456'), // Mật khẩu mặc định
                'phone'             => '0999999999',
                'role'              => 3, // ROLE_ADMIN
                'status'            => 1, // Active
                'avatar'            => null,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );
        // =================================================================
        // 1. TẠO CATEGORIES (ID CỐ ĐỊNH)
        // =================================================================
        $categories = [
            ['id' => 1, 'name' => 'Nhạc sống'],             // Live Music
            ['id' => 2, 'name' => 'Sân khấu & Nghệ thuật'], // Stage Art
            ['id' => 3, 'name' => 'Thể thao'],              // Sports
            ['id' => 4, 'name' => 'Khác'],                  // Other
        ];

        foreach ($categories as $cat) {
            DB::table('event_categories')->updateOrInsert(
                ['id' => $cat['id']],
                ['name' => $cat['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $catIds = array_column($categories, 'id');

        // =================================================================
        // 2. TẠO VENUES (Dữ liệu địa điểm thật với MÃ CODE CHUẨN)
        // =================================================================
        // Quy ước Code: Hà Nội=1, HCM=79, Đà Nẵng=48, Cần Thơ=92
        // District/Ward là mã giả định hợp lệ
        $venuesData = [
            [
                'name' => 'Sân vận động Mỹ Đình',
                'street' => 'Đường Lê Đức Thọ',
                'city' => 1,
                'district' => 21,
                'ward' => 100 // Hà Nội, Nam Từ Liêm
            ],
            [
                'name' => 'Nhà hát Lớn Hà Nội',
                'street' => '01 Tràng Tiền',
                'city' => 1,
                'district' => 1,
                'ward' => 5 // Hà Nội, Hoàn Kiếm
            ],
            [
                'name' => 'Trung tâm Hội nghị Quốc gia',
                'street' => 'Đại lộ Thăng Long',
                'city' => 1,
                'district' => 21,
                'ward' => 101 // Hà Nội, Nam Từ Liêm
            ],
            [
                'name' => 'Nhà hát Hòa Bình',
                'street' => '240 Đường 3/2',
                'city' => 79,
                'district' => 760,
                'ward' => 26734 // HCM, Q10
            ],
            [
                'name' => 'Sân vận động Thống Nhất',
                'street' => '138 Đào Duy Từ',
                'city' => 79,
                'district' => 760,
                'ward' => 26730 // HCM, Q10
            ],
            [
                'name' => 'Nhà thi đấu Phú Thọ',
                'street' => '1 Lữ Gia',
                'city' => 79,
                'district' => 761,
                'ward' => 26740 // HCM, Q11
            ],
            [
                'name' => 'Cung Thể thao Tiên Sơn',
                'street' => 'Đường 2/9',
                'city' => 48,
                'district' => 492,
                'ward' => 20200 // Đà Nẵng, Hải Châu
            ],
            [
                'name' => 'Sân vận động Cần Thơ',
                'street' => 'Lê Lợi, Cái Khế',
                'city' => 92,
                'district' => 916,
                'ward' => 31000 // Cần Thơ, Ninh Kiều
            ]
        ];

        $venueIds = [];
        foreach ($venuesData as $v) {
            $venueIds[] = DB::table('venues')->insertGetId([
                'name'       => $v['name'],
                'street'     => $v['street'],
                'city'       => $v['city'],     // INT
                'district'   => $v['district'], // INT
                'ward'       => $v['ward'],     // INT
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // =================================================================
        // 3. TẠO USERS & ORGANIZERS (Nhà tổ chức xịn)
        // =================================================================
        $orgNames = [
            'Viet Ticket Corp',
            'M-TP Entertainment',
            'SpaceSpeakers Group',
            'V-League Organizer',
            'Vietnam Concerts',
            'Saigon Theatre'
        ];

        $organizerIds = [];

        foreach ($orgNames as $index => $orgName) {
            // Tạo User
            $userId = DB::table('users')->insertGetId([
                'name'              => "Admin " . $orgName,
                'email'             => "contact" . $index . "@" . Str::slug($orgName) . ".com",
                'password'          => Hash::make('123456'),
                'phone'             => '0909' . rand(100000, 999999),
                'role'              => 2, // Organizer
                'status'            => 1, // Active
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Tạo Organizer Profile
            $organizerIds[] = DB::table('organizers')->insertGetId([
                'user_id'           => $userId,
                'organization_name' => $orgName,
                'description'       => "Đơn vị tổ chức sự kiện chuyên nghiệp hàng đầu Việt Nam.",
                'website'           => "https://" . Str::slug($orgName) . ".vn",
                'verified'          => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // =================================================================
        // 4. DATA POOL CHO TÊN SỰ KIỆN (Để random cho thật)
        // =================================================================
        $eventTemplates = [
            1 => [ // Nhạc sống
                "Liveshow Hà Anh Tuấn - Chân Trời Rực Rỡ",
                "Mỹ Tâm - Tri Âm Concert",
                "Sơn Tùng M-TP - Sky Tour 2025",
                "Đen Vâu - Show Của Đen",
                "Hòa Nhạc Giao Hưởng Mùa Thu",
                "Đêm Nhạc Trịnh Công Sơn - Biển Nhớ",
                "Rap Việt All-Star Concert",
                "Lễ Hội Âm Nhạc EDM Ravolution",
                "Chillies - Tour Xuyên Việt"
            ],
            2 => [ // Sân khấu
                "Vở Kịch: Ngày Xửa Ngày Xưa 34",
                "Kịch Nói: Dạ Cổ Hoài Lang",
                "Xiếc Nghệ Thuật: À Ố Show",
                "Múa Rối Nước Thăng Long",
                "Hài Kịch: Táo Quân Vi Hành",
                "Nhạc Kịch: Bỉ Vỏ"
            ],
            3 => [ // Thể thao
                "Chung Kết Bóng Đá AFF Cup: VN vs Thailand",
                "V-League: Hà Nội FC vs CAHN",
                "Giải Chạy Marathon VnExpress Midnight",
                "Bóng Rổ VBA: Saigon Heat vs Hanoi Buffaloes",
                "Giải Quần Vợt Vietnam Open",
                "Võ Thuật Tổng Hợp Lion Championship"
            ],
            4 => [ // Khác
                "Triển Lãm Công Nghệ Tech Expo 2025",
                "Hội Chợ Sách Quốc Tế",
                "Lễ Hội Ẩm Thực Đường Phố",
                "Workshop: Nhiếp Ảnh Nghệ Thuật",
                "Ngày Hội Việc Làm IT"
            ]
        ];
        

        // =================================================================
        // 5. TẠO 50 EVENTS
        // =================================================================
        for ($i = 1; $i <= 50; $i++) {
            $catId = $catIds[array_rand($catIds)]; // Random category ID (1-4)

            // Lấy tên event random theo đúng category để logic hợp lý
            $namePool = $eventTemplates[$catId] ?? ["Sự kiện đặc biệt #$i"];
            $baseName = $namePool[array_rand($namePool)];

            // Random thêm năm hoặc số để tránh trùng lặp nhàm chán
            $eventName = $baseName . " (" . rand(2024, 2025) . ")";

            // Random địa điểm
            $venueId = $venueIds[array_rand($venueIds)];

            // Insert Event
            $eventId = DB::table('events')->insertGetId([
                'organizer_id'       => $organizerIds[array_rand($organizerIds)],
                'venue_id'           => $venueId,
                'category_id'        => $catId,
                'event_name'         => $eventName,
                'description'        => "Trải nghiệm không gian sự kiện đẳng cấp $eventName. Cùng tham gia để tận hưởng những khoảnh khắc đáng nhớ.",
                'background_image_url' => "https://picsum.photos/800/400?random=$i",
                'poster_image_url'   => "https://picsum.photos/400/600?random=$i",
                'status'             => 3, // Published (đa số là published cho đẹp)
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // =============================================================
            // 6. TẠO SCHEDULE & TICKETS
            // =============================================================

            // Mỗi event có 1 hoặc 2 suất diễn
            $numSchedules = rand(1, 2);

            for ($s = 0; $s < $numSchedules; $s++) {
                // Random ngày diễn (có thể quá khứ hoặc tương lai gần)
                // 30% quá khứ, 70% tương lai
                $isPast = rand(1, 100) <= 30;
                $startDate = $isPast
                    ? Carbon::now()->subDays(rand(1, 30))
                    : Carbon::now()->addDays(rand(1, 60));

                $startDate->setHour(rand(18, 20))->setMinute(0); // Diễn tối 18h-20h
                $endDate = (clone $startDate)->addHours(3);

                $scheduleId = DB::table('event_schedules')->insertGetId([
                    'event_id'          => $eventId,
                    'start_datetime'    => $startDate,
                    'end_datetime'      => $endDate,
                    'status'            => $isPast ? 3 : 1, // 3: Completed, 1: Upcoming
                    'available_tickets' => rand(500, 2000),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Tạo các loại vé (Ticket Types) cho suất diễn này
                // 1. Vé thường
                DB::table('ticket_types')->insert([
                    'schedule_id'        => $scheduleId,
                    'name'               => 'Standard',
                    'base_price'         => rand(2, 8) * 100000, // 200k - 800k
                    'total_quantity'     => 500,
                    'available_quantity' => rand(100, 500),
                    'status'             => 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // 2. Vé VIP (50% cơ hội có VIP)
                if (rand(0, 1)) {
                    DB::table('ticket_types')->insert([
                        'schedule_id'        => $scheduleId,
                        'name'               => 'VIP Area',
                        'base_price'         => rand(10, 20) * 100000, // 1tr - 2tr
                        'total_quantity'     => 100,
                        'available_quantity' => rand(10, 90),
                        'status'             => 1,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            }
        }
    }
}
