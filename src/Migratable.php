<?php

namespace Novius\ScoutElastic;

trait Migratable
{
    /**
     * Get the write alias.
     *
     * @return string
     */
    public function getWriteAlias()
    {
        return $this->getName().'_write';
    }
}
