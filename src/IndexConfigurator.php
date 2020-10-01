<?php

namespace Novius\ScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    /**
     * The name.
     *
     * @var string
     */
    protected $name;

    /**
     * The settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * The default mapping.
     *
     * @var array
     */
    protected $defaultMapping = [];

    /**
     * Get th name.
     *
     * @return string
     */
    public function getName($withDateSuffix = false)
    {
        $name = $this->name ?? Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));

        return config('scout.prefix').$name.($withDateSuffix ? '_'.date('Y-m-d_H-i-s') : '');
    }

    /**
     * Get the settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function getDefaultMapping()
    {
        return $this->defaultMapping;
    }
}
