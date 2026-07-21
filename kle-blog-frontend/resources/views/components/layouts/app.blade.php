<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'KLE Blog' }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-indigo-600 tracking-wider">
                        KLE<span class="text-gray-800">BLOG</span>
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Ana Sayfa</a>
                    
                    @if(session()->has('user_token'))
                        <a href="/posts/create" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-100 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Yazı Ekle
                        </a>
                       
                        <span class="text-sm font-bold text-gray-800">👋 {{ session('user_data')['name'] }}</span>
                        <a href="/logout" class="text-sm font-medium text-red-500 hover:text-red-700">Çıkış Yap</a>
                    @else
                        <a href="/login" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Giriş Yap</a>
                        <a href="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition-all">Kayıt Ol</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

  
    <main class="py-10 block">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>