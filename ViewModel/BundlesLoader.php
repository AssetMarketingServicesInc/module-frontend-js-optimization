<?php

declare(strict_types=1);

namespace Ams\FrontendJsOptimization\ViewModel;

use Ams\FrontendJsOptimization\Model\FileManager;
use Ams\FrontendJsOptimization\Model\Config\IsEnabledInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BundlesLoader
{
    public function __construct(
        private DirectoryList $dir,
        private FileManager $fileManager,
        private PageConfig $pageConfig,
        private RequireJsConfig $requireJsConfig,
        private Minification $minification,
        private IsEnabledInterface $isEnabled
    ) {
    }

    /**
     * @return string
     */
    public function getCommonBundleUrl()
    {
        $commonBundle = $this->getData('common_bundle');
        if ($commonBundle && $this->isEnabled->get()) {
            return $this->getViewFileUrl($commonBundle['bundle_path']);
        }

        return '';
    }

    /**
     * @return string[] List of page bundles URLs.
     */
    public function getPageBundlesUrls()
    {
        $pageBundles = $this->getData('page_bundles');
        if (!empty($pageBundles) && $this->isEnabled->get()) {
            return array_map(function($pageBundle) {
                return $this->getViewFileUrl($pageBundle['bundle_path']);
            }, $pageBundles);
        }

        return [];
    }

    /**
     * @return string[] List of bundles URLs to prefetch when browser is idle.
     */
    public function getPrefetchBundlesUrls()
    {
        $prefetchBundles = $this->getData('prefetch_bundles');
        if (!empty($prefetchBundles) && $this->isEnabled->get()) {
            return array_values(
                array_map(function($prefetchBundle) {
                    return $this->getViewFileUrl($prefetchBundle);
                }, $prefetchBundles)
            );
        }
        return [];
    }

    /**
     * Adjust layout to include all bundles configuration files.
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _prepareLayout()
    {
        if ($this->isEnabled->get()) {
            foreach ($this->_getBundlesConfigPaths() as $bundleConfigPath) {
                $this->_addBundleConfig($bundleConfigPath);
            }
        }
    }

    /**
     * Adds given bundle configuration to the head scripts.
     */
    protected function _addBundleConfig($bundleConfigPath) {
        if (!$bundleConfigPath) {
            return;
        }

        $assetCollection = $this->pageConfig->getAssetCollection();

        $bundleConfigAsset = $this->fileManager->createRequireJsConfigAsset($bundleConfigPath);
        $bundleConfigRelPath = $bundleConfigAsset->getFilePath();
        $bundleConfigAbsPath = $this->dir->getPath('static') . '/' . $bundleConfigRelPath;

        /**
         * Add bundle config before main requirejs-config.js file to make sure all modules are loaded from them.
         */
        if (file_exists($bundleConfigAbsPath)) {
            $assetCollection->insert(
                $bundleConfigRelPath,
                $bundleConfigAsset,
                $this->requireJsConfig->getMixinsFileRelativePath()
            );
        }
    }

    /**
     * Prepares and returns a list of all defined bundles configurations paths.
     */
    protected function _getBundlesConfigPaths() {
        $configPaths = [];

        $commonBundle = $this->getCommonBundle();
        if ($commonBundle) {
            $configPaths[] = $this->minification->addMinifiedSign($commonBundle['config_path']);
        }

        $pageBundles = $this->getPageBundles() ?? [];
        foreach ($pageBundles as $pageBundle) {
            $configPaths[] = $this->minification->addMinifiedSign($pageBundle['config_path']);
        }
        return array_reverse($configPaths);
    }
}
