<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries\Pages;

use App\Content\EntryEditor;
use App\Content\InvalidEntry;
use App\Content\ReferenceStore;
use App\Filament\Resources\Entries\EntryForm;
use App\Filament\Resources\Entries\EntryFormState;
use App\Filament\Resources\Entries\EntryResource;
use App\Models\Entry;
use App\Packages\PackageRegistry;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Content\FieldType;
use Illuminate\Database\Eloquent\Model;

class EditEntry extends EditRecord
{
    protected static string $resource = EntryResource::class;

    public function form(Schema $schema): Schema
    {
        return EntryForm::configure($schema, $this->definition(), $this->getRecord());
    }

    /** References live in their own table, not in the entry's columns. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $targets = app(ReferenceStore::class)->targets($this->getRecord()->id);
        foreach ($this->definition()?->fields ?? [] as $field) {
            if ($field->type === FieldType::Reference) {
                $ids = $targets[$field->key] ?? [];
                $data[$field->curated ? 'curated' : 'fields'][$field->key] = $field->multiple ? $ids : ($ids[0] ?? null);
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Entry $record */
        $type = $this->definition();

        try {
            return app(EntryEditor::class)->update($record, EntryFormState::input($type, $data, $record));
        } catch (InvalidEntry $refused) {
            throw EntryFormState::refusal($refused, $type);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn (): bool => $this->getRecord()->isHandWritten() && ! $this->getRecord()->trashed()),
            RestoreAction::make(),
            ForceDeleteAction::make()->visible(fn (): bool => $this->getRecord()->isHandWritten() && $this->getRecord()->trashed()),
        ];
    }

    private function definition(): ?ContentTypeDefinition
    {
        return app(PackageRegistry::class)->contentType($this->getRecord()->type);
    }
}
