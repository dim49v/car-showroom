<?php

namespace App\Service;

use Redis;

class RedisService
{
    public const MIN_TTL = 1;
    public const MAX_TTL = 3600;

    protected ?Redis $redis = null;
    protected string $host;
    protected int $port;

    /**
     * RedisService constructor.
     */
    public function __construct(string $host = 'cache', int $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return bool|string
     */
    public function get(string $key)
    {
        $this->connect();

        return $this->redis->get($key);
    }

    private function connect(): void
    {
        if (null === $this->redis || '+PONG' !== $this->redis->ping()) {
            $this->redis = new Redis();
            $this->redis->connect($this->host, $this->port);
        }
    }

    public function set(string $key, string $value, ?float $ttl = null): bool
    {
        $this->connect();

        if (null === $ttl) {
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $this->normaliseTtl($ttl), $value);
    }

    private function normaliseTtl(float $ttl): int
    {
        $ttl = ceil(abs($ttl));

        return ($ttl >= self::MIN_TTL && $ttl <= self::MAX_TTL) ? $ttl : self::MAX_TTL;
    }

    public function expire(string $key, ?float $ttl = self::MIN_TTL): bool
    {
        $this->connect();

        return $this->redis->expire($key, $this->normaliseTtl($ttl));
    }

    public function delete(string $key): int
    {
        $this->connect();

        return $this->redis->del($key);
    }

    public function getTtl(string $key): float
    {
        $this->connect();

        return $this->redis->ttl($key);
    }

    public function persist(string $key): bool
    {
        $this->connect();

        return $this->redis->persist($key);
    }
}
