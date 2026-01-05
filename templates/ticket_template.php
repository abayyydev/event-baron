<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tiket Event</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .ticket {
            border: 1px solid #ddd;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #1D4ED8;
            color: white;
            padding: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .body {
            padding: 20px;
        }

        h3 {
            font-size: 14px;
            color: #555;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item h4 {
            font-size: 12px;
            color: #666;
            margin: 0 0 5px 0;
            font-weight: normal;
        }

        .detail-item p {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="header">
            <h2><?= htmlspecialchars($data['judul']) ?></h2>
            <p><?= date('l, d F Y', strtotime($data['tanggal_waktu'])) ?></p>
        </div>
        <div class="body">
            <table>
                <tr>
                    <td style="width: 70%; vertical-align: top;">
                        <h3>Informasi Peserta</h3>
                        <div class="details-grid">
                            <!-- MENAMPILKAN DATA STATIS -->
                            <div class="detail-item">
                                <h4>Nama Peserta</h4>
                                <p><?= htmlspecialchars($data['nama_peserta']) ?></p>
                            </div>
                            <div class="detail-item">
                                <h4>Email</h4>
                                <p><?= htmlspecialchars($data['email_peserta']) ?></p>
                            </div>
                            <div class="detail-item">
                                <h4>No. Telepon</h4>
                                <p><?= htmlspecialchars($data['telepon_peserta']) ?></p>
                            </div>
                            <div class="detail-item">
                                <h4>Jenis Kelamin</h4>
                                <p><?= htmlspecialchars($data['jenis_kelamin'] ?: '-') ?></p>
                            </div>

                            <!-- MENAMPILKAN DATA DINAMIS (JIKA ADA) -->
                            <?php if (!empty($data['detail_pendaftar_dinamis'])): ?>
                                <?php foreach ($data['detail_pendaftar_dinamis'] as $detail): ?>
                                    <div class="detail-item">
                                        <h4><?= htmlspecialchars($detail['label']) ?></h4>
                                        <p><?= htmlspecialchars($detail['value']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="width: 30%; text-align: center; vertical-align: top;">
                        <img src="<?= $data['qr_code_path'] ?>" alt="QR Code"
                            style="width:140px; height:140px; margin: 20px auto;">
                        <h4 style="margin-bottom: 5px;">Kode Tiket</h4>
                        <p style="font-family: monospace; font-size: 18px; margin: 0;"><?= $data['kode_unik'] ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>