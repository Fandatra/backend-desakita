<?php

namespace App\Http\Controllers;

use App\Models\HeadOfFamily;
use Illuminate\Http\Request;

class HeadOfFamilyController extends Controller
{
    public function index(Request $request)
    {
        // Jika admin, tampilkan semua kepala keluarga + relasi user & residents
        if ($request->user()->role === 'admin') {
            return HeadOfFamily::with(['user', 'residents'])->get();
        }

        // Jika user biasa, tampilkan hanya miliknya sendiri
        return HeadOfFamily::with(['user'])
            ->withCount('residents') // ➕ menambah kolom 'residents_count'
            ->get();
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
            $validated['profile_picture'] = $path; // <-- ini sudah benar
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

        return response()->json([
            'data' => [
                'id' => $hof->id,
                'user_id' => $hof->user_id,
                'user_name' => $hof->user?->name ?? 'Tidak ada nama',
                'user_email' => $hof->user?->email ?? 'Tidak ada email',
                'profile_picture_url' => $hof->profile_picture ? asset('storage/' . $hof->profile_picture) : null,
                'nik' => $hof->nik,
                'gender' => $hof->gender,
                'date_of_birth' => $hof->date_of_birth,
                'phone_number' => $hof->phone_number,
                'address' => $hof->address,
                'occupation' => $hof->occupation,
                'marital_status' => $hof->marital_status,
            ]
        ]);
    }


    public function update(Request $request, $id)
    {
        $hof = HeadOfFamily::findOrFail($id);

        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Validasi data
        $validated = $request->validate([
            'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'nik' => 'required|string|unique:head_of_families,nik,' . $id,
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
            'phone_number' => 'nullable|string',
            'address' => 'required|string',
            'occupation' => 'nullable|string',
            'marital_status' => 'required|in:single,married,divorced',
            'email' => 'nullable|email|unique:users,email,' . ($hof->user_id ?? 'NULL'),
        ]);

        // Update nama dan alamat email user juga
        if ($hof->user) {
            if ($request->filled('name')) {
                $hof->user->name = $request->name;
            }
            if ($request->filled('email')) {
                $hof->user->email = $request->email;
            }
            $hof->user->save();
        }

        // Jika ada upload file baru
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $validated['profile_picture'] = $path;
        }

        // Update field
        $hof->update($validated);

        return response()->json([
            'message' => 'Head of family updated successfully',
            'data' => $hof
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $hof = HeadOfFamily::with('user')->findOrFail($id);

        if ($request->user()->role !== 'admin' && $hof->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Hapus akun user yang terhubung
        if ($hof->user) {
            $hof->user->delete();
        }

        // Hapus head of family
        $hof->delete();

        return response()->json(['message' => 'Head of family and user deleted successfully']);
    }

    public function myHead(Request $request)
    {
        $head = HeadOfFamily::where('user_id', $request->user()->id)->first();

        if (!$head) {
            return response()->json(['message' => 'Belum ada data kepala keluarga'], 404);
        }

        return response()->json($head);
    }

    public function myAids(Request $request)
    {
        $head = $request->user()->headOfFamily; // relasi dari User ke HeadOfFamily
        return $head->receivedAids()->withPivot(['status', 'received_nominal', 'notes'])->get();
    }
}
