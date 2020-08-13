<?php

/**
 *
 * Copyright (c) Vladimír Macháček
 *
 * For the full copyright and license information, please view the file license.md
 * that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace WebLoader\Bridges\Tracy;

use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;

class WebLoaderPanel implements IBarPanel
{
    private const TEMPLATES_DIR = __DIR__ . '/templates';

    /**
     * @var Compiler
     */
    private $webLoaderCompiler;


    public function __construct()
    {
        Debugger::getBar()->addPanel($this);
    }


    public function getPanel(): string
    {
        ob_start();

        $cacheEnabled = $this->getWebLoaderCompiler()->isCacheEnabled();
        $filesCollectionsByType = $this->getWebLoaderCompiler()->getFilesCollections();
        $filesCollectionsContainers = $this->getWebLoaderCompiler()->getFilesCollectionsContainers();
        $filters = $this->getWebLoaderCompiler()->getFilters();
        $outputDir = $this->getWebLoaderCompiler()->getOutputDir();
        $documentRoot = $this->getWebLoaderCompiler()->getDocumentRoot();
        $pathsPlaceholders = $this->getWebLoaderCompiler()->getPathsPlaceholders();
        $version = $this->getWebLoaderCompiler()->getVersion();

        require self::TEMPLATES_DIR . '/panel.phtml';

        return ob_get_clean();
    }


    public function getTab(): string
    {
        ob_start();

        require self::TEMPLATES_DIR . '/tab.phtml';

        return ob_get_clean();
    }


    public function setWebLoader(Compiler $compiler): WebLoaderPanel
    {
        $this->webLoaderCompiler = $compiler;
        return $this;
    }


    private function getWebLoaderCompiler(): Compiler
    {
        return $this->webLoaderCompiler;
    }
}
