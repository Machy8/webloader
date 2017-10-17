<?php

/**
 *
 * This file is part of the Macdom
 *
 * Copyright (c) 2015-2017 Vladimír Macháček
 *
 * For the full copyright and license information, please view the file license.md
 * that was distributed with this source code.
 *
 */

declare(strict_types = 1);

namespace WebLoader\Bridges\Nette;

use Nette\DI\CompilerExtension;


class WebLoaderExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'debugger' => TRUE,
		'disableCache' => FALSE,
		'documentRoot' => NULL,
		'filesCollections' => [],
		'filesCollectionsContainers' => [],
		'outputDir' => NULL
	];


	public function loadConfiguration()
	{
		$this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$compiler = $builder->addDefinition($this->prefix('compiler'))
			->setClass('WebLoader\Compiler')
			->setArguments([$this->config['outputDir']]);

		if ($this->config['documentRoot']) {
			$compiler->addSetup('setDocumentRoot', [$this->config['documentRoot']]);
		}

		if ($this->config['disableCache']) {
			$compiler->addSetup('disableCache');
		}

		if ($this->config['outputDir']) {
			$compiler->addSetup('setOutputDir', [$this->config['outputDir']]);
		}

		if ($this->config['filesCollections']) {
			$compiler->addSetup('createFilesCollectionsFromArray', [$this->config['filesCollections']]);
		}

		if ($this->config['filesCollectionsContainers']) {
			$compiler->addSetup(
				'createFilesCollectionsContainersFromArray',
				[$this->config['filesCollectionsContainers']]
			);
		}

		if ($this->config['debugger'] === TRUE) {
			$builder->addDefinition($this->prefix('tracyPanel'))
				->setClass('WebLoader\Bridges\Tracy\WebLoaderPanel');

			$compiler->addSetup(
				'@' . $this->prefix('tracyPanel') . '::setWebLoader', ['@' . $this->prefix('compiler')]
			);
		}
	}

}
