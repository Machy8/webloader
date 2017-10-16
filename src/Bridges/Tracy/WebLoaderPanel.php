<?php

/**
 *
 * Copyright (c) Vladimír Macháček
 *
 * For the full copyright and license information, please view the file license.md
 * that was distributed with this source code.
 *
 */

declare(strict_types = 1);

namespace WebLoader\Bridges\Tracy;

use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;


class WebLoaderPanel implements IBarPanel
{

	const TEMPLATES_DIR = __DIR__ . '/templates';

	/**
	 * @var Compiler
	 */
	private $webLoader;


	public function __construct()
	{
		Debugger::getBar()->addPanel($this);
	}


	/**
	 * @return string
	 */
	public function getPanel(): string
	{
		ob_start();

		$cacheEnabled = $this->getWebLoader()->isCacheEnabled();
		$filesCollectionsByType = $this->getWebLoader()->getFilesCollections();
		$filesCollectionsContainers = $this->getWebLoader()->getFilesCollectionsContainers();
		$filters = $this->getWebLoader()->getFilters();
		$outputDir = $this->getWebLoader()->getOutputDir();
		$documentRoot = $this->getWebLoader()->getDocumentRoot();
		$pathsPlaceholders = $this->getWebLoader()->getPathsPlaceholders();
		$version = $this->getWebLoader()->getVersion();

		require self::TEMPLATES_DIR . '/panel.phtml';

		return ob_get_clean();
	}


	public function getTab(): string
	{
		ob_start();

		require self::TEMPLATES_DIR . '/tab.phtml';

		return ob_get_clean();
	}


	public function setWebLoader(Compiler $compiler): self
	{
		$this->webLoader = $compiler;

		return $this;
	}


	private function getWebLoader(): Compiler
	{
		if ( ! $this->webLoader) {
			$this->webLoader = new Compiler;
		}

		return $this->webLoader;
	}

}
