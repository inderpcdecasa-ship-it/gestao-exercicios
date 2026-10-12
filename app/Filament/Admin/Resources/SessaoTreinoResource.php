<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SessaoTreinoResource\Pages;
use App\Filament\Admin\Resources\SessaoTreinoResource\RelationManagers;
use App\Models\SessaoTreino;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessaoTreinoResource extends Resource
{
    protected static ?string $model = SessaoTreino::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('data_treino')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Toggle::make('concluido')
                    ->required()
                    ->default(false),
                Forms\Components\DateTimePicker::make('inicio')
                    ->default(now()),
                Forms\Components\DateTimePicker::make('fim'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data_treino')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('concluido')
                    ->boolean(),
                Tables\Columns\TextColumn::make('inicio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fim')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSessaoTreinos::route('/'),
            'create' => Pages\CreateSessaoTreino::route('/create'),
            'edit' => Pages\EditSessaoTreino::route('/{record}/edit'),
        ];
    }
}