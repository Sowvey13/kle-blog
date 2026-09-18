<div class="max-w-7xl mx-auto px-4 py-12">
    
    @if(session()->has('success'))
        <div class="max-w-4xl mx-auto mb-8 p-4 bg-emerald-50 text-emerald-800 text-sm font-semibold rounded-2xl border border-emerald-100 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-6xl font-extrabold text-gray-950 tracking-tight mb-4">
            KLE <span class="text-indigo-600">Blog</span>
        </h1>
        <p class="text-gray-500 text-lg max-w-md mx-auto">
            İlk blogunuzu oluşturun
        </p>
    </div>

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

    <div class="flex flex-wrap justify-center gap-2 mb-12">
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($posts as $post)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full relative group">
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-semibold uppercase tracking-wider">
                            {{ $post['category']['name'] ?? 'Genel' }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ ! empty($post['created_at']) ? \Carbon\Carbon::parse($post['created_at'])->format('d M Y') : '' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-indigo-600 transition-colors">
                        <a href="{{ route('posts.show', $post['slug']) }}">
                            {{ $post['title'] }}
                        </a>
                    </h3>

                    <p class="text-gray-600 text-sm leading-relaxed mb-6 flex-grow">
                        {{ \Illuminate\Support\Str::limit($post['content'] ?? '', 120) }}
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

    <x-api-pagination :pagination="$pagination" />
</div>
