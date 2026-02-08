<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SampleEventsSeeder extends Seeder
{
    public function run(): void
    {
        // ========== 1. USERS (Organizer) ==========
        $organizerUserIds = [];

        for ($i = 1; $i <= 5; $i++) {
            $id = DB::table('users')->insertGetId([
                'name'              => "Organizer User {$i}",
                'email'             => "organizer{$i}@example.com",
                'password'          => bcrypt('password'),
                'phone'             => '0123' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'role'              => 2, // organizer (int)
                'status'            => 1, // active (int)
                'avatar'            => null,
                'device_token'      => null,
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $organizerUserIds[] = $id;
        }

        // ========== 2. ORGANIZERS ==========
        $organizerIds = [];

        foreach ($organizerUserIds as $index => $userId) {
            $id = DB::table('organizers')->insertGetId([
                'user_id'           => $userId,
                'organization_name' => "Organizer Company " . ($index + 1),
                'description'       => 'Professional event organizer #' . ($index + 1),
                'website'           => "https://organizer" . ($index + 1) . ".example.com",
                'verified'          => 1, // verified (int)
                'logo'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $organizerIds[] = $id;
        }

        // ========== 3. VENUES ==========
        $venueData = [
            ['Hanoi Arena', '123 Nguyen Trai, Hanoi', 5000, 'Hanoi'],
            ['Saigon Stadium', '456 Le Loi, HCMC', 3000, 'Ho Chi Minh'],
            ['Danang Center', '789 Tran Phu, Danang', 2000, 'Danang'],
            ['Can Tho Hall', '101 Ly Thai To, Can Tho', 1500, 'Can Tho'],
            ['Hue Palace Hall', '202 Nguyen Hue, Hue', 1000, 'Hue'],
        ];

        $venueIds = [];
        foreach ($venueData as $venue) {
            $id = DB::table('venues')->insertGetId([
                'name'       => $venue[0],
                'address'    => $venue[1],
                'capacity'   => $venue[2],
                'city'       => $venue[3],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $venueIds[] = $id;
        }

        // ========== 4. EVENT CATEGORIES ==========
        $categoryNames = ['Music','Technology','Sports','Business','Education','Comedy','Workshop'];

        $categoryIds = [];
        foreach ($categoryNames as $name) {
            $id = DB::table('event_categories')->insertGetId([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categoryIds[] = $id;
        }

        // ========== 5. MAPPING TRẠNG THÁI DẠNG INT ==========
        $eventStatuses = [
            1, // draft
            2, // pending_review
            3, // published
            4, // cancelled
            5, // completed
        ];

        // ========== 6. TẠO EVENTS + SCHEDULES + TICKET TYPES ==========
        for ($i = 1; $i <= 50; $i++) {

            $eventId = DB::table('events')->insertGetId([
                'organizer_id'        => $organizerIds[array_rand($organizerIds)],
                'venue_id'            => $venueIds[array_rand($venueIds)],
                'category_id'         => $categoryIds[array_rand($categoryIds)],
                'event_name'          => "Sample Event #{$i}",
                'description'         => "This is sample event description #{$i}.",
                'background_image_url'=> "https://picsum.photos/seed/bg{$i}/1200/400",
                'poster_image_url'    => "https://picsum.photos/seed/poster{$i}/600/900",
                'status'              => $eventStatuses[array_rand($eventStatuses)], // INT
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Nhiều category (event_category_map)
            foreach (collect($categoryIds)->shuffle()->take(rand(0,3)) as $cat) {
                DB::table('event_category_map')->insert([
                    'event_id'   => $eventId,
                    'category_id'=> $cat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // tạo 1–3 lịch schedule
            for ($s = 1; $s <= rand(1,3); $s++) {
                $start = Carbon::now()->addDays(rand(1,120))->setTime(rand(9,19), 0);
                $end = (clone $start)->addHours(rand(2,5));

                $scheduleId = DB::table('event_schedules')->insertGetId([
                    'event_id'          => $eventId,
                    'start_datetime'    => $start,
                    'end_datetime'      => $end,
                    'status'            => 1, // upcoming (int)
                    'available_tickets' => rand(200,1000),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // TICKET TYPES (Standard + VIP)
                DB::table('ticket_types')->insert([
                    [
                        'schedule_id'        => $scheduleId,
                        'name'               => 'Standard',
                        'base_price'         => rand(100,300) * 1000,
                        'total_quantity'     => rand(100,500),
                        'available_quantity' => rand(100,500),
                        'status'             => 1, // active
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ],
                    [
                        'schedule_id'        => $scheduleId,
                        'name'               => 'VIP',
                        'base_price'         => rand(300,600) * 1000,
                        'total_quantity'     => rand(50,150),
                        'available_quantity' => rand(50,150),
                        'status'             => 1, // active
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ],
                ]);
            }
        }
    }
}
