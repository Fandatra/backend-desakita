<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        return Event::all();
    }

   public function store(Request $request)
{
    $this->authorizeAdmin($request);

    $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'pic'         => 'required|string|max:255',
        'description' => 'required|string',
        'event_photo' => 'nullable|string|max:255',
        'location'    => 'required|string|max:255',
        'date'        => 'required|date',
        'time'        => 'required',
    ]);

    $event = Event::create($validated);

    return response()->json($event, 201);
}


    public function show($id)
    {
        return Event::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $event = Event::findOrFail($id);
        $event->update($request->all());
        return response()->json($event);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        Event::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    private function authorizeAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
