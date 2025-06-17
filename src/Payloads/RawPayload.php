<?php

namespace Novius\ScoutElastic\Payloads;

use Illuminate\Support\Arr;

class RawPayload
{
    /**
     * The payload.
     */
    protected array $payload = [];

    /**
     * Set a value.
     */
    public function set(?string $key, $value): static
    {
        if (! is_null($key)) {
            Arr::set($this->payload, $key, $value);
        }

        return $this;
    }

    /**
     * Set a value if it's not empty.
     */
    public function setIfNotEmpty(string $key, $value): static
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    /**
     * Set a value if it's not null.
     */
    public function setIfNotNull(string $key, $value): static
    {
        if (is_null($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    /**
     * Checks that the payload key has a value.
     */
    public function has(string $key): bool
    {
        return Arr::has($this->payload, $key);
    }

    /**
     * Add a value.
     */
    public function add(?string $key, $value): static
    {
        if (! is_null($key)) {
            $currentValue = Arr::get($this->payload, $key, []);

            if (! is_array($currentValue)) {
                $currentValue = Arr::wrap($currentValue);
            }

            $currentValue[] = $value;

            Arr::set($this->payload, $key, $currentValue);
        }

        return $this;
    }

    /**
     * Add a value if it's not empty.
     */
    public function addIfNotEmpty(string $key, $value): static
    {
        if (empty($value)) {
            return $this;
        }

        return $this->add($key, $value);
    }

    /**
     * Get value.
     */
    public function get(?string $key = null, $default = null)
    {
        return Arr::get($this->payload, $key, $default);
    }
}
