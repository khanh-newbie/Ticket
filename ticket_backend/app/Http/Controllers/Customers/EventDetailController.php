<?php

namespace App\Http\Controllers\Customers;

use App\Helpers\ResponseApi;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventDetailController extends Controller
{
    private $responseApi;
    public function __construct()
    {

        $this->responseApi = new ResponseApi();
    }

    public function index(Request $request, $id)
    {
        $event = Event::with(['schedules', 'venue', 'organizer', 'ticketTypes'])
            ->withMin('ticketTypes', 'base_price')
            ->withMin('schedules', 'start_datetime')
            ->where('id', $id)
            ->first();

        return $this->responseApi->success($event);
    }

    public function getInfoOrganizer(Request $request, $id)
    {
        $organizer = Organizer::find($id);
        return $this->responseApi->success($organizer);
    }

    public function addToCart(Request $request)
    {
        $param = $request->all();
        $cart = Cart::updateOrCreate(
            ['user_id' => Auth::id()],
        );
        $cartItem = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'ticket_type_id' => $param['ticket_id'],
        ]);
        $cartItem->quantity = ($cartItem->quantity ?? 0) + $param['quantity'];
        $cartItem->save();

        $countCart = CartItem::where('cart_id', $cart->id)->count();
        return $this->responseApi->success($countCart);
    }

    public function getCart(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->join('ticket_types', 'cart_items.ticket_type_id', '=', 'ticket_types.id')
            ->join('event_schedules as schedules', 'ticket_types.schedule_id', '=', 'schedules.id')
            ->join('events', 'schedules.event_id', '=', 'events.id')
            ->select(
                'cart_items.*',
                'ticket_types.name as ticket_type_name',
                'ticket_types.base_price',
                'schedules.start_datetime',
                'schedules.end_datetime',
                'events.event_name as event_name',
                'events.background_image_url',
                'events.poster_image_url',
                'events.description'
            );
        $cartItems = $cartItems->orderBy('schedules.start_datetime', 'asc')->get();
        return $this->responseApi->success($cartItems);
    }

    public function removeFromCart(Request $request)
    {
        $ticketId = $request->ticket_id;
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return (new \App\Helpers\ResponseApi())->BadRequest('Không tìm thấy giỏ hàng');
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('ticket_type_id', $ticketId)
            ->first();

        if (!$cartItem) {
            return (new \App\Helpers\ResponseApi())->BadRequest('Sản phẩm không tồn tại trong giỏ');
        }

        $cartItem->delete();

        $countCart = CartItem::where('cart_id', $cart->id)->count();
        return (new \App\Helpers\ResponseApi())->success($countCart); // Dùng success giống các API khác
    }

    public function getEventReviews(Request $request)
    {
        $param = $request->all();
        $eventId = $param['event_id'];

        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->select(
                'reviews.id',
                'reviews.rating',
                'reviews.comment',
                'reviews.created_at',
                'users.name as user_name',
                'users.avatar as user_avatar'
            )
            ->where('reviews.event_id', $eventId)
            ->where('reviews.status', 'active')
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        return $this->responseApi->success($reviews);
    }
}
