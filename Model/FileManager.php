<?php

declare(strict_types=1);

namespace MageSuite\Magepack\Model;

use Magento\Framework\View\Asset\File\FallbackContext as FileFallbackContext;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Asset\File;

/**
 * Class that creates needed assets for the page.
 */
class FileManager
{
    public function __construct(
        private AssetRepository $assetRepo
    ) {
    }

    /**
     * Create a view asset for default RequireJS config.
     */
    public function createRequireJsConfigAsset(string $fileName): File
    {
        return $this->assetRepo->createArbitrary(
            $this->assetRepo->getStaticViewFileContext()->getConfigPath() . '/' . $fileName,
            ''
        );
    }
}
