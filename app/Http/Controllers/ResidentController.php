<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\HeadOfFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return Resident::with('headOfFamily')->get();
        }

        return Resident::whereHas('headOfFamily', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('headOfFamily')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'head_of_family_id' => 'required|exists:head_of_families,id',
            'name'              => 'required|string|max:255',
            'nik'               => 'required|string|unique:residents,nik',
            'gender'            => 'required|in:male,female',
            'date_of_birth'     => 'required|date',
            'phone_number'      => 'nullable|string',
            'occupation'        => 'nullable|string',
            'marital_status'    => 'required|in:single,married',
            'relation'          => 'required|string',
            'photo'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // cek kepemilikan
        $hof = HeadOfFamily::findOrFail($validated['head_of_family_id']);
        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // simpan foto kalau ada
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('resident_photos', 'public');
            $validated['photo'] = $path;
        }

        $resident = Resident::create($validated);
        return response()->json($resident, 201);
    }

    public function show(Request $request, $id)
    {
        $resident = Resident::with('headOfFamily')->findOrFail($id);

        if ($request->user()->role !== 'admin' && $resident->headOfFamily->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return $resident;
    }

    public function update(Request $request, $id)
    {
        $resident = Resident::findOrFail($id);

        if ($request->user()->role !== 'admin' && $resident->headOfFamily->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'              => 'sometimes|required|string|max:255',
            'nik'               => 'sometimes|required|string|unique:residents,nik,' . $resident->id,
            'gender'            => 'sometimes|required|in:male,female',
            'date_of_birth'     => 'sometimes|required|date',
            'phone_number'      => 'nullable|string',
            'occupation'        => 'nullable|string',
            'marital_status'    => 'sometimes|required|in:single,married',
            'relation'          => 'sometimes|required|string',
            'photo'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // hapus foto lama kalau ada
            if ($resident->photo && Storage::disk('public')->exists($resident->photo)) {
                Storage::disk('public')->delete($resident->photo);
            }

            // upload foto baru
            $path = $request->file('photo')->store('resident_photos', 'public');
            $validated['photo'] = $path;
        }

        $resident->update($validated);
        return response()->json($resident);
    }

    public function destroy(Request $request, $id)
    {
        $resident = Resident::findOrFail($id);

        if ($request->user()->role !== 'admin' && $resident->headOfFamily->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $resident->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function byHeadOfFamily($id)
    {
        $residents = Resident::where('head_of_family_id', $id)->get();

        $residents->transform(function ($resident) {
            $resident->photo_url = $resident->photo
                ? asset('storage/' . $resident->photo)
                : null;
            return $resident;
        });

        return response()->json($residents);
    }

}
