<?php

namespace Novius\ScoutElastic\Payloads\Features;

trait HasProtectedKeys
{
    /**
     * {@inheritdoc}
     */
    public function set(?string $key, $value): static
    {
        if (in_array($key, $this->protectedKeys, true)) {
            return $this;
        }

        return parent::set($key, $value);
    }
}
