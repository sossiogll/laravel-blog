<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceInstances\Pages;

use App\Filament\Fields\FormValues;
use App\Filament\Resources\SourceInstances\SourceInstanceResource;
use App\Models\SourceInstance;
use App\Packages\Configuration;
use App\Packages\PackageRegistry;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSourceInstance extends CreateRecord
{
    protected static string $resource = SourceInstanceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $source = app(PackageRegistry::class)->sources()[$data['source']];
        $definition = SourceInstanceResource::definition($data['source']);

        return SourceInstance::create([
            'package' => $source->package,
            'source_key' => $source->key(),
            'label' => $data['label'],
            'enabled' => $data['enabled'] ?? true,
            'interval_minutes' => FormValues::integer($data['interval_minutes'] ?? null),
            'settings' => Configuration::settings($definition->instanceSettings, FormValues::settings($definition->instanceSettings, $data['settings'] ?? [])),
            'credentials' => Configuration::credentials($definition->instanceCredentials, $data['credentials'] ?? [], null),
        ]);
    }
}
