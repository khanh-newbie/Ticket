<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     // Users
    //     Schema::create('users', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('name')->nullable();
    //         $table->string('email')->unique();
    //         $table->timestamp('email_verified_at')->nullable();
    //         $table->string('password')->nullable();
    //         $table->string('phone')->nullable();
    //         $table->integer('role')->nullable(); // 1 customer, 2 organizer, 3 admin
    //         $table->string('status')->nullable(); // 1 active, 2 banned, 3 pending
    //         $table->text('avatar')->nullable();
    //         $table->text('device_token')->nullable();
    //         $table->rememberToken();
    //         $table->timestamps();
    //     });

    //     // Organizers
    //     Schema::create('organizers', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id')->nullable();
    //         $table->string('organization_name')->nullable();
    //         $table->text('description')->nullable();
    //         $table->string('website')->nullable();
    //         $table->integer('verified')->nullable(); // 0 not verified, 1 verified
    //         $table->string('logo')->nullable();
    //         $table->timestamps();

    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index('user_id');
    //     });

    //     // User verifications
    //     Schema::create('user_verifications', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id')->index(); // Khóa ngoại tới bảng users
    //         $table->string('code'); // Mã xác thực (VD: 6 ký tự)
    //         $table->timestamp('expires_at')->nullable(); // Thời gian hết hạn mã
    //         $table->timestamps();

    //         // 🔗 Khóa ngoại
    //         $table->foreign('user_id')
    //             ->references('id')
    //             ->on('users')
    //             ->onDelete('cascade'); // Nếu user bị xóa thì xóa luôn mã
    //     });
    //     // Messages
    //     Schema::create('messages', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('sender_id')->nullable();
    //         $table->unsignedBigInteger('receiver_id')->nullable();
    //         $table->text('content')->nullable();
    //         $table->timestamp('sent_at')->nullable();
    //         $table->timestamp('is_read')->nullable(); // Thay vì boolean
    //         $table->timestamps();

    //         $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index(['sender_id', 'receiver_id']);
    //     });

    //     // Admin Logs
    //     Schema::create('admin_logs', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('admin_id')->nullable();
    //         $table->string('action')->nullable();
    //         $table->unsignedBigInteger('target_id')->nullable();
    //         $table->timestamps();

    //         $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index('admin_id');
    //     });

    //     // Venues
    //     Schema::create('venues', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('name')->nullable();
    //         $table->string('city')->nullable();
    //         $table->string('district')->nullable();
    //         $table->string('ward')->nullable();
    //         $table->string('street')->nullable();
    //         $table->timestamps();
    //     });

    //     // Event Categories
    //     Schema::create('event_categories', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('name')->nullable();
    //         $table->timestamps();
    //     });

    //     // Events
    //     Schema::create('events', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('organizer_id')->nullable();
    //         $table->unsignedBigInteger('venue_id')->nullable();
    //         $table->unsignedBigInteger('category_id')->nullable();
    //         $table->string('event_name')->nullable();
    //         $table->text('description')->nullable();
    //         $table->text('background_image_url')->nullable();
    //         $table->text('poster_image_url')->nullable();
    //         $table->string('status')->nullable(); // draft / pending_review / published / cancelled / completed
    //         $table->timestamps();

    //         $table->foreign('organizer_id')->references('id')->on('organizers')->onDelete('cascade');
    //         $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
    //         $table->foreign('category_id')->references('id')->on('event_categories')->onDelete('set null');
    //         $table->index(['organizer_id', 'venue_id', 'category_id']);
    //     });

    //     // Event Schedules
    //     Schema::create('event_schedules', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('event_id')->nullable();
    //         $table->timestamp('start_datetime')->nullable();
    //         $table->timestamp('end_datetime')->nullable();
    //         $table->string('status')->nullable(); // upcoming / ongoing / completed / cancelled
    //         $table->integer('available_tickets')->nullable();
    //         $table->timestamps();

    //         $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
    //         $table->index('event_id');
    //     });

    //     // Event Category Map
    //     Schema::create('event_category_map', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('event_id')->nullable();
    //         $table->unsignedBigInteger('category_id')->nullable();
    //         $table->timestamps();

    //         $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
    //         $table->foreign('category_id')->references('id')->on('event_categories')->onDelete('cascade');
    //         $table->index(['event_id', 'category_id']);
    //     });

    //     // Ticket Types
    //     Schema::create('ticket_types', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('schedule_id')->nullable();
    //         $table->string('name')->nullable();
    //         $table->integer('base_price')->nullable();
    //         $table->integer('total_quantity')->nullable();
    //         $table->integer('available_quantity')->nullable();
    //         $table->string('status')->nullable(); // active / sold_out / inactive
    //         $table->timestamps();

    //         $table->foreign('schedule_id')->references('id')->on('event_schedules')->onDelete('cascade');
    //         $table->index('schedule_id');
    //     });

    //     // Reviews
    //     Schema::create('reviews', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('event_id')->nullable();
    //         $table->unsignedBigInteger('user_id')->nullable();
    //         $table->unsignedBigInteger('order_id')->unique();
    //       //  $table->unsignedBigInteger('event_id')->nullable();
    //         $table->integer('rating')->nullable(); // 1-5
    //         $table->text('comment')->nullable();
    //         $table->timestamps();
    //         $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    //         $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index(['event_id', 'user_id', 'order_id']);
    //     });

    //     // Carts
    //     Schema::create('carts', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id')->nullable();
    //         $table->string('session_code')->nullable();
    //         $table->timestamps();

    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index('user_id');
    //     });

    //     // Cart Items
    //     Schema::create('cart_items', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('cart_id')->nullable();
    //         $table->unsignedBigInteger('ticket_type_id')->nullable();
    //         $table->integer('quantity')->nullable();
    //         $table->timestamp('added_at')->nullable();

    //         $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
    //         $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
    //         $table->index(['cart_id', 'ticket_type_id']);
    //         $table->timestamps();
    //     });

    //     // Coupons
    //     Schema::create('coupons', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('event_id')->nullable();
    //         $table->string('code')->nullable();
    //         $table->string('discount_type')->nullable(); // percent / fixed
    //         $table->integer('discount_value')->nullable();
    //         $table->integer('max_uses')->nullable();
    //         $table->integer('used_count')->nullable();
    //         $table->timestamp('valid_from')->nullable();
    //         $table->timestamp('valid_until')->nullable();
    //         $table->timestamps();

    //         $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
    //         $table->index('event_id');
    //     });

    //     // Orders
    //     Schema::create('orders', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id')->nullable();
    //         $table->unsignedBigInteger('coupon_id')->nullable();
    //         $table->integer('total_amount')->nullable();
    //         $table->integer('discount_amount')->nullable();
    //         $table->integer('final_amount')->nullable();
    //         $table->string('payment_status')->nullable(); // pending / paid / failed / refunded / cancelled
    //         $table->string('payment_method')->nullable(); // credit_card / momo / zalo_pay …
    //         $table->timestamps();

    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
    //         $table->index(['user_id', 'coupon_id']);
    //     });

    //     // Order Items
    //     Schema::create('order_items', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('order_id')->nullable();
    //         $table->unsignedBigInteger('ticket_type_id')->nullable();
    //         $table->integer('quantity')->nullable();
    //         $table->integer('unit_price')->nullable();
    //         $table->integer('subtotal')->nullable();
    //         $table->timestamps();

    //         $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    //         $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
    //         $table->index(['order_id', 'ticket_type_id']);
    //     });

    //     // Tickets
    //     Schema::create('tickets', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('order_item_id')->nullable();
    //         $table->unsignedBigInteger('ticket_type_id')->nullable();
    //         $table->string('qr_code')->nullable();
    //         $table->string('seat_number')->nullable();
    //         $table->string('status')->nullable(); // valid / used / cancelled
    //         $table->timestamp('issued_at')->nullable();
    //         $table->timestamps();

    //         $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
    //         $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
    //         $table->index(['order_item_id', 'ticket_type_id']);
    //     });

    //     // Payment Methods
    //     Schema::create('payment_methods', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id')->nullable();
    //         $table->string('method_type')->nullable(); // credit_card / e_wallet
    //         $table->string('provider_name')->nullable();
    //         $table->string('card_number')->nullable(); // token
    //         $table->string('account_holder_name')->nullable();
    //         $table->string('expiry_date')->nullable();
    //         $table->integer('is_default')->nullable(); // 0/1
    //         $table->timestamps();

    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->index('user_id');
    //     });

    //     // Payments
    //     Schema::create('payments', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('order_id')->nullable();
    //         $table->unsignedBigInteger('payment_method_id')->nullable();
    //         $table->string('transaction_code')->nullable();
    //         $table->integer('amount')->nullable();
    //         $table->string('status')->nullable(); // pending / success / failed / refunded
    //         $table->timestamp('payment_date')->nullable();
    //         $table->json('response_data')->nullable();
    //         $table->timestamps();

    //         $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    //         $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
    //         $table->index(['order_id', 'payment_method_id']);
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::dropIfExists('payments');
    //     Schema::dropIfExists('payment_methods');
    //     Schema::dropIfExists('tickets');
    //     Schema::dropIfExists('order_items');
    //     Schema::dropIfExists('orders');
    //     Schema::dropIfExists('coupons');
    //     Schema::dropIfExists('cart_items');
    //     Schema::dropIfExists('carts');
    //     Schema::dropIfExists('reviews');
    //     Schema::dropIfExists('ticket_types');
    //     Schema::dropIfExists('event_category_map');
    //     Schema::dropIfExists('event_schedules');
    //     Schema::dropIfExists('events');
    //     Schema::dropIfExists('event_categories');
    //     Schema::dropIfExists('venues');
    //     Schema::dropIfExists('admin_logs');
    //     Schema::dropIfExists('messages');
    //     Schema::dropIfExists('organizers');
    //     Schema::dropIfExists('users');
    //     Schema::dropIfExists('user_verifications');
    // }
};
