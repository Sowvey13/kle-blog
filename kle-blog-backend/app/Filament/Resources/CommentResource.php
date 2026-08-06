<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Yorumlar';

    protected static ?string $pluralModelLabel = 'Yorumlar';

    protected static ?string $modelLabel = 'Yorum';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Yorum Detayları')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('post_id')
                                    ->relationship('post', 'title')
                                    ->required()
                                    ->label('Yazı')
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->label('Yorumu Yapan Kullanıcı')
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->label('Yorum İçeriği'),

                        Forms\Components\Toggle::make('is_approved')
                            ->required()
                            ->label('Yorumu Sitede Yayınla (Onayla)')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable()
                    ->label('Kullanıcı'),
                Tables\Columns\TextColumn::make('post.title')
                    ->limit(30)
                    ->sortable()
                    ->searchable()
                    ->label('Yazı Başlığı'),
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->searchable()
                    ->label('Yorum'),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->sortable()
                    ->label('Onay Durumu'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Tarih'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_approved')
                    ->query(fn ($query) => $query->where('is_approved', true))
                    ->label('Sadece Onaylılar'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
