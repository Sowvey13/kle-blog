<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Yazılar';
    protected static ?string $pluralModelLabel = 'Yazılar';
    protected static ?string $modelLabel = 'Yazı';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Yazı Bilgileri')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Yazı Başlığı')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true)
                                    ->label('Slug / Link')
                                    ->readonly() // disabled() yerine readonly() yaptık ki formla beraber veritabanına gönderilebilsin kanka.
                                    ->dehydrated(true), // Değerin veritabanına kesin gitmesini garanti ediyoruz.
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->label('Kategori')
                                    ->preload() // Kategorileri önceden yükler, hata payını sıfırlar.
                                    ->searchable(),

                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->label('Yazar / Kullanıcı')
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('posts')
                            ->label('Öne Çıkan Görsel'),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->label('Yazı İçeriği'),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->label('Yazıyı Sitede Yayınla (Onayla)')
                            ->default(false), // Varsayılan değer ekleyerek boş gitmesini engelledik.
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Görsel'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Başlık'),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('Yazar'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Durum / Onay'),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->label('Okunma'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Tarih'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Sadece Onaylılar'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}