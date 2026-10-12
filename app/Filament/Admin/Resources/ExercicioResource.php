<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExercicioResource\Pages;
use App\Models\Exercicio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExercicioResource extends Resource
{
    protected static ?string $model = Exercicio::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(100)
                    ->label('Nome do Exercício'),

                Forms\Components\Textarea::make('descricao')
                    ->columnSpanFull()
                    ->label('Descrição / Instruções'),

                // Foto do aparelho: campo nativo do Filament.
                // Grava em storage/app/public/aparelhos (mesmo formato do Modo Treino)
                // e reduz a foto no navegador antes de enviar (bom para fotos de celular).
                Forms\Components\FileUpload::make('foto_aparelho')
                    ->label('Foto do Aparelho')
                    ->image()
                    ->disk('public')
                    ->directory('aparelhos')
                    ->visibility('public')
                    ->maxSize(10240)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->imageResizeUpscale(false)
                    ->imageEditor()
                    ->openable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('series')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(3)
                    ->label('Séries'),

                Forms\Components\TextInput::make('repeticoes')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->label('Repetições'),

                Forms\Components\TextInput::make('peso_padrao')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('kg')
                    ->label('Peso Padrão (kg)'),

                Forms\Components\Select::make('dia_semana')
                    ->required()
                    ->options([
                        'segunda' => 'Segunda-feira',
                        'terca' => 'Terça-feira',
                        'quarta' => 'Quarta-feira',
                        'quinta' => 'Quinta-feira',
                        'sexta' => 'Sexta-feira',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo',
                    ])
                    ->label('Dia da Semana'),

                Forms\Components\TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->label('Ordem de Execução'),

                Forms\Components\Toggle::make('ativo')
                    ->required()
                    ->default(true)
                    ->label('Ativo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_aparelho')
                    ->disk('public')
                    ->circular()
                    ->label('Foto'),
                Tables\Columns\TextColumn::make('ordem')
                    ->sortable()
                    ->label('Ordem'),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable()
                    ->label('Exercício'),
                Tables\Columns\TextColumn::make('dia_semana')
                    ->badge()
                    ->sortable()
                    ->label('Dia'),
                Tables\Columns\TextColumn::make('series')
                    ->numeric()
                    ->sortable()
                    ->label('Séries'),
                Tables\Columns\TextColumn::make('repeticoes')
                    ->numeric()
                    ->sortable()
                    ->label('Reps'),
                Tables\Columns\TextColumn::make('peso_padrao')
                    ->numeric()
                    ->sortable()
                    ->suffix(' kg')
                    ->label('Peso Base'),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean()
                    ->label('Ativo'),
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
            'index' => Pages\ListExercicios::route('/'),
            'create' => Pages\CreateExercicio::route('/create'),
            'edit' => Pages\EditExercicio::route('/{record}/edit'),
        ];
    }
}