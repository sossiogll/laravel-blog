<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Testing;

use Gll\Hub\Contracts\Http\HttpClient;
use Gll\Hub\Contracts\Http\HttpResponse;
use RuntimeException;
use Throwable;

/**
 * The HTTP client every package tests with. Each URL answers what it was given:
 * a response, or a contract exception to stand for what the core's client throws
 * (Unavailable, RateLimited, InvalidCredentials). An unknown URL fails the test.
 */
final class FakeHttpClient implements HttpClient
{
    /** @var array<string, HttpResponse|Throwable> */
    private array $routes = [];

    /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string}> */
    public array $sent = [];

    public function respond(string $url, HttpResponse|Throwable $answer): self
    {
        $this->routes[$url] = $answer;

        return $this;
    }

    public function get(string $url, array $headers = []): HttpResponse
    {
        return $this->request('GET', $url, $headers);
    }

    public function request(string $method, string $url, array $headers = [], ?string $body = null): HttpResponse
    {
        $this->sent[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];
        $answer = $this->routes[$url] ?? throw new RuntimeException("FakeHttpClient: no answer for {$method} {$url}");

        if ($answer instanceof Throwable) {
            throw $answer;
        }

        return $answer;
    }
}
