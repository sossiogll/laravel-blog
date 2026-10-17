<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries\Pages;

use App\Content\EntryEditor;
use App\Content\InvalidEntry;
use App\Filament\Resources\Entries\EntryForm;
use App\Filament\Resources\Entries\EntryFormState;
use App\Filament\Resources\Entries\EntryResource;
use App\Packages\PackageRegistry;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

class CreateEntry extends CreateRecord
{
    protected static string $resource = EntryResource::class;

    #[Url]
    public ?string $type = null;

    public function mount(): void
    {
        abort_if($this->definition() === null, 404);

        parent::mount();
    }

    public function getTitle(): string|Htmlable
    {
        return 'New ' . $this->definition()?->label;
    }

    public function form(Schema $schema): Schema
    {
        return EntryForm::configure($schema, $this->definition(), null);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $type = $this->definition();

        try {
            return app(EntryEditor::class)->create((string) $this->type, EntryFormState::input($type, $data, null), auth()->id());
        } catch (InvalidEntry $refused) {
            throw EntryFormState::refusal($refused, $type);
        }
    }

    private function definition(): ?ContentTypeDefinition
    {
        return $this->type === null ? null : app(PackageRegistry::class)->contentType($this->type);
    }
}
