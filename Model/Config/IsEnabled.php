<?php

declare(strict_types=1);

namespace Ams\FrontendJsOptimization\Model\Config;

class IsEnabled implements IsEnabledInterface
{
    private const XML_PATH_IS_ENABLED = 'dev/js/enable_frontend_js_optimization';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }
    
    public function get(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
