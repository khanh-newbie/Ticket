<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->integer('role')->default(1); // 1: Customer, 2: Organizer, 3: Admin
            $table->string('avatar')->nullable();
            $table->integer('status')->default(1); // 1: Active
            $table->string('device_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. User Verifications
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('code');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // 3. Organizers
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('organization_name');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });

        // 4. Venues (ĐÚNG CẤU TRÚC BẠN YÊU CẦU)
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên địa điểm (VD: Nhà hát lớn)
            
            // Lưu Mã (Code) của Tiny API nhưng giữ tên cột cũ
            $table->integer('city')->index();      // Lưu Province Code (VD: 79)
            $table->integer('district')->index();  // Lưu District Code (VD: 760)
            $table->integer('ward')->nullable();   // Lưu Ward Code (VD: 26734)
            
            $table->string('street'); // Số nhà, tên đường
            $table->timestamps();
        });

        // 5. Event Categories
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 6. Events
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizers')->onDelete('cascade');
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('event_categories');
            
            $table->string('event_name');
            $table->text('description')->nullable();
            $table->string('background_image_url')->nullable();
            $table->string('poster_image_url')->nullable();
            $table->integer('status')->default(1); // 1: Draft...
            $table->timestamps();
        });

        // 7. Event Category Map
        Schema::create('event_category_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('event_categories')->onDelete('cascade');
            $table->timestamps();
        });

        // 8. Event Schedules
        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->integer('status')->default(1);
            $table->integer('available_tickets')->default(0);
            $table->timestamps();
        });

        // 9. Ticket Types
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('event_schedules')->onDelete('cascade');
            $table->string('name');
            $table->decimal('base_price', 15, 2);
            $table->integer('total_quantity');
            $table->integer('available_quantity');
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        // 10. Coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('discount_type'); // percent/fixed
            $table->decimal('discount_value', 15, 2);
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->timestamps();
        });

        // 11. Carts
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_code')->nullable()->index();
            $table->timestamps();
        });

        // 12. Cart Items
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
        });

        // 13. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->string('order_code')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2);
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('return_path')->nullable();
            $table->timestamps();
        });

        // 14. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained('ticket_types');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 15. Tickets
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained('ticket_types');
            $table->string('qr_code')->unique();
            $table->string('seat_number')->nullable();
            $table->string('status')->default('valid');
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });

        // 16. Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // 17. Admin Logs
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users');
            $table->string('action');
            $table->string('target_table')->nullable();
            $table->integer('target_id')->nullable();
            $table->text('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });

        // 18. Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('method_type');
            $table->string('provider_name')->nullable();
            $table->string('card_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('expiry_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 19. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');
            $table->string('transaction_code')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('status');
            $table->dateTime('payment_date');
            $table->json('response_data')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Người báo cáo

            // HAI CỘT RIÊNG BIỆT (Cho phép null)
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->foreignId('review_id')->nullable()->constrained('reviews')->onDelete('cascade');

            $table->string('reason'); // Lý do: Lừa đảo, Spam...
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('event_schedules');
        Schema::dropIfExists('event_category_map');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('venues');
        Schema::dropIfExists('organizers');
        Schema::dropIfExists('user_verifications');
        Schema::dropIfExists('users');
        Schema::dropIfExists('reports');
    }
};