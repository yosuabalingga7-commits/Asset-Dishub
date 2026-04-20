<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LINTAS - Dishub KBB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            overflow: hidden;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .input-gradient {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .input-gradient:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        /* Background Waves Animation */
        .waves {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40vh;
            margin-bottom: -7px;
            min-height: 100px;
            max-height: 150px;
            z-index: -1;
        }

        .parallax > use {
            animation: move-forever 25s cubic-bezier(.55, .5, .45, .5) infinite;
        }
        .parallax > use:nth-child(1) { animation-delay: -2s; animation-duration: 7s; }
        .parallax > use:nth-child(2) { animation-delay: -3s; animation-duration: 10s; }
        .parallax > use:nth-child(3) { animation-delay: -4s; animation-duration: 13s; }
        .parallax > use:nth-child(4) { animation-delay: -5s; animation-duration: 20s; }

        @keyframes move-forever {
            0% { transform: translate3d(-90px, 0, 0); }
            100% { transform: translate3d(85px, 0, 0); }
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(59, 130, 246, 0.1);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -2;
            animation: blob-move 20s infinite alternate;
        }

        @keyframes blob-move {
            from { transform: translate(-10%, -10%); }
            to { transform: translate(20%, 20%); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative">

    {{-- Background Elements --}}
    <div class="blob top-0 left-0"></div>
    <div class="blob bottom-0 right-0" style="background: rgba(99, 102, 241, 0.05); animation-delay: -5s;"></div>

    {{-- Animated Waves --}}
    <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
    viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
        <defs>
            <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
        </defs>
        <g class="parallax">
            <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(59, 130, 246, 0.05)" />
            <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(59, 130, 246, 0.1)" />
            <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(59, 130, 246, 0.15)" />
            <use xlink:href="#gentle-wave" x="48" y="7" fill="rgba(59, 130, 246, 0.2)" />
        </g>
    </svg>

    <div class="max-w-[450px] w-full glass-card rounded-[2.5rem] overflow-hidden relative z-10">
        <div class="p-10">
            {{-- Link ke Landing Page --}}
            <div class="absolute top-6 right-8">
                {{-- Diupdate ke route('landing') agar tepat sasaran --}}
                <a href="{{ route('landing') }}" class="text-blue-400 hover:text-blue-300 transition-all flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest group">
                    Beranda <i class="fas fa-external-link-alt group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-3xl mb-6 shadow-2xl rotate-3 transform transition hover:rotate-0 duration-500">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight mb-2">LINTAS</h1>
                <p class="text-blue-300/80 text-xs font-semibold uppercase tracking-[0.2em]">Layanan Inventaris & Tata Aset Sistem</p>
            </div>

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/50 text-red-200 text-sm rounded-2xl backdrop-blur-md">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-blue-200 mb-2 ml-1">Identitas NIP</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-400 group-focus-within:text-blue-300">
                            <i class="fas fa-id-badge text-lg"></i>
                        </span>
                        <input type="text" name="nip" value="{{ old('nip') }}" required autofocus
                            class="input-gradient block w-full pl-12 pr-4 py-4 rounded-2xl outline-none text-sm placeholder-gray-500"
                            placeholder="Masukkan NIP Anda">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2 ml-1">
                        <label class="text-sm font-medium text-blue-200">Security Key</label>
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-400 group-focus-within:text-blue-300">
                            <i class="fas fa-fingerprint text-lg"></i>
                        </span>
                        <input type="password" name="password" required
                            class="input-gradient block w-full pl-12 pr-4 py-4 rounded-2xl outline-none text-sm placeholder-gray-500"
                            placeholder="••••••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="hidden peer">
                        <div class="w-5 h-5 border-2 border-blue-500 rounded-md flex items-center justify-center peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all">
                            <i class="fas fa-check text-[10px] text-white hidden peer-checked:block"></i>
                        </div>
                        <span class="ml-3 text-sm text-blue-200/70">Ingat Sesi Saya</span>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 px-6 rounded-2xl shadow-[0_10px_20px_-5px_rgba(59,130,246,0.5)] transform active:scale-[0.98] transition-all duration-200 uppercase tracking-widest text-xs flex items-center justify-center">
                    Otentikasi Masuk <i class="fas fa-arrow-right ml-3"></i>
                </button>
            </form>
        </div>

        <div class="bg-white/5 p-6 text-center border-t border-white/5">
            <p class="text-[10px] text-blue-300/40 uppercase font-bold tracking-[0.3em]">
                &copy; 2026 DISHUB KABUPATEN BANDUNG BARAT
            </p>
        </div>
    </div>

</body>
</html>