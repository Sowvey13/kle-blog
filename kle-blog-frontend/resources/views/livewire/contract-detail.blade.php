<div class="max-w-4xl mx-auto px-4 py-12">
    @if(!empty($contract))
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
            <h1 class="text-3xl font-black text-slate-900 mb-6">{{ $contract['title'] ?? 'Sözleşme' }}</h1>
            <div class="prose prose-slate max-w-none text-slate-600 text-sm leading-relaxed">
                {!! nl2br(e($contract['content'] ?? '')) !!}
            </div>
        </div>
    @else
        <div class="text-center py-12 text-slate-400">
            Sözleşme metni bulunamadı.
        </div>
    @endif
</div>