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

    public function recipients($id)
    {
        $aid = SocialAid::with('recipients')->findOrFail($id);
        return response()->json($aid->recipients);
    }

    public function updateRecipient(Request $request, $socialAidId, $headOfFamilyId)
    {
        $validated = $request->validate([
            'status' => 'in:approved,pending,rejected,distributed',
            'received_nominal' => 'nullable|numeric',
            'notes' => 'nullable|string'
        ]);

        $socialAid = SocialAid::findOrFail($socialAidId);
        $socialAid->recipients()->updateExistingPivot($headOfFamilyId, $validated);

        return response()->json(['message' => 'Recipient status updated']);
    }

    public function summary($id)
    {
        $aid = SocialAid::withCount(['recipients as total_recipients'])
            ->withCount([
                'recipients as distributed_count' => function ($query) {
                    $query->wherePivot('status', 'distributed');
                }
            ])
            ->findOrFail($id);

        return response()->json($aid);
    }

}
