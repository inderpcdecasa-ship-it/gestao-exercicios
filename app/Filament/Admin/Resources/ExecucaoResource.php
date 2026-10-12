<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExecucaoResource\Pages;
use App\Models\Execucao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExecucaoResource extends Resource
{
    protected static ?string $model = Execucao::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sessao_id')
                    ->relationship('sessaoTreino', 'data_treino')
                    ->required()
                    ->label('Sessão de Treino'),
                Forms\Components\Select::make('exercicio_id')
                    ->relationship('exercicio', 'nome')
                    ->required()
                    ->label('Exercício'),
                Forms\Components\TextInput::make('serie')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('repeticoes_feitas')
                    ->required()
                    ->numeric()
                    ->label('Repetições Feitas'),
                Forms\Components\TextInput::make('peso_utilizado')
                    ->required()
                    ->numeric()
                    ->prefix('kg')
                    ->label('Peso Utilizado'),
                Forms\Components\Toggle::make('concluido')
                    ->required()
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sessaoTreino.data_treino')
                    ->date()
                    ->sortable()
                    ->label('Data do Treino'),
                Tables\Columns\TextColumn::make('exercicio.nome')
                    ->searchable()
                    ->sortable()
                    ->label('Exercício'),
                Tables\Columns\TextColumn::make('serie')
                    ->numeric()
                    ->sortable()
                    ->label('Série'),
                Tables\Columns\TextColumn::make('repeticoes_feitas')
                    ->numeric()
                    ->sortable()
                    ->label('Reps'),
                Tables\Columns\TextColumn::make('peso_utilizado')
                    ->numeric()
                    ->sortable()
                    ->label('Peso (kg)'),
                Tables\Columns\IconColumn::make('concluido')
                    ->boolean()
                    ->label('Concluído'),
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
            'index' => Pages\ListExecucaos::route('/'),
            'create' => Pages\CreateExecucao::route('/create'),
            'edit' => Pages\EditExecucao::route('/{record}/edit'),
        ];
    }
}