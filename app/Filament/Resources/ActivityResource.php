<?php

namespace App\Filament\Resources;

use Filament\Notifications\Notification;
use App\Models\Task;
use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('task')
                    ->relationship('tasks','name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('new task')
                        ])
                    ->required()
                    ->columnSpan(1), 
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('from_date')
                    ->readOnly()
                    ->default(now())
                    ->columnSpan(1),    
                Forms\Components\DatePicker::make('to_date') 
                     ->columnSpan(1),  
                Forms\Components\RichEditor::make('comment')
                    ->columnSpanFull(),    
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('task.name') /// poruqe no anda!!!!!!!!!!!!
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()                   
                    ->searchable()
                    ->sortable()
                    ->label('completed')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('from_date')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('to_date')
                    ->dateTime('d-m-Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),  

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\BulkAction::make('activity completed')
                    ->icon('heroicon-o-clock')
                    ->action(function ($records) {
                        $newDate = now();  
                        foreach ($records as $record) {
                            $record->update([
                                'to_date' => $newDate,
                                'status' => True,
                            ]);
                            }
                        Notification::make()
                            ->title('activity completed succeed')
                            ->success()
                            ->send();
                        })
                    ->deselectRecordsAfterCompletion() 
                    ->action(fn (Activity $record) => $record->save())
                    ->requiresConfirmation()
                    ->modalHeading('Ending activity')
                    ->modalDescription('Are you sure you would like to do this?, This cannot be undone.')
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
