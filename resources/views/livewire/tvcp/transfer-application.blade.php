<div class="text-left">
    <form wire:submit="create">
        {{ $this->form }}

        <x-filament::section class="mt-2">
            <x-slot name="heading">
                Submit Application
            </x-slot>

            <p>Upon submitting this application you should receive an email confirmation to your registered email address.
                If you do not receive this email, please contact the Member Services department via our helpdesk.</p>

            <div class="text-right">
                <button type="submit" class="inline-flex flex-1 text-right mt-2 rounded-md bg-brand px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Submit Application
                </button>
            </div>
        </x-filament::section>
    </form>
</div>
