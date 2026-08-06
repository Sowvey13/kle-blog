<div class="w-full">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Yeni Yazı Oluştur</h1>
            <p class="mt-2 text-sm text-gray-500">Blogunda paylaşmak üzere harika bir içerik hazırla.</p>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-8">
            
            @if(session()->has('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-2xl border border-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @error('api_error')
                <div class="mb-6 p-4 bg-red-50 text-red-700 text-sm font-semibold rounded-2xl border border-red-100">
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit.prevent="savePost" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Yazı Başlığı</label>
                    <input 
                        type="text" 
                        wire:model="title"
                        placeholder="Dikkat çekici bir başlık yazın..." 
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                    >
                    @error('title') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-gray-700">Kategori</label>
                        
                        @if(session('user.role') === 'admin' || session('user_data.role') === 'admin')
                            <button 
                                type="button"
                                wire:click="toggleCategoryForm"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1"
                            >
                                {{ $showCategoryForm ? '✕ İptal Et' : '➕ Yeni Kategori Oluştur' }}
                            </button>
                        @endif
                    </div>

                    @if($categorySuccessMessage)
                        <div class="mb-3 text-xs text-emerald-600 font-semibold bg-emerald-50 p-2.5 rounded-xl border border-emerald-100">
                            {{ $categorySuccessMessage }}
                        </div>
                    @endif

                    @if($showCategoryForm && (session('user.role') === 'admin' || session('user_data.role') === 'admin'))
                        <div class="mb-4 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 flex items-end gap-3 transition-all">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-2">Yeni Kategori Adı</label>
                                <input 
                                    type="text" 
                                    wire:model="newCategoryName"
                                    placeholder="Örn: Yapay Zeka"
                                    class="block w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                @error('newCategoryName') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            <button 
                                type="button"
                                wire:click="saveCategory"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all h-[42px]"
                            >
                                Ekle
                            </button>
                        </div>
                    @endif

                    <select 
                        wire:model="category_id"
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                    >
                        <option value="">-- Kategori Seçin --</option>
                        @foreach($categories as $category)
                            @php
                                $cId = is_array($category) ? ($category['id'] ?? null) : ($category->id ?? null);
                                $cName = is_array($category) ? ($category['name'] ?? null) : ($category->name ?? null);
                            @endphp
                            @if($cId && $cName)
                                <option value="{{ $cId }}">{{ $cName }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">İçerik</label>
                    <textarea 
                        wire:model="content"
                        rows="10" 
                        placeholder="Hikayenizi buraya yazmaya başlayın..." 
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all duration-200 resize-y"
                    ></textarea>
                    @error('content') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex items-center justify-end gap-4 border-t border-gray-100">
                    <a href="{{ route('home') }}" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors">
                        İptal Et
                    </a>
                    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200">
                        Hemen Paylaş
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>