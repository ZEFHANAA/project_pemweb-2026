<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LokasiResource\Pages;
use App\Models\Lokasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class LokasiResource extends Resource
{
    protected static ?string $model = Lokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Lokasi Wisata';

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?string $modelLabel = 'Lokasi';

    protected static ?string $pluralModelLabel = 'Lokasi Wisata';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_lokasi';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_lokasi', 'kategori', 'deskripsi'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lokasi')
                            ->label('Nama Lokasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Pantai Kuta')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'Pantai'  => '🏖️ Pantai',
                                'Gunung'  => '🏔️ Gunung',
                                'Kota'    => '🏙️ Kota',
                                'Budaya'  => '🏛️ Budaya',
                                'Kuliner' => '🍜 Kuliner',
                                'Alam'    => '🌿 Alam',
                                'Lainnya' => '📍 Lainnya',
                            ])
                            ->default('Lainnya')
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Pemilik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Koordinat')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->required()
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('-6.1754'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->required()
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('106.8272'),
                    ])->columns(2),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Deskripsi singkat tentang lokasi wisata ini...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nama_lokasi')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->colors([
                        'info'    => 'Pantai',
                        'success' => 'Alam',
                        'warning' => 'Gunung',
                        'danger'  => 'Budaya',
                        'gray'    => 'Lainnya',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Lat')
                    ->numeric(decimalPlaces: 4)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('Lon')
                    ->numeric(decimalPlaces: 4)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->placeholder('Tidak ada deskripsi')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Filter Kategori')
                    ->options([
                        'Pantai'  => '🏖️ Pantai',
                        'Gunung'  => '🏔️ Gunung',
                        'Kota'    => '🏙️ Kota',
                        'Budaya'  => '🏛️ Budaya',
                        'Kuliner' => '🍜 Kuliner',
                        'Alam'    => '🌿 Alam',
                        'Lainnya' => '📍 Lainnya',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Filter Pemilik')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('gmaps')
                    ->label('Maps')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->url(fn (Lokasi $record): string =>
                        "https://www.google.com/maps?q={$record->latitude},{$record->longitude}"
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Lokasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_lokasi')
                            ->label('Nama Lokasi')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('kategori')
                            ->label('Kategori')
                            ->badge(),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Pemilik')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->date('d M Y, H:i'),
                    ])->columns(2),

                Infolists\Components\Section::make('Koordinat')
                    ->schema([
                        Infolists\Components\TextEntry::make('latitude')
                            ->label('Latitude'),
                        Infolists\Components\TextEntry::make('longitude')
                            ->label('Longitude'),
                    ])->columns(2),

                Infolists\Components\Section::make('Deskripsi')
                    ->schema([
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('')
                            ->placeholder('Tidak ada deskripsi')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLokasis::route('/'),
            'create' => Pages\CreateLokasi::route('/create'),
            'view'   => Pages\ViewLokasi::route('/{record}'),
            'edit'   => Pages\EditLokasi::route('/{record}/edit'),
        ];
    }
}
