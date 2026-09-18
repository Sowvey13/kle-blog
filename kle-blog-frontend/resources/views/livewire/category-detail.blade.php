<div class="max-w-6xl mx-auto px-4 py-12">
    @if($errorMessage)
        <div class="max-w-xl mx-auto text-center py-16">
            <p class="text-red-600 font-semibold">{{ $errorMessage }}</p>
            <a href="{{ route('home') }}" class="inline-block mt-4 text-sm font-bold text-indigo-600 hover:text-indigo-800">Ana sayfaya dön</a>
        </div>
    @else
        <div class="mb-10 text-center">
            <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Kategori</span>
            <h1 class="text-4xl font-black text-slate-900 mt-3">{{ $category['name'] ?? 'Kategori' }}</h1>
            <p class="text-slate-500 text-sm mt-2">{{ $category['description'] ?? 'Bu kategoriye ait yayınlanmış blog yazıları.' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($posts as $post)
                @continue(! is_array($post) || empty($post['slug']))
                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition">
                    <span class="text-xs text-slate-400 block mb-2">{{ $post['created_at'] ?? '' }}</span>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $post['title'] ?? 'Yazı' }}</h3>
                    <p class="text-xs text-slate-500 line-clamp-3 mb-4">{{ strip_tags($post['content'] ?? '') }}</p>
                    <a href="{{ route('posts.show', $post['slug']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                        Yazıyı Oku &rarr;
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-slate-400">
                    Bu kategoride henüz yayınlanmış yazı bulunmuyor.
                </div>
            @endforelse
        </div>
    @endif
</div>
