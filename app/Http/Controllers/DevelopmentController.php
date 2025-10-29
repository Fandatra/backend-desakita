<?php

namespace App\Http\Controllers;

use App\Models\Development;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DevelopmentController extends Controller
{
    // GET /developments
    public function index()
    {
        $devs = Development::all()->map(function ($dev) {
            $dev->photo_url = $dev->photo ? asset('storage/' . $dev->photo) : null;
            return $dev;
        });

        return response()->json($devs);
    }

    public function publicIndex()
    {
        $devs = Development::latest()->get()->map(function ($dev) {
            $dev->photo_url = $dev->photo ? asset('storage/' . $dev->photo) : null;
            return $dev;
        });

        return response()->json($devs);
    }

    // POST /developments (admin only)
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'pic'         => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'status'      => 'required|in:planning,ongoing,completed',
            'budget'      => 'required|numeric',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('developments', 'public');
        }

        $dev = Development::create($validated);

        return response()->json($dev, 201);
    }

    // GET /developments/{id}
    public function show($id)
    {
        $dev = Development::findOrFail($id);
        $dev->photo_url = $dev->photo ? asset('storage/' . $dev->photo) : null;
        return response()->json($dev);
    }

    // PUT /developments/{id} (admin only)
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'pic'         => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location'    => 'sometimes|string|max:255',
            'status'      => 'sometimes|in:planning,ongoing,completed',
            'budget'      => 'sometimes|numeric',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after_or_equal:start_date',
        ]);

        $dev = Development::findOrFail($id);
        $dev->update($validated);

        if ($request->hasFile('photo')) {
            // hapus foto lama jika ada
            if ($dev->photo && Storage::disk('public')->exists($dev->photo)) {
                Storage::disk('public')->delete($dev->photo);
            }
            $dev->photo = $request->file('photo')->store('developments', 'public');
            $dev->save();
        }

        return response()->json($dev);
    }

    // DELETE /developments/{id} (admin only)
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        Development::destroy($id);

        return response()->json(['message' => 'Deleted successfully']);
    }

    // 🔐 helper check admin
    private function authorizeAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
