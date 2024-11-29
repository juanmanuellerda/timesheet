<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Exports\TaskExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\Filter;

use Filament\Forms\Components\Tabs;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;

use Filament\Resources\Actions\BulkAction;
use Filament\Forms\Components\Button;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Filament\Infolists\Components;

use App\Models\Task;
use BladeUI\Icons\Components\Icon;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Composer;
use OpenSpout\Reader\Common\ColumnWidth;
use Ramsey\Uuid\Type\Integer;

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
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Task')
                            ->schema([
                                Forms\Components\TextInput::make('external_id') //campos del formulario de Tasks
                                    ->maxLength(30)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(5), 
                                Forms\Components\TextInput::make('duration')
                                    ->required()
                                    ->label('Estimated [h]')
                                    ->columnSpan(1),    
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
                                    ->columnSpan(3), 
                                Forms\Components\Select::make('user')
                                    ->relationship('users','name')
                                    ->default([auth()->user()->id]) 
                                    ->multiple()
                                    ->preload()                   
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('new task')
                                    ])
                                    ->columnSpan(3), 
                                Forms\Components\DateTimePicker::make('date')
                                    ->default(now())
                                    ->seconds(false)
                                    ->columnSpan(2)
                                    ->readOnly(),                                     
                            ])->columns(8),
                        Tabs\Tab::make('More detail')
                            ->schema([
                                Forms\Components\RichEditor::make('comment')
                                ->columnSpanFull(),
                                Forms\Components\FileUpload::make('file')
                                ->multiple()
                                ->directory('attachments')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->downloadable()
                                ->openable()
                                ->label('attachments')
                                ->columnSpanFull(),
                            ]),         
                    ])->activeTab(1) 
                ])->columns(1);        
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
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('activities.description')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 

                Tables\Columns\TextColumn::make('activities_sum_duration')
                    ->sum('activities','duration')
                    ->toggleable(isToggledHiddenByDefault: false),    

                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Estimated [h]')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('duration_activities')
                    ->searchable()
                    ->sortable()
                    ->summarize(
                        Sum::make()    
                    )
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('users.name')
                    ->searchable()
                    ->sortable()
                    ->label('Assigned to')
                    ->toggleable(isToggledHiddenByDefault: true), 
            ])->defaultSort('created_at', 'desc')
            
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
                //Tables\Actions\DeleteAction::make(),
            ], position: ActionsPosition::BeforeColumns)

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),                         
                    Tables\Actions\BulkAction::make('unde complete')
                        ->icon('heroicon-o-clock')
                        ->requiresConfirmation()
                        ->modalHeading('xxxx xxxxxx')
                        ->modalDescription('Are you sure you would like to do this?.')
                        ->action(function ($records) {
                            // dd(auth()->user());

                            $newDate = now();  
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => false,
                                ]);
                                }
                            Notification::make()
                                ->title('task uncompleted succeed')
                                ->success()
                                ->send();
                            })
                        ->deselectRecordsAfterCompletion(),     
                    ExportBulkAction::make()
                        ->exporter(TaskExporter::class)   
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {

        return $infolist
            ->schema([  
                Components\Section::make()->schema([
                    Components\Grid::make(4)->schema([
                        TextEntry::make('external_id'),
                        TextEntry::make('name'),
                        IconEntry::make('status')
                            ->icon(fn (int $state): string => match ($state) {
                                0 => 'heroicon-o-clock',
                                1 => 'heroicon-o-clock',
                            })
                            ->color(fn (int $state): string => match ($state) {
                                    1 => 'success',            
                                    default => 'gray',
                            }),
                        TextEntry::make('project.name'),
                        TextEntry::make('date'),
                        TextEntry::make('duration'),
                        
                        ]),
                        TextEntry::make('activities.description'),
                        TextEntry::make('comment'),

                ])
            ]);
    }
    
    
    public static function getRelations(): array
    {
        return [
            //RelationManagers\UserRelationManager::class,
            //RelationManagers\ProjectRelationManager::class,
            RelationManagers\ActivityRelationManager::class,
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