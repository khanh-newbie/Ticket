<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Helpers\ResponseApi;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ResponseApi();
    }

    public function index(Request $request)
    {
        try {
            $keyword = $request->query('keyword');

            $organizers = Organizer::with('user')
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('organization_name', 'like', "%{$keyword}%");
                })
                ->orderByDesc('created_at')
                ->get();

            if ($organizers->isEmpty()) {
                return $this->response->dataNotfound();
            }

            return $this->response->success($organizers);

        } catch (\Exception $e) {
            return $this->response->InternalServerError();
        }
    }
}
