<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="mb-10">
        <h1 class="text-3xl font-extrabold text-gray-900">Kullanıcı Paneli</h1>
        <p class="text-gray-500 text-sm mt-1">Profil bilgilerinizi yönetin ve yazdığınız içerikleri takip edin.</p>
    </div>

    @if($successMessage)
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-2xl border border-emerald-100">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-6 p-4 bg-red-50 text-red-700 text-sm font-semibold rounded-2xl border border-red-100">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
        <div class="lg:col-span-1 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm h-fit">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Profil Bilgileri</h3>
            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ad Soyad</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">E-Posta</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition shadow-md shadow-blue-100">
                    Bilgileri Güncelle
                </button>
            </form>
        </div>

      
        <div class="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Yazılarım</h3>

            <div class="space-y-4">
                @forelse($myPosts as $post)
                    <div class="p-4 border border-gray-100 rounded-2xl flex items-center justify-between gap-4 hover:border-gray-200 transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($post['is_approved'] ?? false) ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ ($post['is_approved'] ?? false) ? 'Onaylandı' : 'Onay Bekliyor' }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $post['category']['name'] ?? 'Genel' }}</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm">{{ $post['title'] }}</h4>
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="deletePost({{ $post['id'] }})" wire:confirm="Bu yazıyı silmek istediğinizden emin misiniz?" class="p-2 text-gray-400 hover:text-red-600 transition" title="Sil">
                                
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-sm">
                        Henüz bir yazı oluşturmadınız.
                    </div>
                @endforelse
            </div>

            <x-api-pagination :pagination="$pagination" />
        </div>
    </div>
</div>