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
            'nama_lokasi' => 'required|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'deskripsi'   => 'nullable|string|max:2000',
            'kategori'    => 'nullable|string|max:50',
        ]);

        if (empty($validated['kategori'])) {
            $validated['kategori'] = 'Lainnya';
        }

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

        // Strict ownership check via private helper
        if (($err = $this->authorizeOwnership($lokasi)) !== null) {
            return response()->json(['message' => $err], 401);
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

        // Strict ownership check via private helper
        if (($err = $this->authorizeOwnership($lokasi)) !== null) {
            return response()->json(['message' => $err], 401);
        }

        $validated = $request->validate([
            'nama_lokasi' => 'sometimes|string|max:255',
            'latitude'    => 'sometimes|numeric|between:-90,90',
            'longitude'   => 'sometimes|numeric|between:-180,180',
            'deskripsi'   => 'nullable|string|max:2000',
            'kategori'    => 'nullable|string|max:50',
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

        // Strict ownership check via private helper
        if (($err = $this->authorizeOwnership($lokasi)) !== null) {
            return response()->json(['message' => $err], 401);
        }

        $lokasi->delete();
        return response()->json(['message' => 'Lokasi berhasil dihapus'], 200);
    }

    // Export lokasi milik user ke Excel (.xls) — format SpreadsheetML, tanpa library
    public function export()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $lokasis  = Auth::user()->lokasis;
        $filename = 'lokasi_wisata_' . date('Ymd_His') . '.xls';
        $user     = Auth::user();

        // Warna header (hex tanpa #)
        $headerBg  = '1E3A8A'; // biru gelap
        $headerFg  = 'FFFFFF'; // teks putih
        $titleBg   = '0F172A'; // hampir hitam
        $evenRowBg = 'F8FAFC'; // abu sangat muda

        // Warna per kategori (hex tanpa #)
        $katColor = [
            'Pantai'  => 'DBEAFE',
            'Gunung'  => 'FEF3C7',
            'Kota'    => 'F3E8FF',
            'Alam'    => 'DCFCE7',
            'Budaya'  => 'FEE2E2',
            'Kuliner' => 'FFEDD5',
            'Lainnya' => 'F1F5F9',
        ];

        // ── Build SpreadsheetML XML ──────────────────────────────────────────
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<?mso-application progid=\"Excel.Sheet\"?>\n";
        $xml .= "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= "  xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= "  xmlns:x=\"urn:schemas-microsoft-com:office:excel\">\n";

        // ── Styles ──────────────────────────────────────────────────────────
        $xml .= "<Styles>\n";

        // Default
        $xml .= "<Style ss:ID=\"Default\"><Font ss:FontName=\"Calibri\" ss:Size=\"11\"/></Style>\n";

        // Judul laporan
        $xml .= "<Style ss:ID=\"sTitle\">"
              . "<Font ss:FontName=\"Calibri\" ss:Size=\"14\" ss:Bold=\"1\" ss:Color=\"#{$headerFg}\"/>"
              . "<Interior ss:Color=\"#{$titleBg}\" ss:Pattern=\"Solid\"/>"
              . "<Alignment ss:Vertical=\"Center\"/>"
              . "</Style>\n";

        // Sub-info (meta)
        $xml .= "<Style ss:ID=\"sMeta\">"
              . "<Font ss:FontName=\"Calibri\" ss:Size=\"9\" ss:Color=\"#64748B\"/>"
              . "<Interior ss:Color=\"#F1F5F9\" ss:Pattern=\"Solid\"/>"
              . "</Style>\n";

        // Header kolom
        $xml .= "<Style ss:ID=\"sHeader\">"
              . "<Font ss:FontName=\"Calibri\" ss:Size=\"11\" ss:Bold=\"1\" ss:Color=\"#{$headerFg}\"/>"
              . "<Interior ss:Color=\"#{$headerBg}\" ss:Pattern=\"Solid\"/>"
              . "<Alignment ss:Vertical=\"Center\" ss:WrapText=\"0\"/>"
              . "<Borders>"
              . "<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#1E40AF\"/>"
              . "</Borders>"
              . "</Style>\n";

        // Baris data ganjil
        $xml .= "<Style ss:ID=\"sOdd\">"
              . "<Font ss:FontName=\"Calibri\" ss:Size=\"10\"/>"
              . "<Alignment ss:Vertical=\"Center\"/>"
              . "<Borders>"
              . "<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>"
              . "</Borders>"
              . "</Style>\n";

        // Baris data genap
        $xml .= "<Style ss:ID=\"sEven\">"
              . "<Font ss:FontName=\"Calibri\" ss:Size=\"10\"/>"
              . "<Interior ss:Color=\"#{$evenRowBg}\" ss:Pattern=\"Solid\"/>"
              . "<Alignment ss:Vertical=\"Center\"/>"
              . "<Borders>"
              . "<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>"
              . "</Borders>"
              . "</Style>\n";

        // Style kategori dinamis
        foreach ($katColor as $kat => $bg) {
            $id = 'sKat' . preg_replace('/[^A-Za-z]/', '', $kat);
            $xml .= "<Style ss:ID=\"{$id}\">"
                  . "<Font ss:FontName=\"Calibri\" ss:Size=\"10\" ss:Bold=\"1\"/>"
                  . "<Interior ss:Color=\"#{$bg}\" ss:Pattern=\"Solid\"/>"
                  . "<Alignment ss:Vertical=\"Center\" ss:Horizontal=\"Center\"/>"
                  . "</Style>\n";
        }

        $xml .= "</Styles>\n";

        // ── Worksheet ────────────────────────────────────────────────────────
        $xml .= "<Worksheet ss:Name=\"Lokasi Wisata\">\n<Table>\n";

        // Lebar kolom (dalam points)
        $colWidths = [40, 200, 80, 100, 100, 220, 120, 120];
        foreach ($colWidths as $w) {
            $xml .= "<Column ss:Width=\"{$w}\"/>\n";
        }

        // Baris 1: Judul
        $colCount = 8;
        $xml .= "<Row ss:Height=\"28\">"
              . "<Cell ss:MergeAcross=\"" . ($colCount - 1) . "\" ss:StyleID=\"sTitle\">"
              . "<Data ss:Type=\"String\">Data Lokasi Wisata - " . htmlspecialchars($user->name) . "</Data>"
              . "</Cell></Row>\n";

        // Baris 2: Meta info
        $meta = 'Diekspor: ' . now()->format('d/m/Y H:i')
              . '   |   Total: ' . $lokasis->count() . ' lokasi';
        $xml .= "<Row ss:Height=\"18\">"
              . "<Cell ss:MergeAcross=\"" . ($colCount - 1) . "\" ss:StyleID=\"sMeta\">"
              . "<Data ss:Type=\"String\">" . htmlspecialchars($meta) . "</Data>"
              . "</Cell></Row>\n";

        // Baris 3: Kosong
        $xml .= "<Row ss:Height=\"10\"><Cell><Data ss:Type=\"String\"></Data></Cell></Row>\n";

        // Baris 4: Header kolom
        $headers = ['ID', 'Nama Lokasi', 'Kategori', 'Latitude', 'Longitude', 'Deskripsi', 'Tanggal Simpan', 'Update Terakhir'];
        $xml .= "<Row ss:Height=\"22\">";
        foreach ($headers as $h) {
            $xml .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">" . htmlspecialchars($h) . "</Data></Cell>";
        }
        $xml .= "</Row>\n";

        // Baris data
        $rowNum = 0;
        foreach ($lokasis as $lokasi) {
            $rowNum++;
            $baseStyle = ($rowNum % 2 === 0) ? 'sEven' : 'sOdd';
            $kat       = $lokasi->kategori ?? 'Lainnya';
            $katStyle  = 'sKat' . preg_replace('/[^A-Za-z]/', '', $kat);

            $xml .= "<Row ss:Height=\"20\">";

            // ID
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"Number\">{$lokasi->id}</Data></Cell>";

            // Nama Lokasi
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"String\">" . htmlspecialchars($lokasi->nama_lokasi) . "</Data></Cell>";

            // Kategori (dengan warna)
            $xml .= "<Cell ss:StyleID=\"{$katStyle}\"><Data ss:Type=\"String\">" . htmlspecialchars($kat) . "</Data></Cell>";

            // Latitude
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"Number\">{$lokasi->latitude}</Data></Cell>";

            // Longitude
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"Number\">{$lokasi->longitude}</Data></Cell>";

            // Deskripsi
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"String\">" . htmlspecialchars($lokasi->deskripsi ?? '-') . "</Data></Cell>";

            // Tanggal Simpan
            $tgl = $lokasi->created_at ? $lokasi->created_at->format('d/m/Y H:i') : '-';
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"String\">{$tgl}</Data></Cell>";

            // Update Terakhir
            $upd = $lokasi->updated_at ? $lokasi->updated_at->format('d/m/Y H:i') : '-';
            $xml .= "<Cell ss:StyleID=\"{$baseStyle}\"><Data ss:Type=\"String\">{$upd}</Data></Cell>";

            $xml .= "</Row>\n";
        }

        $xml .= "</Table>\n";

        // Freeze pane pada baris 5 (setelah header)
        $xml .= "<WorksheetOptions xmlns=\"urn:schemas-microsoft-com:office:excel\">"
              . "<FreezePanes/><FrozenNoSplit/>"
              . "<SplitHorizontal>4</SplitHorizontal><TopRowBottomPane>4</TopRowBottomPane>"
              . "<ActivePane>2</ActivePane>"
              . "</WorksheetOptions>\n";

        $xml .= "</Worksheet>\n</Workbook>";

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    // Halaman detail publik — tanpa auth, bisa diakses siapa saja
    public function publicDetail(string $id)
    {
        $lokasi = Lokasi::find($id);

        if (!$lokasi) {
            abort(404);
        }

        // Coba ambil thumbnail dari Wikipedia API
        $thumbnail = null;
        try {
            $query   = urlencode($lokasi->nama_lokasi);
            $apiUrl  = "https://en.wikipedia.org/api/rest_v1/page/summary/{$query}";
            $ctx     = stream_context_create(['http' => ['timeout' => 3, 'header' => 'User-Agent: LokasiWisataApp/1.0']]);
            $json    = @file_get_contents($apiUrl, false, $ctx);
            if ($json) {
                $data      = json_decode($json, true);
                $thumbnail = $data['thumbnail']['source'] ?? null;
            }
        } catch (\Exception $e) {
            // Thumbnail opsional, abaikan error
        }

        return view('lokasi-detail', compact('lokasi', 'thumbnail'));
    }

    /**
     * Centralized ownership check — DRY pattern reused by show/update/destroy.
     * Returns null if authorized, otherwise returns an error message string.
     */
    private function authorizeOwnership(Lokasi $lokasi): ?string
    {
        if (!Auth::check()) {
            return 'Unauthorized';
        }
        if (Auth::id() !== $lokasi->user_id) {
            return 'Unauthorized — data ini milik pengguna lain';
        }
        return null;
    }
}
