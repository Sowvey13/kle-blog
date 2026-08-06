<div class="max-w-md mx-auto my-12 p-6 bg-white rounded-3xl border border-gray-100 shadow-sm">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Yeni Hesap Oluştur</h2>

    @if($errorMessage || session('errorMessage'))
        <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-sm font-semibold">
            {{ $errorMessage ?: session('errorMessage') }}
        </div>
    @endif

    <form action="{{ route('register.action') }}" method="POST" wire:submit.prevent="register" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Adınız Soyadınız</label>
            <input type="text" name="name" wire:model="name" value="{{ old('name') }}" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="Ahmet Yılmaz" required>
            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">E-Posta Adresiniz</label>
            <input type="email" name="email" wire:model="email" value="{{ old('email') }}" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="ornek@mail.com" required>
            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Şifreniz</label>
            <input type="password" name="password" wire:model="password" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="••••••••" required>
            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Şifre Tekrarı</label>
            <input type="password" name="password_confirmation" wire:model="password_confirmation" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="••••••••" required>
        </div>

        <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold text-sm shadow-md shadow-green-100 transition-all cursor-pointer">
            Kayıt Ol ve Başla
        </button>
    </form>
</div>