<?php
http_response_code(404); // Set status code
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-indigo-600">404</h1>
        <p class="text-2xl font-medium text-gray-800 mb-4">Oops! Halaman Tidak Ditemukan</p>
        <p class="text-gray-600 mb-8">Halaman yang Anda cari mungkin telah dihapus atau URL-nya salah.</p>
        <a href="/" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Kembali ke
            Beranda</a>
    </div>
</body>

</html>