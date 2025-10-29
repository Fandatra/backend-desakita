<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all()->map(function ($event) {
            $event->event_photo = $event->event_photo ? asset('storage/' . $event->event_photo) : null;
            return $event;
        });

        return response()->json($events);
    }

   public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'pic'         => 'required|string|max:255',
            'description' => 'required|string',
            'event_photo' => 'nullable|image|mimes:jpeg,png|max:2048',
            'location'    => 'required|string|max:255',
            'date'        => 'required|date',
            'time'        => 'required',
        ]);

        // Simpan foto ke storage
        if ($request->hasFile('event_photo')) {
            $path = $request->file('event_photo')->store('events', 'public');
            $validated['event_photo'] = $path;
        }

        // Baru buat event setelah file tersimpan
        $event = Event::create($validated);

        // Tambahkan URL foto agar frontend bisa menampilkannya langsung
        $event->event_photo = $event->event_photo ? asset('storage/' . $event->event_photo) : null;

        return response()->json($event, 201);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);

        // Tambahkan URL publik agar Vue bisa menampilkan gambar
        $event->event_photo = $event->event_photo
            ? asset('storage/' . $event->event_photo)
            : null;

        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'pic'         => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'date'        => 'required|date',
            'time'        => 'required',
            'event_photo' => 'nullable|image|mimes:jpeg,png|max:2048',
        ]);

        // 🔹 Jika user upload foto baru
        if ($request->hasFile('event_photo')) {
            // Hapus foto lama jika ada
            if ($event->event_photo && Storage::disk('public')->exists($event->event_photo)) {
                Storage::disk('public')->delete($event->event_photo);
            }

            // Simpan foto baru
            $path = $request->file('event_photo')->store('events', 'public');
            $validated['event_photo'] = $path;
        }

        // 🔹 Update data event
        $event->update($validated);

        // 🔹 Tambahkan URL publik untuk dikirim ke frontend
        $event->event_photo = $event->event_photo ? asset('storage/' . $event->event_photo) : null;

        return response()->json($event, 200);
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
