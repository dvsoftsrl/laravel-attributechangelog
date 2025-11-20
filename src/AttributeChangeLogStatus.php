<?php

namespace DvSoft\AttributeChangeLog;

use Illuminate\Contracts\Config\Repository;

class AttributeChangeLogStatus
{
    protected $enabled = true;

    public function __construct(Repository $config)
    {
        $this->enabled = $config['attributechangelog.enabled'];
    }

    public function enable(): bool
    {
        return $this->enabled = true;
    }

    public function disable(): bool
    {
        return $this->enabled = false;
    }

    public function disabled(): bool
    {
        return $this->enabled === false;
    }
}
