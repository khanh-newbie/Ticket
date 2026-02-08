<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseApi;
use App\Models\Event;
use Illuminate\Http\Request;

class EventReviewController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ResponseApi();
    }

    // Danh sách pending
    public function pending()
    {
        $events = Event::with('venue', 'organizer')
            ->where('status', Event::STATUS_PENDING_REVIEW)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->response->success($events);
    }

    public function approve($id)
    {
        $event = Event::findOrFail($id);

        $event->update([
            'status' => Event::STATUS_PUBLISHED
        ]);

        if ($event->category_id) {
            $event->categories()->syncWithoutDetaching([
                $event->category_id
            ]);
        }

        return response()->json([
            'message' => 'Event approved'
        ]);
    }


    public function reject(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $event->update([
            'status' => Event::STATUS_CANCELLED
        ]);

        return response()->json([
            'message' => 'Event rejected'
        ]);
    }

    public function show($id)
    {
        $event = Event::with([
            'organizer',
            'venue',
            'schedules',
            'ticketTypes',
            'categories'
        ])->findOrFail($id);

        return $this->response->success($event);
    }

}
