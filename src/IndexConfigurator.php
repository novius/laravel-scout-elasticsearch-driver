<?php

namespace Novius\ScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    /**
     * The name.
     */
    protected ?string $name;

    /**
     * The settings.
     */
    protected array $settings = [];

    /**
     * The default mapping.
     */
    protected array $defaultMapping = [];

    /**
     * Get the name.
     */
    public function getName($withDateSuffix = false): string
    {
        $name = $this->name ?? Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));

        return config('scout.prefix').$name.($withDateSuffix ? '_'.date('Y-m-d_H-i-s') : '');
    }

    /**
     * Get the settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getDefaultMapping(): array
    {
        return $this->defaultMapping;
    }
}
