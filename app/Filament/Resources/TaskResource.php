<?php

namespace App\Filament\Resources;

use App\Filament\Exports\TaskExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
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
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

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
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('duration')
                    ->required(),
                Forms\Components\RichEditor::make('comment')
                    ->columnSpanFull(),   
                Forms\Components\FileUpload::make('file')
                    ->columnSpanFull()
                    ->multiple()
                    ->directory('attachments')
                    ->visibility('public')
                    //->storeFileNamesIn('file')
                    ->preserveFilenames() //cuando funcine quitar
                    ->downloadable()
                    ->openable()
                    ->label('attachments'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('external_id')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.name')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('task name')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Task description'),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),     
                Tables\Columns\TextColumn::make('duration')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                Tables\Columns\TextColumn::make('users.name')
                    ->searchable()
                    ->sortable()
                    ->label('Assigned to')
                    ->toggleable(isToggledHiddenByDefault: true), 
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('users')
                    ->relationship('users','name')
                    ->label('Usuarios'),
                Tables\Filters\SelectFilter::make('projects')
                    ->relationship('project','name')
                    ->label('Proyectos'),              
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(TaskExporter::class)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(TaskExporter::class)
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
