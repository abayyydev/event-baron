<?php
// admin/ajax_get_calon_peserta.php

require_once '../core/koneksi.php';

$event_id = $_GET['event_id'] ?? 0;

if ($event_id == 0) {
    echo '<tr><td colspan="4" class="text-center p-4">Event ID tidak valid.</td></tr>';
    exit;
}

try {
    // Query: Ambil semua santri + Cek apakah sudah terdaftar di event ini
    // Kolom 'is_registered' akan bernilai > 0 jika sudah daftar
    $sql = "SELECT s.*, 
            (SELECT COUNT(*) FROM pendaftaran p 
             WHERE p.santri_id = s.id AND p.workshop_id = :eid) as is_registered
            FROM santri s 
            ORDER BY s.kelas ASC, s.nama_lengkap ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['eid' => $event_id]);
    $santri_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($santri_list) > 0) {
        foreach ($santri_list as $s) {
            $id = $s['id'];
            $nama = htmlspecialchars($s['nama_lengkap']);
            $nis = htmlspecialchars($s['nis']);
            $kelas = htmlspecialchars($s['kelas']);
            $wali = htmlspecialchars($s['nama_wali']);
            $hp = htmlspecialchars($s['no_hp_wali']);
            $jk = htmlspecialchars($s['jenis_kelamin']);

            // Logic Terdaftar
            $registered = $s['is_registered'] > 0;

            // Atribut baris
            $disabledAttr = $registered ? 'disabled' : '';
            $rowClass = $registered ? 'bg-gray-50 opacity-60 cursor-not-allowed' : 'hover:bg-emerald-50/50 cursor-pointer transition-colors';
            $onClick = $registered ? '' : 'onclick="toggleRow(this)"'; // Fungsi JS di parent page

            // Checkbox Class "santri-checkbox" PENTING untuk JS updateCount()
            $checkboxHtml = $registered
                ? '<i class="fas fa-check-circle text-emerald-500"></i>'
                : "<input type='checkbox' name='santri_ids[]' value='$id' class='santri-checkbox w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer pointer-events-none'>";

            // Badge Status
            $statusBadge = $registered
                ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Sudah Terdaftar</span>'
                : '';

            echo "
            <tr class='border-b border-gray-50 $rowClass' $onClick>
                
                <td class='p-4 text-center'>
                    <div class='flex justify-center items-center h-full'>
                        $checkboxHtml
                    </div>
                </td>

                <td class='p-4'>
                    <div class='search-target'>
                        <div class='font-bold text-gray-800 text-sm'>$nama $statusBadge</div>
                        <div class='flex items-center gap-2 mt-1'>
                            <span class='text-[10px] font-mono bg-gray-100 text-gray-500 px-1.5 rounded border border-gray-200'>$nis</span>
                            <span class='text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 rounded'>$kelas</span>
                        </div>
                    </div>
                </td>

                <td class='p-4'>
                    <div class='flex flex-col'>
                        <span class='text-xs font-semibold text-gray-700'><i class='fas fa-user-friends text-gray-300 mr-1'></i> $wali</span>
                        <span class='text-xs text-gray-500 mt-0.5'><i class='fab fa-whatsapp text-green-500 mr-1'></i> $hp</span>
                    </div>
                </td>

                <td class='p-4 text-center'>
                    <span class='text-xs font-medium " . ($jk == 'Laki-laki' ? 'text-blue-600 bg-blue-50' : 'text-pink-600 bg-pink-50') . " px-2 py-1 rounded-lg'>
                        " . ($jk == 'Laki-laki' ? 'L' : 'P') . "
                    </span>
                </td>

            </tr>";
        }
    } else {
        echo '<tr><td colspan="4" class="p-8 text-center text-gray-500 italic">Belum ada data santri. Silakan tambahkan data master santri terlebih dahulu.</td></tr>';
    }

} catch (PDOException $e) {
    echo '<tr><td colspan="4" class="p-8 text-center text-red-500">Error: ' . $e->getMessage() . '</td></tr>';
}
?>