<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAid;

class SocialAidRecipientController extends Controller
{
    public function store(Request $request, $socialAidId)
    {
        $validated = $request->validate([
            'head_of_family_ids' => 'required|array',
            'head_of_family_ids.*' => 'exists:head_of_families,id',
        ]);

        $socialAid = SocialAid::findOrFail($socialAidId);
        $socialAid->recipients()->syncWithoutDetaching($validated['head_of_family_ids']);

        return response()->json(['message' => 'Recipients added successfully']);
    }

}
