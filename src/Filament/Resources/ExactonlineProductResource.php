<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Dashed\DashedEcommerceMontaportal\Models\MontaportalProduct;
use Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource\Pages\ListExactonlineProducts;

class ExactonlineProductResource extends Resource
{
    protected static ?string $model = MontaportalProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'E-commerce';
    protected static ?string $navigationLabel = 'Exactonline producten';
    protected static ?string $label = 'Exactonline product';
    protected static ?string $pluralLabel = 'Exactonline producten';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_synced')
                    ->label('Is gesynchroniseerd')
                    ->getStateUsing(fn ($record) => $record->exactonline_id ? true : false)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('error')
                    ->label('Foutmelding')
                    ->getStateUsing(fn ($record) => ! $record->exactonline_id ? $record->error : ''),

            ])
            ->filters([
                //
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
            'index' => ListExactonlineProducts::route('/'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
