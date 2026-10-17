<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceInstances\Pages;

use App\Filament\Resources\SourceInstances\SourceInstanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSourceInstances extends ListRecords
{
    protected static string $resource = SourceInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
