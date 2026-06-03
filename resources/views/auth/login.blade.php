<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LINTAS - Dishub KBB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }

        .bg-login {
            background: linear-gradient(rgba(142, 141, 147, 0.8), rgba(32, 49, 88, 0.8)), 
                        url("{{ asset('img/kegiatan_dishub_kbb2.png') }}");
            background-size: cover;
            background-position: center;
        }

        .glass-feature {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-feature:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }

        .input-custom {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .input-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: #2563eb;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .text-lin { color: #ffffff; }
        .text-tas { color: #3b82f6; }
        .feature-title { color: #ffffff; }
        .feature-desc { color: #cbd5e1; }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen w-full overflow-hidden">

    <div class="hidden md:flex md:w-3/5 bg-login relative items-center justify-center p-8 h-full">
        <div class="relative z-10 w-full max-w-xl text-center flex flex-col items-center">
            
            <div class="inline-block mb-6">
                <img src="{{ asset('img/logodishub.png') }}" alt="Logo Dishub" 
                     class="w-60 h-60 object-contain transform transition hover:scale-105 duration-500">
            </div>

            <h2 class="text-lg font-light mb-1 tracking-wide text-slate-300">Selamat Datang di</h2>
            <h1 class="text-4xl font-extrabold mb-2 tracking-tight text-white">
                Aplikasi <span class="text-lin">LIN</span><span class="text-tas">TAS</span>
            </h1>
            <p class="text-[10px] text-slate-300 font-bold mb-8 uppercase tracking-[0.3em]">Layanan Inventaris & Sistem Tata Aset</p>

            <div class="grid grid-cols-2 gap-4 text-left w-full px-4">
                <div class="glass-feature p-3 rounded-2xl">
                    <i class="fas fa-chart-line text-md text-blue-400 mb-1"></i>
                    <h3 class="text-[11px] font-bold mb-0.5 feature-title">Monitoring Real-Time</h3>
                    <p class="text-[9px] feature-desc leading-tight">Pantau kondisi aset daerah secara langsung.</p>
                </div>
                <div class="glass-feature p-3 rounded-2xl">
                    <i class="fas fa-map-marked-alt text-md text-blue-400 mb-1"></i>
                    <h3 class="text-[11px] font-bold mb-0.5 feature-title">Tracking Lokasi</h3>
                    <p class="text-[9px] feature-desc leading-tight">Pemetaan aset berbasis GIS.</p>
                </div>
                <div class="glass-feature p-3 rounded-2xl">
                    <i class="fas fa-check-circle text-md text-blue-400 mb-1"></i>
                    <h3 class="text-[11px] font-bold mb-0.5 feature-title">Validasi Digital</h3>
                    <p class="text-[9px] feature-desc leading-tight">Proses pelaporan transparan.</p>
                </div>
                <div class="glass-feature p-3 rounded-2xl">
                    <i class="fas fa-shield-alt text-md text-blue-400 mb-1"></i>
                    <h3 class="text-[11px] font-bold mb-0.5 feature-title">Data Terproteksi</h3>
                    <p class="text-[9px] feature-desc leading-tight">Keamanan data terjamin.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-2/5 flex flex-col items-center justify-center p-8 bg-white h-full relative">
        <div class="w-full max-w-sm">
            
            <div class="mb-8">
                <a href="{{ route('landing') }}" class="inline-flex items-center text-slate-400 hover:text-blue-600 transition-colors text-[10px] font-bold uppercase tracking-widest mb-4 group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Beranda
                </a>
                <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Login Aplikasi</h2>
                <p class="text-sm text-slate-500 font-medium">Silakan masuk menggunakan akun Anda</p>
            </div>

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-[10px] rounded-r-lg">
                <ul class="font-semibold">
                    @foreach($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-700 ml-1 uppercase tracking-wider">Username / NIP</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" required autofocus
                        class="input-custom block w-full px-4 py-3 rounded-xl outline-none text-sm font-semibold placeholder-slate-400"
                        placeholder="Masukkan NIP atau Email">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-700 ml-1 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="input-custom block w-full px-4 py-3 rounded-xl outline-none text-sm font-semibold placeholder-slate-400"
                            placeholder="Masukkan Password">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-blue-600">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                    class="btn-primary w-full text-white font-bold py-3 px-6 rounded-xl shadow-lg uppercase tracking-widest text-[10px] flex items-center justify-center group mt-2">
                    <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                </button>
            </form>

            <div class="absolute bottom-8 left-8 right-8 text-center md:text-left">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">
                    &copy; 2026 DISHUB KABUPATEN BANDUNG BARAT
                </p>
                <p class="text-[9px] text-slate-300 mt-0.5 italic font-medium">Optimalisasi Manajemen Aset Daerah secara Digital</p>
            </div>

        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>