<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-s-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('external_id') //campos de Tareas
                    ->maxLength(30),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('comment')
                    ->required()
                    ->columnSpanFull('full'),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('duration')
                    ->required(),
                Forms\Components\Select::make('user')
                    ->relationship('users','name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                    //->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('external_id'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('comment'),
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('duration'),
                Tables\Columns\TextColumn::make('users.name')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('users','name'),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
