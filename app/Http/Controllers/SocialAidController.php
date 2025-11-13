<?php

namespace App\Http\Controllers;

use App\Models\SocialAid;
use Illuminate\Http\Request;

class SocialAidController extends Controller
{
    public function index()
    {
        $aids = SocialAid::with('recipients') // <-- ini penting
            ->get()
            ->map(function ($aid) {
                if ($aid->thumbnail) {
                    $aid->thumbnail = asset('storage/' . $aid->thumbnail);
                }
                return $aid;
            });

        return response()->json($aids);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'category'   => 'required|in:bahan pokok,uang tunai,bbm subsidi,kesehatan',
            'aid_name'   => 'required|string|max:255',
            'thumbnail'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'nominal'    => 'required|numeric',
            'donor_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $aid = SocialAid::create($validated);
        if ($aid->thumbnail) {
            $aid->thumbnail = asset('storage/' . $aid->thumbnail);
        }
        return response()->json($aid, 201);
    }

    public function show($id)
    {
        $aid = SocialAid::findOrFail($id);
        if ($aid->thumbnail) {
            $aid->thumbnail = asset('storage/' . $aid->thumbnail);
        }
        return response()->json($aid);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $aid = SocialAid::findOrFail($id);

        $data = $request->all();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $aid->update($data);

        if ($aid->thumbnail) {
            $aid->thumbnail = asset('storage/' . $aid->thumbnail);
        }

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
        $aid = SocialAid::with('recipients.user')->findOrFail($id);
        return response()->json($aid->recipients);
    }

    public function addRecipients(Request $request, $socialAidId)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'head_of_family_ids' => 'required|array',
            'head_of_family_ids.*' => 'exists:head_of_families,id',
        ]);

        $socialAid = SocialAid::findOrFail($socialAidId);

        $ids = $validated['head_of_family_ids'];
        $count = count($ids);

        if ($count === 0) {
            return response()->json(['message' => 'Tidak ada penerima yang dipilih'], 400);
        }

        // Pembagian nominal berbasis integer (tanpa desimal)
        $perPersonNominal = intdiv($socialAid->nominal, $count);
        $remainder = $socialAid->nominal % $count; // sisa pembagian

        $pivotData = [];
        foreach ($ids as $index => $id) {
            // Orang pertama–ke-$remainder dapat 1 rupiah ekstra
            $amount = $perPersonNominal + ($index < $remainder ? 1 : 0);
            $pivotData[$id] = [
                'status' => 'pending',
                'received_nominal' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $socialAid->recipients()->syncWithoutDetaching($pivotData);

        foreach ($pivotData as $headOfFamilyId => $pivotValues) {
            $socialAid->recipients()->updateExistingPivot($headOfFamilyId, $pivotValues);
        }

        return response()->json([
            'message' => 'Penerima berhasil ditambahkan dan nominal dibagi rata tanpa pecahan.',
            'per_person_nominal' => $perPersonNominal,
            'remainder_distributed' => $remainder,
        ]);
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

    public function updateRecipients(Request $request, $socialAidId)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'head_of_family_ids' => 'required|array',
            'head_of_family_ids.*' => 'exists:head_of_families,id',
        ]);

        $socialAid = SocialAid::findOrFail($socialAidId);

        // Hapus semua penerima lama dan ganti dengan yang baru
        $socialAid->recipients()->sync([]);

        $ids = $validated['head_of_family_ids'];
        $count = count($ids);

        if ($count === 0) {
            return response()->json(['message' => 'Tidak ada penerima yang dipilih'], 400);
        }

        // Pembagian nominal seperti sebelumnya
        $perPersonNominal = intdiv($socialAid->nominal, $count);
        $remainder = $socialAid->nominal % $count;

        $pivotData = [];
        foreach ($ids as $index => $id) {
            $amount = $perPersonNominal + ($index < $remainder ? 1 : 0);
            $pivotData[$id] = [
                'status' => 'distributed',
                'received_nominal' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $socialAid->recipients()->sync($pivotData);

        return response()->json([
            'message' => 'Daftar penerima berhasil diperbarui.',
            'total_recipients' => $count,
            'per_person_nominal' => $perPersonNominal,
        ]);
    }

    public function myAids(Request $request)
    {
        $user = $request->user();
        $head = $user->headOfFamily; // ambil kepala keluarga milik user

        if (!$head) {
            return response()->json([], 200); // jika belum punya data kepala keluarga
        }

        $aids = $head->receivedAids()->withPivot('status', 'received_nominal')->get();

        $aids->transform(function ($aid) {
            if ($aid->thumbnail) {
                $aid->thumbnail = asset('storage/' . $aid->thumbnail);
            }
            return $aid;
        });

        return response()->json($aids);
    }

    public function updateMyStatus(Request $request, $socialAidId)
    {
        $user = $request->user();
        $head = $user->headOfFamily;

        if (!$head) {
            return response()->json(['message' => 'Anda belum terdaftar sebagai kepala keluarga'], 400);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,distributed',
        ]);

        $socialAid = SocialAid::findOrFail($socialAidId);

        // Pastikan user memang penerima bantuan ini
        $recipient = $socialAid->recipients()->where('head_of_family_id', $head->id)->first();
        if (!$recipient) {
            return response()->json(['message' => 'Anda bukan penerima bantuan ini'], 403);
        }



        // Update status di pivot
        $socialAid->recipients()->updateExistingPivot($head->id, [
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status bantuan berhasil diperbarui']);
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
