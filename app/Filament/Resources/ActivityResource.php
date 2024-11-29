<?php



namespace App\Filament\Resources;

use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\Filter;
use App\Filament\Resources\ActivityResource\Pages;


use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\Task;



use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\select;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('end_at',null)->count();
    }

    public static function form(Form $form): Form
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
                        ->seconds(false)
                        ->readOnly()
                        ->Hidden(),
                    Forms\Components\Select::make('user')
                        ->relationship('users','name')
                        //->default([auth()->user()->id]) 
                        ->multiple()
                        ->preload(),                   
                    ])
                    ->required()
                    ->columnSpan(3),
                    //->columnSpan(1), 
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255)
                //     ->hidden(),
                Forms\Components\DateTimePicker::make('start_at')
                    ->readOnly()
                    ->seconds(false)
                    ->default(now())
                    ->columnSpan(1),      
                Forms\Components\DateTimePicker::make('end_at')
                    ->seconds(false)  
                    ->columnSpan(1)
                    ->readOnly()
                    ->hidden(),
                Forms\Components\TextInput::make('duration')
                    //->label('Activity time [min]')
                    ->readOnly()
                    ->hidden()
                    ->default(fn ($record) => $record ? $record->date_difference : null), // Usando el accesor       
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->columnSpanFull(),    
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('task.name') 
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('isCompleted')
                    ->boolean()                   
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime('d-m-Y H:i')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime('d-m-Y H:i')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('duration')
                    ->label('duration [min]')
                    ->summarize(
                        Sum::make()    
                    ),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->label('description')
                    ->toggleable(isToggledHiddenByDefault: true),  
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tasks')
                    ->relationship('task','name'),
                Filter::make('status')
                    ->query(fn (Builder $query): Builder => $query->where('status', false))
                    ->label('uncompleted'), 
                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                ],layout: FiltersLayout::Modal)

     
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\DeleteAction::make(),
                Action::make('done')
                    ->icon('heroicon-o-clock')
                    ->action(function ($record) { 
                        $newDate = now();
                        $oldDate = $record->start_at;
                        $duration = $record->duration;
                        $duration = abs($newDate->diffInMinutes($oldDate)) + $duration;
                        $task_id = $record->task_id;

                        $record->update([
                            'end_at' => $newDate,
                            'duration' => $duration,
                        ]);
                        
                        $task = Task::select('id','duration_activities')->where('id','=',$task_id)->get();
                        $totalDuration = $task->first()->duration_activities;
                        
                        // $TotalStatus = Activity::select('task_id','to_date')->where('task_id','=',$task_id)->count();
                        // $TrueStatus  = Activity::select('task_id','to_date')->where('task_id','=',$task_id)->whereNotNull('to_date')->count();
                        // if ($TotalStatus == $TrueStatus)
                        
                        $task = Task::select('id','duration_activities')->where('id',$task_id)->update(['duration_activities' => $duration + $totalDuration]);
                        
                        // $completed = !Activity::where('task_id',$task_id)->whereNull('to_date')->whereNot('id',$record->id)->ddRawSql();
                        $completed = !Activity::where('task_id',$task_id)->whereNull('end_at')->whereNot('id',$record->id)->exists();
                        
                        //$completed = Activity::whereHas('task',function($query) {$query->where('end_at');})->where('task_id','=',$task_id)->exists();
                        /*select * from `activities` where exists (select * from `tasks` where `activities`.`task_id` = `tasks`.`id` and `to_date` is null) and `task_id` = 1*/

                        
                        if($completed)   
                            {   
                                $task = Task::select('id','status')->where('id',$task_id)->update(['status' => true]);
                            }                                 
                        Notification::make()
                            ->title('activity completed succeed')
                            ->success()
                            ->send();
                    }) 
 
                    ->deselectRecordsAfterCompletion() 
                    ->requiresConfirmation()
                    ->modalHeading('Ending activity')
                    ->modalDescription('Are you sure you would like to do this?, This cannot be undone.')
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
                        foreach ($records as $record) {
                            $newData = now();
                            $record->update([
                                'start_at' => $newData,
                                'end_at' => null,
                            ]);
                        $task_id = $record->task_id;    
                        $task = Task::select('id','status')->where('id',$task_id)->update(['status' => false]);
                        }
                        Notification::make()
                            ->title('Activity uncompleted succeed')
                            ->success()
                            ->send();
                        })
                    ->deselectRecordsAfterCompletion(),  
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
