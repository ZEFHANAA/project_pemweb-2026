<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiController extends Controller
{
    // Tampil lokasi milik user yang login. Guest mendapat array kosong.
    public function index()
    {
        if (Auth::check()) {
            // Jika user sudah login, tampilkan lokasi milik user saja
            return response()->json(Auth::user()->lokasis, 200);
        }
        // Jika belum login, kembalikan array kosong
        // (lokasi bersifat pribadi, hanya bisa dilihat oleh pemiliknya)
        return response()->json([], 200);
    }

    // Simpan lokasi baru - hanya untuk user terautentikasi
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'nama_lokasi' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $lokasi = Lokasi::create($validated);
        return response()->json($lokasi, 201);
    }

    // Tampil detail lokasi
    public function show(string $id)
    {
        $lokasi = Lokasi::find($id);
        if (!$lokasi) {
            return response()->json(['message' => 'Lokasi tidak ditemukan'], 404);
        }
        return response()->json($lokasi, 200);
    }

    // Update lokasi - hanya pemilik yang bisa update
    public function update(Request $request, string $id)
    {
        $lokasi = Lokasi::find($id);
        if (!$lokasi) {
            return response()->json(['message' => 'Lokasi tidak ditemukan'], 404);
        }

        // Check jika user terautentikasi dan (pemilik lokasi OR lokasi legacy tanpa user_id)
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        if ($lokasi->user_id !== null && Auth::id() !== $lokasi->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'nama_lokasi' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        $lokasi->update($validated);
        return response()->json($lokasi, 200);
    }

    // Hapus lokasi - hanya pemilik yang bisa hapus
    public function destroy(string $id)
    {
        $lokasi = Lokasi::find($id);
        if (!$lokasi) {
            return response()->json(['message' => 'Lokasi tidak ditemukan'], 404);
        }

        // Check jika user terautentikasi dan (pemilik lokasi OR lokasi legacy tanpa user_id)
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        if ($lokasi->user_id !== null && Auth::id() !== $lokasi->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lokasi->delete();
        return response()->json(['message' => 'Lokasi berhasil dihapus'], 200);
    }

    // Export lokasi milik user yang login ke CSV
    public function export()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $lokasis = Auth::user()->lokasis;
        $filename = 'lokasi_wisata_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['id', 'nama_lokasi', 'latitude', 'longitude', 'deskripsi', 'created_at'];

        $callback = function () use ($lokasis, $columns) {
            $file = fopen('php://output', 'w');
            // BOM agar Excel bisa baca UTF-8
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($lokasis as $lokasi) {
                fputcsv($file, [
                    $lokasi->id,
                    $lokasi->nama_lokasi,
                    $lokasi->latitude,
                    $lokasi->longitude,
                    $lokasi->deskripsi,
                    $lokasi->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

