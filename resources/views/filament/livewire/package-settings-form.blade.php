<form wire:submit="save">
    {{ $this->form }}

    <div style="margin-top: 1rem">
        <x-filament::button type="submit">Save</x-filament::button>
    </div>
</form>
