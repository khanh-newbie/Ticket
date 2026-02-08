<?php

namespace App\Http\Controllers\Organizers;

use App\Helpers\ResponseApi;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Http\Request;
use App\Helpers\DescriptionImageHelper;
use App\Models\EventCategory;
use App\Models\Organizer;

class CreateEventController extends Controller
{
    private $responseApi;

    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function createEvent(Request $request)
    {
        $baseUrl = 'http://127.0.0.1:8000/';

        // -------------------------
        // ✅ VALIDATE DỮ LIỆU
        // -------------------------
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'info.eventName' => 'required|string|max:255',
            'info.orgName' => 'required|string|max:255',
            'info.orgDescription' => 'required|string',
            'info.category' => 'required|exists:event_categories,id',
            'info.address.cityId' => 'required',
            'info.address.districtId' => 'required',
            'info.address.wardId' => 'required',
            'info.address.street' => 'required|string',
            'time.shows' => 'required|array|min:1',
        ]);

        $info = $request->input('info', []);
        $address = json_decode($info['address'] ?? '{}', true);
        $time = json_decode($request->input('time') ?? '{}', true);
        $payment = json_decode($request->input('payment') ?? '{}', true);

        // Validate mỗi show và vé
        foreach ($time['shows'] ?? [] as $index => $show) {
            validator($show, [
                'start_time' => 'required|date',
                'end_time' => 'required|date|after_or_equal:start_time',
                'tickets' => 'required|array|min:1',
            ])->validate();

            foreach ($show['tickets'] as $ticket) {
                validator($ticket, [
                    'ticket_name' => 'required|string',
                    'price' => 'required|numeric|min:0',
                    'quantity' => 'required|integer|min:1',
                ])->validate();
            }
        }

        // -------------------------
        // 1️⃣ LƯU VENUE
        // -------------------------
        $venue = Venue::create([
            'name' => $info['venueName'] ?? '',
            'city' => $address['cityId'],
            'district' => $address['districtId'],
            'ward' => $address['wardId'],
            'street' => $address['street'],
        ]);

        // -------------------------
        // 2️⃣ UPLOAD FILES
        // -------------------------
        $bgUrls = [];
        if ($request->hasFile('bgFile')) {
            foreach ($request->file('bgFile') as $file) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('bg'), $filename);
                $bgUrls[] = 'bg/' . $filename;
            }
        }
        $bgUrl = $bgUrls[0] ?? null;

        $posterUrls = [];
        if ($request->hasFile('eventFile')) {
            foreach ($request->file('eventFile') as $file) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('poster'), $filename);
                $posterUrls[] = 'poster/' . $filename;
            }
        }
        $posterUrl = $posterUrls[0] ?? null;

        $previewUrl = null;
        if ($request->hasFile('eventPreview')) {
            $file = $request->file('eventPreview');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('preview'), $filename);
            $previewUrl = 'preview/' . $filename;
        }

        $logoUrls = [];
        if ($request->hasFile('orgLogoFile')) {
            foreach ($request->file('orgLogoFile') as $file) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('logo'), $filename);
                $logoUrls[] = 'logo/' . $filename;
            }
        }
        $logoUrl = $logoUrls[0] ?? null;

        // -------------------------
        // 3️⃣ LƯU ORGANIZER
        // -------------------------
        $organizer = Organizer::updateOrCreate(
            ['user_id' => $request->input('user_id')],
            [
                'organization_name' => $info['orgName'],
                'description' => $info['orgDescription'],
                'logo' => $logoUrl ? $baseUrl . $logoUrl : null
            ]
        );

        // -------------------------
        // 4️⃣ XỬ LÝ DESCRIPTION (ảnh Base64)
        // -------------------------
        $descriptionHtml = $info['eventDescription'] ?? '';
        $descriptionHtml = DescriptionImageHelper::saveImages($descriptionHtml);

        // -------------------------
        // 5️⃣ LƯU EVENT
        // -------------------------
        $event = Event::create([
            'event_name' => $info['eventName'],
            'organizer_id' => $organizer->id,
            'venue_id' => $venue->id,
            'category_id' => $info['category'],
            'description' => $descriptionHtml,
            'background_image_url' => $bgUrl ? $baseUrl . $bgUrl : null,
            'poster_image_url' => $posterUrl ? $baseUrl . $posterUrl : null,
            'preview_image_url' => $previewUrl ? $baseUrl . $previewUrl : null,
            'status' => Event::STATUS_PENDING_REVIEW,
        ]);

        // -------------------------
        // 6️⃣ LƯU LỊCH CHIẾU + VÉ
        // -------------------------
        foreach ($time['shows'] ?? [] as $show) {
            $schedule = EventSchedule::create([
                'event_id' => $event->id,
                'start_datetime' => $show['start_time'],
                'end_datetime' => $show['end_time'],
                'status' => EventSchedule::STATUS_UPCOMING,
            ]);

            foreach ($show['tickets'] ?? [] as $ticket) {
                TicketType::create([
                    'schedule_id' => $schedule->id,
                    'name' => $ticket['ticket_name'],
                    'base_price' => $ticket['price'],
                    'total_quantity' => $ticket['quantity'],
                    'available_quantity' => $ticket['quantity'],
                    'status' => TicketType::STATUS_ACTIVE
                ]);
            }
        }

        // -------------------------
        // 7️⃣ RETURN RESPONSE
        // -------------------------
        return $this->responseApi->success([
            'event' => $event,
            'venue' => $venue,
            'time' => $time,
            'payment' => $payment,
            'uploadedBgFiles' => $bgUrls,
            'uploadedPosterFiles' => $posterUrls,
            'uploadedPreview' => $previewUrl,
        ]);
    }

    public function getInfo(Request $request, $id)
    {
        $organizer = Organizer::where('user_id', $id)->first();
        return $this->responseApi->success($organizer);
    }

    public function getEventCategories()
    {
        $categories = EventCategory::orderBy('name', 'asc')
            ->get(['id', 'name']);

        return $this->responseApi->success($categories);
    }
}
