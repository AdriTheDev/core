<?php

namespace App\Livewire\TVCP;

use App\Models\TVCP\VisitTransferApplication;
use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Component;

class TransferApplication extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function create(): void
    {
        VisitTransferApplication::create([
            'id' => (string) Str::uuid(),
            'account_id' => auth()->id(),
            'type' => VisitTransferApplication::TYPE_TRANSFER,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Placeholder::make('intro')
                    ->hiddenLabel()
                    ->content('This form is for members of other VATSIM divisions within the Europe, Middle-East and Africa (EMEA) division to VATSIM UK.'),
                Components\Section::make('Personal Details')
                    ->columns([
                        'sm' => 2,
                    ])
                    ->description('The following represents the information VATSIM UK has about you. If any of the details we have on file for you below are out of date, please contact our Member Services team before completing your application.')
                    ->schema([
                        Components\TextInput::make('full_name')
                            ->label('Full Name')
                            ->default(auth()->user()->name),
                        Components\TextInput::make('vatsim_id')
                            ->label('VATSIM CID')
                            ->default(auth()->user()->id)
                            ->readonly(),

                        Components\TextInput::make('current_region')
                            ->label('Current Region')
                            ->default(auth()->user()->primary_state->pivot->region),

                        Components\TextInput::make('current_division')
                            ->label('Current Division')
                            ->default(auth()->user()->primary_state->pivot->division),

                        Components\TextInput::make('Current ATC Rating')
                            ->label('Current ATC Rating')
                            ->default(auth()->user()->qualification_atc->name_long.' ('.auth()->user()->qualification_atc->code.')'),
                    ]),

                Components\Section::make('Eligibility for Transfer')
                    ->label('Eligibility for Transfer')
                    ->description('All of the following criteria must be met for a transferring application to be submitted.')
                    ->schema([
                        Components\Placeholder::make('intro')
                            ->hiddenLabel()
                            ->content('Please confirm that you meet the following criteria to be eligible for a transfer to VATSIM UK. The criteria will be double checked upon your application being submitted so please answer truthfully.'),
                        Components\Checkbox::make('read_policy')
                            ->label('I have read and agree to the VATSIM UK Transferring Controller Policy')
                            ->accepted(),
                        Components\Checkbox::make('hours_post_rating')
                            ->label('I have achieved 50 hours of controlling time post-rating on positions at that rating e.g. APP positions for S3.')
                            ->accepted(),
                        Components\Checkbox::make('rating_award')
                            ->label('I have not been awarded a new rating in the last 90 days.')
                            ->accepted(),
                        Components\Checkbox::make('recent_transfers')
                            ->label('I have not completed a transfer to another division in the last 90 days.')
                            ->accepted(),
                        Components\Checkbox::make('failed_applications')
                            ->label('I have not made a fail application to transfer to VATSIM UK in the last 90 days')
                            ->accepted(),
                        Components\Checkbox::make('currency')
                            ->label('I have controlled 3 hours in last calendar quarter in my current division.')
                            ->accepted(),
                        Components\Checkbox::make('staff_role')
                            ->label('I do not hold a staff role in another VATSIM division')
                            ->accepted(),
                    ]),

                Components\Section::make('Training')
                    ->schema([
                        Components\Placeholder::make('details_training')
                            ->hiddenLabel()
                            ->content('If your application is successful you will be required to complete
                            a Local Induction Plan which includes self-study and a Competency Check.
                            This is required to take place within 90 days of a training place being allocated
                            post application approval. Please confirm you understand this requirement.'),

                        Components\Checkbox::make('training_understood')
                            ->label('I understand the training requirements and that I must complete them within 90 days of a training place being allocated post application approval.')
                            ->accepted(),
                    ]),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.tvcp.transfer-application');
    }
}
