<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use App\Models\PackageSetting;
use App\Runtime\ContextFactory;
use App\Runtime\CoreContext;
use Gll\Hub\Contracts\Http\HttpClient;
use Gll\Hub\Contracts\Testing\FakeHttpClient;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
use Tests\HubTestCase;

final class ContextTest extends HubTestCase
{
    private object $logs;

    private ContextFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logs = new class () extends AbstractLogger {
            /** @var list<array{0: mixed, 1: string, 2: array<string, mixed>}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message, $context];
            }
        };
        $this->factory = new ContextFactory($this->app->make(HttpClient::class), $this->logs);
    }

    public function test_the_instance_settings_win_over_the_package_settings(): void
    {
        PackageSetting::create(['package' => 'acme/library', 'settings' => ['a' => 'package', 'b' => 'package']]);
        $instance = $this->instance(attributes: ['settings' => ['b' => 'instance', 'c' => 'instance']]);

        $context = $this->factory->forInstance($instance);

        $this->assertSame('package', $context->setting('a'));
        $this->assertSame('instance', $context->setting('b'));
        $this->assertSame('instance', $context->setting('c'));
        $this->assertSame('fallback', $context->setting('missing', 'fallback'));
        $this->assertSame(['b' => 'instance', 'c' => 'instance', 'a' => 'package'], $context->settings());
    }

    public function test_credentials_are_decrypted_and_the_instance_wins(): void
    {
        PackageSetting::create(['package' => 'acme/library', 'settings' => [], 'credentials' => ['shared' => 'pkg', 'token' => 'pkg']]);
        $instance = $this->instance(attributes: ['credentials' => ['token' => 'instance']]);

        $context = $this->factory->forInstance($instance);

        $this->assertSame('instance', $context->credential('token'));
        $this->assertSame('pkg', $context->credential('shared'));
        $this->assertNull($context->credential('missing'));
    }

    public function test_an_instance_without_package_settings_works_on_its_own(): void
    {
        $context = $this->factory->forInstance($this->instance());

        $this->assertSame('a', $context->setting('shelf'));
        $this->assertNull($context->credential('token'));
    }

    public function test_a_package_context_has_the_package_values_only(): void
    {
        PackageSetting::create(['package' => 'acme/library', 'settings' => ['a' => 'package'], 'credentials' => ['secret' => 'shh']]);
        $this->instance(attributes: ['settings' => ['a' => 'instance'], 'credentials' => ['secret' => 'other']]);

        $context = $this->factory->forPackage('acme/library');

        $this->assertSame('package', $context->setting('a'));
        $this->assertSame('shh', $context->credential('secret'));
    }

    public function test_credentials_are_read_only_when_a_package_asks_for_one(): void
    {
        $reads = 0;
        $context = new CoreContext(['a' => 1], function () use (&$reads): array {
            $reads++;

            return ['token' => 'abc'];
        }, new FakeHttpClient(), new NullLogger(), 'https://hub.test');

        $context->setting('a');
        $context->settings();
        $this->assertSame(0, $reads);

        $this->assertSame('abc', $context->credential('token'));
        $this->assertSame('abc', $context->credential('token'));
        $this->assertSame(1, $reads);
    }

    public function test_the_clock_is_utc_and_follows_the_application_clock(): void
    {
        $this->travelTo('2026-05-05 10:00:00');
        $context = $this->factory->forPackage('acme/library');

        $this->assertInstanceOf(\DateTimeImmutable::class, $context->now());
        $this->assertSame('2026-05-05T10:00:00+00:00', $context->now()->format('c'));
        $this->assertSame('UTC', $context->now()->getTimezone()->getName());
    }

    public function test_the_public_url_comes_from_the_application_url_without_a_trailing_slash(): void
    {
        // phpunit.xml sets APP_URL with a trailing slash.
        $this->assertSame('https://hub.example.test', $this->factory->forPackage('acme/library')->publicUrl());
    }

    public function test_the_http_client_is_the_cores(): void
    {
        $this->assertSame($this->app->make(HttpClient::class)::class, $this->factory->forPackage('acme/library')->http()::class);
    }

    public function test_the_logger_tags_every_line_with_the_package_and_the_instance(): void
    {
        $instance = $this->instance();

        $this->factory->forInstance($instance)->logger()->info('Fetched', ['count' => 3]);
        $this->factory->forPackage('acme/library')->logger()->warning('Hooked');

        $this->assertSame(
            [
                ['info', 'Fetched', ['package' => 'acme/library', 'source' => 'catalog', 'instance' => $instance->id, 'count' => 3]],
                ['warning', 'Hooked', ['package' => 'acme/library']],
            ],
            $this->logs->records,
        );
    }
}
