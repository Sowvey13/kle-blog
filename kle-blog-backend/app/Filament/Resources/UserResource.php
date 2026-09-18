<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\DeleteAction as PageDeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Sistem Yönetimi';

    protected static ?string $navigationLabel = 'Kullanıcılar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ad Soyad')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('E-posta Adresi')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options([
                        UserRole::USER->value => 'Standart Kullanıcı',
                        UserRole::ADMIN->value => 'Yönetici (Admin)',
                    ])
                    ->required()
                    ->default(UserRole::USER->value),

                Forms\Components\TextInput::make('password')
                    ->label('Şifre')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof UserRole ? $state->value : (string) $state) {
                        'admin' => 'danger',
                        default => 'info',
                    })
                    ->formatStateUsing(fn ($state) => ($state instanceof UserRole ? $state->value : (string) $state) === 'admin' ? 'Yönetici' : 'Kullanıcı'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kayıt Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role Göre Filtrele')
                    ->options([
                        UserRole::USER->value => 'Kullanıcı',
                        UserRole::ADMIN->value => 'Yönetici',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                TableDeleteAction::make()
                    ->before(fn (TableDeleteAction $action, User $record) => self::haltIfLastAdminDeletion($action, $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records): void {
                            if (! User::deletionWouldRemoveLastAdmin($records)) {
                                return;
                            }

                            self::notifyLastAdminRequired();
                            $action->cancel();
                        }),
                ]),
            ]);
    }

    public static function haltIfLastAdminDeletion(TableDeleteAction|PageDeleteAction $action, User $record): void
    {
        if (! $record->isLastAdmin()) {
            return;
        }

        self::notifyLastAdminRequired();
        $action->cancel();
    }

    public static function notifyLastAdminRequired(): void
    {
        Notification::make()
            ->title(User::LAST_ADMIN_MESSAGE)
            ->danger()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
