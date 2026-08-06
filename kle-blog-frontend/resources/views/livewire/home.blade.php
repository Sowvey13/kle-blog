<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-6xl font-extrabold text-gray-950 tracking-tight mb-4">
            KLE <span class="text-indigo-600">Blog</span>
        </h1>
        <p class="text-gray-500 text-lg max-w-md mx-auto">
            İlk blogunuzu oluşturun
        </p>
    </div>

    <!-- Search Input -->
    <div class="max-w-md mx-auto mb-6">
        <div class="relative shadow-sm rounded-2xl">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                wire:model.live="search" 
                type="text" 
                placeholder="Blog yazılarında arayın..." 
                class="block w-full pl-11 pr-4 py-3 border border-gray-200 rounded-2xl bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 text-sm"
            >
        </div>
    </div>

    <!-- Category Filter Buttons (HTML Links) -->
    <div class="flex flex-wrap justify-center gap-2 mb-12">
        <!-- Tümü Butonu -->
        <a 
            href="{{ route('home') }}"
            class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ is_null($selectedCategoryId) ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
        >
            Tümü
        </a>

        @foreach($categories as $category)
            @if(is_array($category) && isset($category['id']))
                <a 
                    href="{{ route('home', ['category_id' => $category['id']]) }}"
                    class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ (string)$selectedCategoryId === (string)$category['id'] ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    {{ $category['name'] ?? 'Kategori' }}
                </a>
            @endif
        @endforeach
    </div>

    @error('api_error')
        <div class="max-w-md mx-auto mb-6 p-4 bg-red-50 text-red-700 text-sm font-semibold rounded-2xl border border-red-100 text-center">
            {{ $message }}
        </div>
    @enderror

    <!-- Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($posts as $post)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full relative group">
                
                @if($post['can_delete_post'] ?? false)
                    <button 
                        type="button"
                        wire:click="deletePost({{ $post['id'] }})"
                        wire:confirm="Bu blog yazısını tamamen silmek istediğinize emin misiniz? (Tüm yorumlar da silinecektir)"
                        class="absolute top-4 right-4 bg-white/90 hover:bg-red-50 text-gray-400 hover:text-red-600 p-2 rounded-xl border border-gray-100 shadow-sm transition-all duration-200 z-10"
                        title="Yazıyı Sil"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                @endif

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-semibold uppercase tracking-wider">
                            {{ $post['category']['name'] ?? 'Genel' }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($post['created_at'])->format('d M Y') }}
                        </span>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-indigo-600 transition-colors">
                        <a href="{{ route('posts.show', $post['slug']) }}">
                            {{ $post['title'] }}
                        </a>
                    </h3>

                    <p class="text-gray-600 text-sm leading-relaxed mb-6 flex-grow">
                        {{ \Illuminate\Support\Str::limit($post['content'], 120) }}
                    </p>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-auto">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                {{ substr($post['user']['name'] ?? 'B', 0, 1) }}
                            </div>
                            <span class="text-sm font-semibold text-gray-700">
                                {{ $post['user']['name'] ?? 'Yazar' }}
                            </span>
                        </div>

                        <a href="{{ route('posts.show', $post['slug']) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                            Oku →
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">Henüz eklenmiş bir blog yazısı bulunamadı.</p>
            </div>
        @endforelse
    </div>
</div>