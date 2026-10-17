<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The package-wide settings and secrets of one package, shared by all its
 * instances and by its webhooks. Credentials are encrypted and hidden like an
 * instance's.
 *
 * @property array<string, mixed> $settings
 * @property array<string, string>|null $credentials
 */
class PackageSetting extends Model
{
    protected $primaryKey = 'package';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['package', 'settings', 'credentials'];

    protected $hidden = ['credentials'];

    protected $attributes = ['settings' => '[]'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'credentials' => 'encrypted:array',
        ];
    }
}
