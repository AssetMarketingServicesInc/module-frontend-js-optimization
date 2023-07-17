<?php

declare(strict_types=1);

namespace Ams\FrontendJsOptimization\Model\Config;

interface IsEnabledInterface
{   
    public function get(): bool;
}
