<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\Project;
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
                Forms\Components\TextInput::make('external_id') //campos del formulario de Tasks
                    ->maxLength(30),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('project_id')
                    ->relationship('project','name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('new project')
                        ])
                    ->required(),
                Forms\Components\Select::make('user')
                    ->relationship('users','name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                    //->hiddenOn('edit'),
                Forms\Components\RichEditor::make('comment')
                    ->columnSpanFull()
                    ->required(),   
                Forms\Components\FileUpload::make('file')
                    ->columnSpanFull()
                    ->multiple()
                    ->directory('attachments')
                    ->visibility('public')
                    //->storeFileNamesIn('file')
                    ->preserveFilenames() //cuando funcine quitar
                    ->downloadable()
                    ->openable(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('duration')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('external_id')
                //     ->searchable()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                //Tables\Columns\TextColumn::make('file') 
                //Tables\Columns\TextColumn::make('comment'),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable(),     
                //Tables\Columns\TextColumn::make('duration'),
                //Tables\Columns\TextColumn::make('users.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('users','name')
                    ->label('Usuarios'),        
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            //'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}
