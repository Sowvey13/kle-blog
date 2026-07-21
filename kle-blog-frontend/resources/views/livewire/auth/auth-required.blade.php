<div class="max-w-md mx-auto my-16 p-8 bg-white rounded-3xl border border-gray-100 shadow-sm text-center">
    
    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-3">Buraya Erişmek İçin Giriş Yapmalısınız</h2>
    <p class="text-gray-500 text-sm mb-8 leading-relaxed">
        Blog yazılarına yorum yapabilmek veya kendi yazılarınızı oluşturup yönetebilmek için bir kullanıcı hesabına ihtiyacınız var. Hemen giriş yapabilir veya saniyeler içinde yeni bir hesap oluşturabilirsiniz.
    </p>

    <div class="flex flex-col space-y-3">
        <a href="{{ route('login') }}" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm shadow-md shadow-indigo-100 transition-all">
            Giriş Yap
        </a>
        <a href="{{ route('register') }}" class="w-full py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl font-semibold text-sm border border-gray-100 transition-all">
            Hesap Oluştur (Kayıt Ol)
        </a>
        <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-gray-600 mt-2 transition-colors">
            ← Ana Sayfaya Geri Dön
        </a>
    </div>
</div>