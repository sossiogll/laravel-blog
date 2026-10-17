<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Packages\Configuration;
use Gll\Hub\Contracts\Package\Credential;
use Gll\Hub\Contracts\Package\Setting;
use Gll\Hub\Contracts\Package\SettingType;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    /** @return list<Setting> */
    private function declared(): array
    {
        return [
            new Setting('name', 'Name', SettingType::String),
            new Setting('format', 'Format', SettingType::Enum, default: 'paper', options: ['paper', 'audio']),
            new Setting('copies', 'Copies', SettingType::Integer, default: 1),
            new Setting('signed', 'Signed', SettingType::Boolean, default: true),
        ];
    }

    public function test_a_default_is_stored_for_every_setting_left_blank(): void
    {
        $this->assertSame(
            ['format' => 'paper', 'copies' => 1, 'signed' => true],
            Configuration::settings($this->declared(), ['name' => '', 'format' => null]),
        );
    }

    public function test_what_was_typed_wins_over_the_default_even_when_it_is_falsy(): void
    {
        $this->assertSame(
            ['name' => 'Shelf', 'format' => 'audio', 'copies' => 0, 'signed' => false],
            Configuration::settings($this->declared(), ['name' => 'Shelf', 'format' => 'audio', 'copies' => 0, 'signed' => false, 'unknown' => 'x']),
        );
    }

    public function test_a_blank_credential_keeps_the_stored_one(): void
    {
        $declared = [new Credential('token', 'Token'), new Credential('secret', 'Secret')];

        $this->assertSame(
            ['token' => 'new', 'secret' => 'old-secret'],
            Configuration::credentials($declared, ['token' => 'new', 'secret' => ''], ['token' => 'old', 'secret' => 'old-secret']),
        );
        $this->assertSame(['token' => 'old'], Configuration::credentials($declared, [], ['token' => 'old', 'gone' => 'x']));
        $this->assertNull(Configuration::credentials($declared, ['token' => ''], null));
    }
}
