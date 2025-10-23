<?php

namespace App\Http\Controllers;

use App\Models\HeadOfFamily;
use Illuminate\Http\Request;

class HeadOfFamilyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return HeadOfFamily::with('user')->get();
        }

        $heads = HeadOfFamily::with('user')->get();
        return response()->json($heads);

        return HeadOfFamily::where('user_id', $request->user()->id)->with('user')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'profile_picture' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            'nik'            => 'required|string|unique:head_of_families,nik',
            'gender'         => 'required|in:male,female',
            'date_of_birth'  => 'required|date',
            'phone_number'   => 'nullable|string',
            'address'        => 'required|string',
            'occupation'     => 'nullable|string',
            'marital_status' => 'required|in:single,married,divorced',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $validated['profile_picture'] = $path;
        }

        $hof = HeadOfFamily::create($validated);
        return response()->json($hof, 201);
    }

    public function show(Request $request, $id)
    {
        $hof = HeadOfFamily::with('user')->findOrFail($id);

        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return $hof;
    }

    public function update(Request $request, $id)
    {
        $hof = HeadOfFamily::findOrFail($id);

        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $hof->update($request->all());
        return response()->json($hof);
    }

    public function destroy(Request $request, $id)
    {
        $hof = HeadOfFamily::findOrFail($id);

        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $hof->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
