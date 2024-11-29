<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('task_id')
                    ->relationship('task','name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('external_id')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project','name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DateTimePicker::make('date')
                            ->default(now())
                            ->readOnly()
                            ->Hidden(),
                        Forms\Components\Select::make('user')
                            ->relationship('users','name')
                            //->default([auth()->user()->id]) 
                            ->multiple()
                            ->preload(),                   
                    ])
                    ->required()
                    ->columnSpan(5),
                Forms\Components\DateTimePicker::make('start_at')
                    ->readOnly()
                    ->default(now())
                    ->seconds(false)
                    ->columnSpan(2)
                    ->hiddenOn('view'),       
                Forms\Components\DateTimePicker::make('end_at')
                    ->readOnly()
                    ->seconds(false)    
                    ->columnSpan(1)
                    ->hidden(),
                Forms\Components\TextInput::make('duration')
                    //->label('Activity time [min]')
                    ->readOnly()
                    ->columnSpan(2)
                    //->hidden()
                    ->default(fn ($record) => $record ? $record->date_difference : null), // Usando el accesor       
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->columnSpanFull(),    
            ])->columns(9);  
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\IconColumn::make('isCompleted')
                    ->boolean()                   
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
