<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceInstances\Pages;

use App\Filament\Fields\FormValues;
use App\Filament\Resources\SourceInstances\SourceInstanceResource;
use App\Models\SourceInstance;
use App\Packages\Configuration;
use App\Sync\InstanceHealth;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSourceInstance extends EditRecord
{
    protected static string $resource = SourceInstanceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['source'] = "{$data['package']}/{$data['source_key']}";

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var SourceInstance $record */
        $attributes = [
            'label' => $data['label'],
            'enabled' => $data['enabled'] ?? true,
            'interval_minutes' => FormValues::integer($data['interval_minutes'] ?? null),
        ];

        // A source the registry no longer has cannot say what its settings are: keep them as they are.
        $definition = SourceInstanceResource::definition("{$record->package}/{$record->source_key}");
        if ($definition !== null) {
            $attributes['settings'] = Configuration::settings($definition->instanceSettings, FormValues::settings($definition->instanceSettings, $data['settings'] ?? []));
            $attributes['credentials'] = Configuration::credentials($definition->instanceCredentials, $data['credentials'] ?? [], $record->credentials);
        }

        $record->update($attributes);
        app(InstanceHealth::class)->reconfigured($record);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
