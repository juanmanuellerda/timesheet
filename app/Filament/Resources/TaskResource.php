<?php

namespace App\Filament\Resources;

use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Exports\TaskExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\Filter;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;



use Filament\Resources\Actions\BulkAction;

use Filament\Forms\Components\Button;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;


use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use OpenSpout\Reader\Common\ColumnWidth;

use function Laravel\Prompts\select;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-s-wrench-screwdriver';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status',0)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('external_id') //campos del formulario de Tasks
                    ->maxLength(30),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2), 
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
                    ->required()
                    ->columnSpan(1), 
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->columnSpan(1),
                Forms\Components\TextInput::make('duration')
                    ->required()
                    ->label('Duration [h]')
                    ->columnSpan(1),
                Forms\Components\Toggle::make('status')
                    ->inline(false)
                    ->label('completed'),
                Forms\Components\DatePicker::make('date_completed')
                    ->disabled()
                    ->live()
                    ->columnSpan(1),        
                Forms\Components\Select::make('user')
                    ->relationship('users','name')
                    ->searchable()
                    ->multiple()
                    ->preload()                   
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('new task')
                    ])
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('comment')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file')
                    ->multiple()
                    ->directory('attachments')
                    ->visibility('public')
                    //->storeFileNamesIn('file')
                    ->preserveFilenames() //cuando funcine quitar
                    ->downloadable()
                    ->openable()
                    ->label('attachments')
                    ->columnSpanFull(),
            ])->columns(4);
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
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('task name')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()                   
                    ->searchable()
                    ->sortable()
                    ->label('completed')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning'),
                    
                Tables\Columns\TextColumn::make('date_completed')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), 
                Tables\Columns\TextColumn::make('comment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Task description'),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),     
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
                Tables\Filters\SelectFilter::make('projects')
                    ->relationship('project','name'),
                Tables\Filters\SelectFilter::make('customers')
                    ->relationship('project.customer','name'),
                Filter::make('status')
                    ->query(fn (Builder $query): Builder => $query->where('status', true))
                    ->label('completed'), 
                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_completed', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_completed', '<=', $date),
                            );
                    })
                ],layout: FiltersLayout::Modal)
                
            ->headerActions([
                // ExportAction::make()
                //     ->exporter(TaskExporter::class)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),                         
                Tables\Actions\BulkAction::make('task completed')
                        ->icon('heroicon-o-clock')
                        ->action(function ($records) {
                            $newDate = now();  
                            foreach ($records as $record) {
                                $record->update([
                                    'date_completed' => $newDate,
                                    'status' => True,
                                ]);
                                }
                            Notification::make()
                                ->title('task completed succeed')
                                ->success()
                                ->send();
                            })
                        ->deselectRecordsAfterCompletion()                               
                ]),
                   ExportBulkAction::make()->exporter(TaskExporter::class)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserRelationManager::class,
            //RelationManagers\ProjectRelationManager::class,
            //RelationManagers\CustomerRelationManager::class, //preguntar por que esto no anda!!!!!!!!!
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