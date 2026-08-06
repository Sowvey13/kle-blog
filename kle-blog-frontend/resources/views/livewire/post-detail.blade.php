<div class="max-w-3xl mx-auto px-4 py-12">
    <!-- Geri Dön Butonu -->
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors mb-8">
        ← Anasayfaya Geri Dön
    </a>

    @if ($errorMessage || $errors->has('api_error'))
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
            {{ $errorMessage ?: $errors->first('api_error') }}
        </div>
    @endif

    @if($post)
        <article class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-8 md:p-12 mb-12">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold uppercase tracking-wider">
                        {{ $post['category']['name'] ?? 'Genel' }}
                    </span>
                    <span class="text-sm text-gray-400">
                        {{ !empty($post['created_at']) ? \Carbon\Carbon::parse($post['created_at'])->format('d M Y') : '' }}
                    </span>
                </div>

                @php
                    $currentUser = session('user') ?? session('user_data');
                    $isOwner = $currentUser && isset($post['user']['id']) && $currentUser['id'] === $post['user']['id'];
                    $isAdmin = $currentUser && isset($currentUser['role']) && $currentUser['role'] === 'admin';
                @endphp

                @if($isOwner || $isAdmin)
                    <button 
                        wire:click="deletePost({{ $post['id'] }})"
                        wire:confirm="Bu yazıyı silmek istediğinize emin misiniz?"
                        class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                        title="Yazıyı Sil"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Yazıyı Sil
                    </button>
                @endif
            </div>

            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-950 leading-tight mb-8">
                {{ $post['title'] ?? '' }}
            </h1>

            <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed space-y-6">
                {!! nl2br(e($post['content'] ?? '')) !!}
            </div>

            <div class="flex items-center gap-3 mt-12 pt-8 border-t border-gray-100">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($post['user']['name'] ?? 'B', 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">{{ $post['user']['name'] ?? 'Yazar' }}</h4>
                    <p class="text-xs text-gray-400">İçerik Üreticisi</p>
                </div>
            </div>
        </article>

        <!-- Yorumlar Bölümü -->
        <div class="space-y-10">
            <h3 class="text-2xl font-bold text-gray-900">
                Yorumlar ({{ count($post['comments'] ?? []) }})
            </h3>

            <div class="space-y-4">
                @forelse($post['comments'] ?? [] as $comment)
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6 relative group">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold text-xs uppercase">
                                    {{ strtoupper(substr($comment['display_name'] ?? ($comment['user']['name'] ?? 'A'), 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-gray-800">{{ $comment['display_name'] ?? ($comment['user']['name'] ?? 'Anonim') }}</span>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400">
                                    {{ !empty($comment['created_at']) ? \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() : '' }}
                                </span>

                                @if(!empty($comment['can_delete']))
                                    <button 
                                        wire:click="deleteComment({{ $comment['id'] }})" 
                                        wire:confirm="Yorumu silmek istediğinize emin misiniz?"
                                        class="text-gray-400 hover:text-red-500 transition-colors ml-2"
                                        title="Yorumu Sil"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed pl-10">
                            {{ $comment['display_text'] ?? ($comment['content'] ?? '') }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <p class="text-gray-400 text-sm">Bu yazıya henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                    </div>
                @endforelse
            </div>

            <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-sm">
                @if(session()->has('user_token') && session('user_token'))
                    <h4 class="text-lg font-bold text-gray-900 mb-6">Yorum Bırakın</h4>

                    @if($successMessage)
                        <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-2xl border border-emerald-100">
                            {{ $successMessage }}
                        </div>
                    @endif

                    <form wire:submit.prevent="saveComment" class="space-y-4">
                        <div class="mb-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Yorum Yapacak Hesap:</span>
                            <span class="text-sm font-bold text-gray-800">👋 {{ session('user.name') ?? session('user_data.name') ?? 'Kullanıcı' }}</span>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Yorumunuz</label>
                            <textarea 
                                wire:model="content"
                                rows="4" 
                                placeholder="Yorumunuzu buraya yazın..." 
                                class="block w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all duration-200 text-sm"
                            ></textarea>
                            @error('content') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-md shadow-blue-100">
                                Yorumu Gönder
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-6">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-gray-800 mb-2">Tartışmaya Katılın</h4>
                        <p class="text-gray-500 text-sm max-w-sm mx-auto mb-6 leading-relaxed">
                            Bu yazıya yorum yapabilmek için lütfen bir kullanıcı hesabıyla giriş yapın.
                        </p>
                        <a href="{{ route('auth.required') }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-md shadow-blue-100">
                            Giriş Yap veya Kayıt Ol
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-100 p-12 text-center">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Yazı Bulunamadı</h2>
            <p class="text-gray-500 text-sm mb-6">Aradığınız içerik silinmiş veya erişilemiyor olabilir.</p>
            <a href="{{ route('home') }}" class="px-6 py-2.5 bg-blue-600 text-white font-bold text-sm rounded-xl hover:bg-blue-700 transition">
                Anasayfaya Dön
            </a>
        </div>
    @endif
</div>