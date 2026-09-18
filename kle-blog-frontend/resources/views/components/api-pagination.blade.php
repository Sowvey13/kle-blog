@props(['pagination' => []])

@php
    $current = (int) ($pagination['current_page'] ?? 1);
    $last = (int) ($pagination['last_page'] ?? 1);
    $links = is_array($pagination['links'] ?? null) ? $pagination['links'] : [];
@endphp

@if($last > 1)
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Sayfalama">
        <button
            type="button"
            wire:click="previousPage"
            @disabled($current <= 1)
            class="px-4 py-2 text-sm font-semibold rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        >
            Önceki
        </button>

        @forelse($links as $link)
            @php
                $label = html_entity_decode(strip_tags((string) ($link['label'] ?? '')));
                $page = $link['page'] ?? (is_numeric($label) ? (int) $label : null);
            @endphp

            @continue($page === null)

            <button
                type="button"
                wire:click="gotoPage({{ $page }})"
                wire:key="page-{{ $page }}"
                class="min-w-10 px-3 py-2 text-sm font-semibold rounded-xl border transition {{ (int) ($link['active'] ?? false) || $page === $current ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
            >
                {{ $label }}
            </button>
        @empty
            @for($page = 1; $page <= $last; $page++)
                <button
                    type="button"
                    wire:click="gotoPage({{ $page }})"
                    wire:key="page-{{ $page }}"
                    class="min-w-10 px-3 py-2 text-sm font-semibold rounded-xl border transition {{ $page === $current ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
                >
                    {{ $page }}
                </button>
            @endfor
        @endforelse

        <button
            type="button"
            wire:click="nextPage"
            @disabled($current >= $last)
            class="px-4 py-2 text-sm font-semibold rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        >
            Sonraki
        </button>
    </nav>
@endif
