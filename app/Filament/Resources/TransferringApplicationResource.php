<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferringApplicationResource\Pages;
use App\Livewire\TVCP\ApplicationVerificationForm;
use App\Models\TVCP\VisitTransferApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TransferringApplicationResource extends Resource
{
    protected static ?string $model = VisitTransferApplication::class;

    protected static ?string $modelLabel = 'Transferring Application';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Section::make('Member Details')->columns(2)->schema([
    //                 Forms\Components\Placeholder::make('account_id')->label('VATSIM CID')->content(fn (VisitTransferApplication $record) => $record->account_id),
    //                 Forms\Components\Placeholder::make('account.full_name')->label('Full Name')->content(fn (VisitTransferApplication $record) => $record->account->full_name),
    //                 Forms\Components\Placeholder::make('account.email')->label('Email')->content(fn (VisitTransferApplication $record) => $record->account->email),
    //                 Forms\Components\Placeholder::make('account.qualification_atc.name_long')->label('Current ATC Rating')->content(fn (VisitTransferApplication $record) => $record->account->qualification_atc->name_long),
    //                 Forms\Components\Placeholder::make('account.primary_state.pivot.region')->label('Current Region')->content(fn (VisitTransferApplication $record) => $record->account->primary_state->pivot->region),
    //                 Forms\Components\Placeholder::make('account.primary_state.pivot.division')->label('Current Division')->content(fn (VisitTransferApplication $record) => $record->account->primary_state->pivot->division),
    //             ]),

    //             Forms\Components\Section::make('Application Details')->columns(2)->schema([
    //                 Forms\Components\Placeholder::make('id')->label('Application ID')->content(fn (VisitTransferApplication $record) => $record->id),
    //                 Forms\Components\Placeholder::make('status')->label('Status')->content(fn (VisitTransferApplication $record) => $record->status),
    //                 Forms\Components\Placeholder::make('created_at')->label('Submitted At')->content(fn (VisitTransferApplication $record) => $record->created_at),
    //             ]),
    //         ]);
    // }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Member Details')->schema([
                    Infolists\Components\TextEntry::make('account_id')->label('VATSIM CID'),
                    Infolists\Components\TextEntry::make('account.full_name')->label('Full Name'),
                    InfoLists\Components\TextEntry::make('account.email')->label('Email'),
                    Infolists\Components\TextEntry::make('account.qualification_atc.name_long')->label('Current ATC Rating'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.region')->label('Current Region'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.division')->label('Current Division'),
                ])->columns(2),

                Infolists\Components\Section::make('Application Details')->schema([
                    Infolists\Components\TextEntry::make('id')->label('Application ID'),
                    Infolists\Components\TextEntry::make('status')->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => Str::title($state))
                        ->color(
                            fn (string $state): string => match ($state) {
                                VisitTransferApplication::STATUS_SUBMITTED => 'primary',
                                VisitTransferApplication::STATUS_ACCEPTED => 'success',
                                VisitTransferApplication::STATUS_COMPLETED => 'success',
                                VisitTransferApplication::STATUS_FAILED => 'danger',
                                VisitTransferApplication::STATUS_WITHDRAWN => 'grey',
                            }
                        ),
                    Infolists\Components\TextEntry::make('created_at')->label('Submitted At'),
                ])->columns(2),

                Infolists\Components\Section::make('Application Verification')->schema([
                    Infolists\Components\Livewire::make(ApplicationVerificationForm::class)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->copyable(),

                Tables\Columns\TextColumn::make('account_id')->label('VATSIM CID'),

                Tables\Columns\TextColumn::make('account.full_name')->label('Full Name'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::title($state))
                    ->color(fn (string $state): string => match ($state) {
                        VisitTransferApplication::STATUS_SUBMITTED => 'primary',
                        VisitTransferApplication::STATUS_ACCEPTED => 'success',
                        VisitTransferApplication::STATUS_COMPLETED => 'success',
                        VisitTransferApplication::STATUS_FAILED => 'danger',
                        VisitTransferApplication::STATUS_WITHDRAWN => 'grey',
                    }),
            ])
            ->filters([
                //
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', VisitTransferApplication::TYPE_TRANSFER));
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferringApplications::route('/'),
            'view' => Pages\ViewTransferringApplication::route('/{record}'),
            'edit' => Pages\EditTransferringApplication::route('/{record}/edit'),
        ];
    }
}
