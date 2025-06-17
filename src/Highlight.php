<?php

namespace Novius\ScoutElastic;

class Highlight
{
    /**
     * The highlight array.
     */
    private array $highlight;

    /**
     * Highlight constructor.
     *
     * @return void
     */
    public function __construct(array $highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * Get a value.
     *
     * @return mixed|string|null
     */
    public function __get(string $key)
    {
        $field = str_replace('AsString', '', $key);

        if (isset($this->highlight[$field])) {
            $value = $this->highlight[$field];

            return $field === $key ? $value : implode(' ', $value);
        }

        return null;
    }
}
