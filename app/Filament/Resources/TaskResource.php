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
                Forms\Components\TextInput::make('proyect')
                    ->columnSpanFull()
                    ->required(),    
                Forms\Components\RichEditor::make('comment')
                    ->columnSpanFull()
                    ->required(),   
                Forms\Components\FileUpload::make('url')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('external_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('proyect')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->hidden(),   
                Tables\Columns\TextColumn::make('comment')
                    ->hidden(),

                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d-m-Y')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->hidden(),
                Tables\Columns\TextColumn::make('users.name')
                    ->hidden(),
                    
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('users','name')
                    ->label('Usuarios'),
                    
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
            //RelationManagers\UserRelationManager::class,
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
