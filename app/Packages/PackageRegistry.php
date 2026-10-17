<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Package\Package;
use Gll\Hub\Contracts\Package\Role;
use Gll\Hub\Contracts\Testing\Conformance;
use Gll\Hub\Contracts\Testing\ConformanceError;
use Throwable;

/**
 * What the installed packages provide, as the core sees it.
 *
 * A package is accepted only when it conforms to the contract this core speaks
 * (version and rules alike); otherwise it is kept, with the reasons, in rejected().
 * One bad package never takes the others, or the app, down.
 *
 * Built once per process (see AppServiceProvider). Anything that can produce a
 * Package can register it, which is how tests bring their own.
 */
final class PackageRegistry
{
    /** @var array<string, AcceptedPackage> */
    private array $packages = [];

    /** @var array<string, Provided> */
    private array $types = [];

    /** @var array<string, Provided> */
    private array $sources = [];

    /** @var array<string, Provided> */
    private array $webhooks = [];

    /** @var list<RejectedPackage> */
    private array $rejected = [];

    /** True when the package was accepted; otherwise it is in rejected(). */
    public function register(Package $package, ?string $origin = null): bool
    {
        $name = $origin ?? $package::class;

        try {
            $manifest = $package->manifest();
            $name = $manifest->name;
            Conformance::check($package);
        } catch (ConformanceError $e) {
            return $this->reject($name, $e->violations);
        } catch (Throwable $e) {
            return $this->reject($name, [$e::class . ': ' . $e->getMessage()]);
        }

        if (isset($this->packages[$manifest->name])) {
            return $this->reject($name, ["package {$manifest->name} is already registered"]);
        }

        $types = $this->provided($manifest->name, $manifest->classesFor(Role::ContentType));
        $sources = $this->provided($manifest->name, $manifest->classesFor(Role::Source));
        $webhooks = $this->provided($manifest->name, $manifest->classesFor(Role::Webhook));

        $taken = [];
        foreach ($types as $type) {
            if (isset($this->types[$type->key()])) {
                $taken[] = "content type \"{$type->key()}\" is already provided by {$this->types[$type->key()]->package}";
            }
        }
        if ($taken !== []) {
            return $this->reject($name, $taken);
        }

        $this->packages[$manifest->name] = new AcceptedPackage($manifest, $types, $sources, $webhooks);
        foreach ($types as $type) {
            $this->types[$type->key()] = $type;
        }
        foreach ($sources as $source) {
            $this->sources[self::ref($source->package, $source->key())] = $source;
        }
        foreach ($webhooks as $webhook) {
            $this->webhooks[self::ref($webhook->package, $webhook->key())] = $webhook;
        }

        return true;
    }

    /** @param list<string> $reasons */
    public function reject(string $name, array $reasons): bool
    {
        $this->rejected[] = new RejectedPackage($name, $reasons);

        return false;
    }

    /** @return array<string, AcceptedPackage> by package name */
    public function packages(): array
    {
        return $this->packages;
    }

    public function package(string $name): ?AcceptedPackage
    {
        return $this->packages[$name] ?? null;
    }

    /** @return list<RejectedPackage> */
    public function rejected(): array
    {
        return $this->rejected;
    }

    public function contentType(string $key): ?ContentTypeDefinition
    {
        $provided = $this->types[$key] ?? null;

        return $provided?->definition instanceof ContentTypeDefinition ? $provided->definition : null;
    }

    /** @return array<string, ContentTypeDefinition> by content type key */
    public function contentTypes(): array
    {
        $all = [];
        foreach ($this->types as $key => $provided) {
            $all[$key] = $provided->definition;
        }

        return $all;
    }

    public function source(string $package, string $key): ?Provided
    {
        return $this->sources[self::ref($package, $key)] ?? null;
    }

    /** @return array<string, Provided> by "package/key" */
    public function sources(): array
    {
        return $this->sources;
    }

    public function webhook(string $package, string $key): ?Provided
    {
        return $this->webhooks[self::ref($package, $key)] ?? null;
    }

    /** @return array<string, Provided> by "package/key" */
    public function webhooks(): array
    {
        return $this->webhooks;
    }

    private static function ref(string $package, string $key): string
    {
        return "{$package}/{$key}";
    }

    /**
     * @param list<class-string> $classes
     * @return list<Provided>
     */
    private function provided(string $package, array $classes): array
    {
        return array_map(
            static fn (string $class): Provided => new Provided($package, $class, $class::definition()),
            $classes,
        );
    }
}
