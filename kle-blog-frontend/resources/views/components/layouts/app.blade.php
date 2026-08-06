<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'KLE Blog' }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js CDN (Hamburger Menü İçin) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col justify-between">
    <div x-data="{ open: false }" class="w-full">
        <!-- Main Navbar -->
        <header class="bg-white border-b border-slate-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-black text-slate-900 tracking-tight">
                            KLE<span class="text-indigo-600">Blog</span>
                        </a>
                    </div>

                    <!-- Desktop Menu -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Ana Sayfa</a>

                        @if(session()->has('user') || session()->has('user_data'))
                            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">
                                👋 {{ session('user.name') ?? session('user_data.name') ?? 'Profilim' }}
                            </a>
                            <a href="{{ route('posts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition shadow-md shadow-indigo-100">
                                Yazı Ekle
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-700 transition">
                                    Çıkış Yap
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Giriş Yap</a>
                            <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition shadow-md shadow-indigo-100">
                                Kayıt Ol
                            </a>
                        @endif
                    </nav>

                    <!-- Hamburger Button (Mobile) -->
                    <div class="flex items-center md:hidden">
                        <button @click="open = !open" class="text-slate-600 hover:text-slate-900 focus:outline-none p-2 rounded-lg">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div x-show="open" x-transition class="md:hidden border-b border-slate-100 bg-white px-4 pt-2 pb-4 space-y-3">
                <a href="{{ route('home') }}" class="block text-base font-semibold text-slate-700 hover:text-indigo-600 py-1">Ana Sayfa</a>

                @if(session()->has('user') || session()->has('user_data'))
                    <a href="{{ route('dashboard') }}" class="block text-base font-semibold text-indigo-600 py-1">
                        👋 {{ session('user.name') ?? session('user_data.name') ?? 'Profilim' }} (Kullanıcı Paneli)
                    </a>
                    <a href="{{ route('posts.create') }}" class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition my-2">
                        Yazı Ekle
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-base font-semibold text-rose-500 hover:text-rose-700 py-1">
                            Çıkış Yap
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-base font-semibold text-slate-700 hover:text-indigo-600 py-1">Giriş Yap</a>
                    <a href="{{ route('register') }}" class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition my-2">
                        Kayıt Ol
                    </a>
                @endif
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="w-full">
            {{ $slot }}
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} KLE Blog. Tüm hakları saklıdır.
    </footer>

    @livewireScripts
</body>
</html>