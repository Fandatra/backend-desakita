<?php

namespace App\Http\Controllers;

use App\Models\SocialAid;
use Illuminate\Http\Request;

class SocialAidController extends Controller
{
    public function index()
    {
        return SocialAid::all();
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'category'   => 'required|in:bahan pokok,uang tunai,bbm subsidi,kesehatan',
            'aid_name'   => 'required|string|max:255',
            'thumbnail'  => 'nullable|string|max:255',
            'nominal'    => 'required|numeric',
            'donor_name' => 'required|string|max:255',
            'description'=> 'nullable|string',
        ]);

        $aid = SocialAid::create($validated);
        return response()->json($aid, 201);
    }

    public function show($id)
    {
        return SocialAid::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $aid = SocialAid::findOrFail($id);
        $aid->update($request->all());
        return response()->json($aid);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        SocialAid::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    private function authorizeAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
