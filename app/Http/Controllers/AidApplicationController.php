<?php

namespace App\Http\Controllers;

use App\Models\AidApplication;
use Illuminate\Http\Request;

class AidApplicationController extends Controller
{
    // GET semua pengajuan
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return AidApplication::with(['socialAid','headOfFamily'])->get();
        } else {
            return AidApplication::where('head_of_family_id', $request->user()->id)
                ->with(['socialAid','headOfFamily'])
                ->get();
        }
    }

    // POST /aid-applications (user membuat pengajuan)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'social_aid_id'     => 'required|exists:social_aids,id',
            'bank_account'      => 'required|string',
            'requested_nominal' => 'required|numeric',
            'reason'            => 'required|string',
        ]);

        $app = AidApplication::create([
            'head_of_family_id' => $request->user()->id, // user = kepala keluarga
            'social_aid_id'     => $validated['social_aid_id'],
            'bank_account'      => $validated['bank_account'],
            'requested_nominal' => $validated['requested_nominal'],
            'reason'            => $validated['reason'],
            'status'            => 'pending',
        ]);

        return response()->json($app, 201);
    }

    // GET /aid-applications/{id}
    public function show(Request $request, $id)
    {
        $app = AidApplication::findOrFail($id);

        if ($request->user()->role !== 'admin' && $app->head_of_family_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return $app;
    }

    // PUT /aid-applications/{id}
    public function update(Request $request, $id)
    {
        $app = AidApplication::findOrFail($id);

        if ($request->user()->role === 'admin') {
            $app->update($request->all());
        } elseif ($app->head_of_family_id === $request->user()->id && $app->status === 'pending') {
            $app->update($request->only(['reason','bank_account','requested_nominal']));
        } else {
            abort(403, 'Unauthorized');
        }

        return response()->json($app);
    }

    // DELETE
    public function destroy(Request $request, $id)
    {
        $app = AidApplication::findOrFail($id);

        if ($request->user()->role === 'admin' || $app->head_of_family_id === $request->user()->id) {
            $app->delete();
            return response()->json(['message' => 'Deleted successfully']);
        }

        abort(403, 'Unauthorized');
    }
}
