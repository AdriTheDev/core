<?php

namespace App\Filament\Resources\TransferringApplicationResource\Pages;

use App\Filament\Resources\TransferringApplicationResource;
use App\Models\TVCP\VisitTransferApplication;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ViewTransferringApplication extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = TransferringApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function detailsInfoList(Infolist $infolist): Infolist
    {
        return $this->makeInfolist()
            ->record($this->getRecord())
            ->schema([
                Infolists\Components\Section::make('Member Details on View Page')->schema([
                    Infolists\Components\TextEntry::make('account_id')->label('VATSIM CID'),
                    Infolists\Components\TextEntry::make('account.full_name')->label('Full Name'),
                    InfoLists\Components\TextEntry::make('account.email')->label('Email'),
                    Infolists\Components\TextEntry::make('account.qualification_atc.name_long')->label('Current ATC Rating'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.region')->label('Current Region'),
                    InfoLists\Components\TextEntry::make('account.primary_state.pivot.division')->label('Current Division'),
                ])->columns(2)
            ]);
    }

    public function applicationDetailsInfoList(Infolist $infolist): Infolist
    {
        return $this->makeInfolist()
            ->record($this->getRecord())
            ->schema([
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

            ]);
    }

    public function verificationStepsForm(): Form
    {
        return parent::makeForm()
            ->model($this->getRecord())
            ->schema([
                Forms\Components\Section::make('Application Verification')->schema([
                    Forms\Components\Checkbox::make('post_rating_hours')->label('Post Rating Hours')->default(false)->hint('Has the member completed 50 hours post rating?'),
                ])
            ]);
    }
}
