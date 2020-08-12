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

namespace WebLoader\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Helpers;


class WebLoaderExtension extends CompilerExtension
{

	private const
		ENGINE_CLASSNAME = 'WebLoader\Engine',
		ENGINE_PREFIX = 'engine',

		TRACY_CLASSNAME = 'Tracy\Debugger',

		TRACY_PANEL_CLASSNAME = 'WebLoader\Bridges\Tracy\WebLoaderPanel',
		TRACY_PANEL_PREFIX = 'tracyPanel';

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $defaults = [
		'debugger' => '%debugMode%',
		'disableCache' => FALSE,
		'documentRoot' => '/',
		'filesCollections' => [],
		'filesCollectionsContainers' => [],
		'hostUrl' => NULL,
		'outputDir' => NULL,
		'pathPlaceholderDelimiter' => '#',
		'pathsPlaceholders' => []
	];

	/**
	 * @var ContainerBuilder
	 */
	private $builder;


	public function loadConfiguration(): void
	{
		$this->builder = $this->getContainerBuilder();

		$this->config = $this->validateConfig($this->defaults);
		$this->config = Helpers::expand($this->config, $this->builder->parameters);

		$this->setupTracyPanel();
		$this->setupWebLoader();
	}


	public function afterCompile(\Nette\PhpGenerator\ClassType $classType): void
	{
		if ($this->config['debugger'] !== TRUE || ! class_exists(self::TRACY_CLASSNAME)) {
			return;
		}

		$classType->getMethod('initialize')->addBody(
			'$this->getByType("' . self::TRACY_PANEL_CLASSNAME
			. '")->setWebLoader($this->getByType("' . self::ENGINE_CLASSNAME . '")->getCompiler());'
		);
	}


	private function setupTracyPanel(): void
	{
		if ($this->config['debugger'] === TRUE) {
			$this->builder->addDefinition($this->prefix(self::TRACY_PANEL_PREFIX))
				->setFactory(self::TRACY_PANEL_CLASSNAME);
		}
	}


	private function setupWebLoader(): void
	{
		$arguments = [
			$this->config['outputDir'],
			$this->config['documentRoot'],
			$this->config['hostUrl']
		];

		$webLoader = $this->builder->addDefinition($this->prefix(self::ENGINE_PREFIX))
			->setFactory(self::ENGINE_CLASSNAME)
			->setArguments($arguments);

		if ($this->config['disableCache']) {
			$webLoader->addSetup('disableCache');
		}

		if ($this->config['filesCollections']) {
			$webLoader->addSetup('createFilesCollectionsFromArray', [$this->config['filesCollections']]);
		}

		if ($this->config['filesCollectionsContainers']) {
			$webLoader->addSetup(
				'createFilesCollectionsContainersFromArray', [$this->config['filesCollectionsContainers']]
			);
		}

		if ($this->config['pathPlaceholderDelimiter']) {
			$webLoader->addSetup('setPathPlaceholderDelimiter', [$this->config['pathPlaceholderDelimiter']]);
		}

		if ($this->config['pathsPlaceholders']) {
			$webLoader->addSetup('addPathsPlaceholders', [$this->config['pathsPlaceholders']]);
		}
	}

}
