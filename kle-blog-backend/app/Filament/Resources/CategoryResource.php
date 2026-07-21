<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->live(debounce: 500)
                ->afterStateUpdated(fn (string $operation, $state, \Filament\Forms\Set $set) => 
                    $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null
                ),
            \Filament\Forms\Components\TextInput::make('slug')
                ->disabled()
                ->dehydrated()
                ->required()
                ->unique(ignoreRecord: true),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('slug')
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('created_at')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->label('Oluşturulma Tarihi'),
        ])
        ->filters([
            //
        ])
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
