<!-- Tambahkan ini di login.php di atas form login -->
<?php if(isset($_GET['pesan']) && $_GET['pesan'] == "logout"): ?>
    
<?php endif; ?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - Gereja Bala Keselamatan </title>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-96">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800">Korps<span class="text-orange-500"> Makassar</span></h1>
            <p class="text-gray-500 text-sm">Silakan masuk ke akun Anda</p>
        </div>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "gagal"): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded-lg text-sm mb-4 text-center">
                Username atau Password salah!
            </div>
        <?php endif; ?>

        <form action="cek_login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-orange-400 outline-none" placeholder="Masukkan username" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-orange-400 outline-none" placeholder="********" required>
            </div>
            <button type="submit" class="w-full bg-slate-800 text-white p-3 rounded-lg font-bold hover:bg-slate-700 transition">
                Masuk Sekarang
            </button>
        </form>
    </div>
</body>
</html>