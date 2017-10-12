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
	protected $config = [
		'debugger' => '%debugMode%',
		'filesCollections' => [],
		'filesCollectionsContainers' => [],
		'outputDir' => NULL,
		'includePath' => NULL
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->config);

		$compiler = $builder->addDefinition($this->prefix('compiler'))
			->setClass('WebLoader\Compiler');

		if (isset($config['outputDir'])) {
			$compiler->addSetup('setOutputDir', [$config['outputDir']]);
		}

		if (isset($config['documentRoot'])) {
			$compiler->addSetup('setDocumentRoot', [$config['includeDir']]);
		}

		if (isset($config['filesCollections'])) {
			bdump($config['filesCollections']);
			$compiler->addSetup('createFilesCollectionsFromArray', [$config['filesCollections']]);
		}

		if (isset($config['filesCollectionsContainers'])) {
			$compiler->addSetup('createFilesCollectionsContainersFromArray', [$config['filesCollectionsContainers']]);
		}

		if (isset($config['debugger'])) {
			$builder->addDefinition($this->prefix('tracyPanel'))
				->setClass('WebLoader\Bridges\Tracy\WebLoaderPanel');

			$compiler->addSetup(
				'@' . $this->prefix('tracyPanel') . '::setWebLoader', ['@' . $this->prefix('compiler')]
			);
		}
	}

}
