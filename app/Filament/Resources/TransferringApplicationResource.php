<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferringApplicationResource\Pages;
use App\Filament\Resources\TransferringApplicationResource\RelationManagers;
use App\Models\TransferringApplication;
use App\Models\TVCP\VisitTransferApplication;
use Doctrine\DBAL\Schema\Column;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TransferringApplicationResource extends Resource
{
    protected static ?string $model = VisitTransferApplication::class;

    protected static ?string $modelLabel = "Transferring Application";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Member Details')->schema([
                    Infolists\Components\TextEntry::make('account_id')->label('VATSIM CID'),
                    Infolists\Components\TextEntry::make('account.full_name')->label('Full Name'),
                    InfoLists\Components\TextEntry::make('account.email')->label('Email'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.region')->label('Current Region'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.division')->label('Current Division'),
                    Infolists\Components\TextEntry::make('account.qualification_atc.name_long')->label('Current ATC Rating')
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
                ])->columns(2)
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
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', VisitTransferApplication::TYPE_TRANSFER))
            ->actions([
                Tables\Actions\ViewAction::make(),
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
