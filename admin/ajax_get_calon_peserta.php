<?php
// ajax_get_calon_peserta.php

// Gunakan path relatif (mundur satu folder ke core)
require_once '../core/koneksi.php';

if (!isset($_GET['event_id'])) {
    echo '<tr><td colspan="4" class="text-center p-4 text-red-500">Error: Event ID tidak ditemukan</td></tr>';
    exit;
}

$workshop_id = $_GET['event_id'];

try {
    // Ambil user yang BELUM terdaftar di event ini
    $sql = "SELECT * FROM users 
            WHERE role = 'peserta' 
            AND id NOT IN (
                SELECT user_id FROM pendaftaran WHERE workshop_id = ?
            )
            ORDER BY nama_lengkap ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$workshop_id]);
    $calon_peserta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($calon_peserta) > 0) {
        foreach ($calon_peserta as $usr) {
            // Logika Jenis Kelamin (Sesuai kolom baru di database)
            $jk = $usr['jenis_kelamin'];
            $badge_jk = '<span class="text-gray-400 text-xs">-</span>';

            if ($jk == 'Laki-laki') {
                $badge_jk = '<span class="bg-blue-100 text-blue-600 py-1 px-2 rounded text-xs font-bold">L</span>';
            } elseif ($jk == 'Perempuan') {
                $badge_jk = '<span class="bg-pink-100 text-pink-600 py-1 px-2 rounded text-xs font-bold">P</span>';
            }
            ?>

            <tr class="hover:bg-blue-50 transition cursor-pointer border-b border-gray-100" onclick="toggleRow(this)">
                <td class="p-4 text-center">
                    <input type="checkbox" name="user_ids[]" value="<?= $usr['id'] ?>"
                        class="santri-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 pointer-events-none">
                </td>

                <td class="p-4 font-medium text-gray-800 search-target">
                    <?= htmlspecialchars($usr['nama_lengkap']) ?>
                </td>

                <td class="p-4 text-sm text-gray-500">
                    <?= htmlspecialchars($usr['email']) ?>
                    <?php
                    // Perbaikan: Menggunakan kolom 'no_whatsapp' sesuai screenshot database Anda
                    if (!empty($usr['no_whatsapp'])): ?>
                        <br>
                        <span class="text-xs text-gray-400 flex items-center gap-1 mt-1">
                            <i class="fab fa-whatsapp text-green-500"></i>
                            <?= htmlspecialchars($usr['no_whatsapp']) ?>
                        </span>
                    <?php endif; ?>
                </td>

                <td class="p-4 text-center">
                    <?= $badge_jk ?>
                </td>
            </tr>

            <?php
        }
    } else {
        // Tampilan kosong
        echo '<tr><td colspan="4" class="p-8 text-center text-gray-500 bg-gray-50">
                <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                    <span class="mt-2">Semua santri sudah terdaftar di event ini.</span>
                </div>
              </td></tr>';
    }

} catch (PDOException $e) {
    echo '<tr><td colspan="4" class="text-center text-red-500 p-4">Error Database: ' . $e->getMessage() . '</td></tr>';
}
?>