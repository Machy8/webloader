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

namespace WebLoader;


class Engine
{

	public const
		CSS = 'css',
		JS = 'js';

	/**
	 * @var Compiler
	 */
	private $compiler;

	/**
	 * @var FilesCollectionRender
	 */
	private $filesCollectionRender;

	/**
	 * @var FilesCollectionsContainerRender
	 */
	private $filesCollectionsContainerRender;


	public function __construct(string $outputDir, string $documentRoot = '/', ?string $hostUrl = NULL)
	{
		$this->getCompiler()
			->setOutputDir($outputDir)
			->setDocumentRoot($documentRoot)
			->setHostUrl($hostUrl);
	}


	public function addCssFilter(string $name, callable $filter, ?bool $forEachFile = NULL): Engine
	{
		$this->getCompiler()->addFilter(Engine::CSS, $name, $filter, $forEachFile);
		return $this;
	}


	public function addJsFilter(string $name, callable $filter, ?bool $forEachFile = NULL): Engine
	{
		$this->getCompiler()->addFilter(Engine::JS, $name, $filter, $forEachFile);
		return $this;
	}


	public function addPathsPlaceholders(array $placeholders): Engine
	{
		$this->getCompiler()->addPathsPlaceholders($placeholders);
		return $this;
	}


	public function createCssFilesCollection(string $name): FilesCollection
	{
		return $this->getCompiler()->createFilesCollection(Engine::CSS, $name);
	}


	public function createFilesCollectionsContainer(string $name): FilesCollectionsContainer
	{
		return $this->getCompiler()->createFilesCollectionsContainer($name);
	}


	public function createFilesCollectionsContainersFromArray(array $containers): Engine
	{
		$this->getCompiler()->createFilesCollectionsContainersFromArray($containers);
		return $this;
	}


	public function createFilesCollectionsContainersFromConfig(string $configPath): Engine
	{
		$this->getCompiler()->createFilesCollectionsContainersFromConfig($configPath);
		return $this;
	}


	public function createFilesCollectionsFromArray(array $collections): Engine
	{
		$this->getCompiler()->createFilesCollectionsFromArray($collections);
		return $this;
	}


	public function createFilesCollectionsFromConfig(string $configPath): Engine
	{
		$this->getCompiler()->createFilesCollectionsFromConfig($configPath);
		return $this;
	}


	public function createJsFilesCollection(string $name): FilesCollection
	{
		return $this->getCompiler()->createFilesCollection(Engine::JS, $name);
	}


	public function disableCache(): Engine
	{
		$this->getCompiler()->disableCache();
		return $this;
	}


	public function getCompiler(): Compiler
	{
		if ( ! $this->compiler) {
			$this->compiler = new Compiler();
		}

		return $this->compiler;
	}


	public function getFilesCollectionRender(): FilesCollectionRender
	{
		if ( ! $this->filesCollectionRender) {
			$this->filesCollectionRender = new FilesCollectionRender($this->getCompiler());
		}

		return $this->filesCollectionRender;
	}


	public function getFilesCollectionsContainerRender(): FilesCollectionsContainerRender
	{
		if ( ! $this->filesCollectionsContainerRender) {
			$this->filesCollectionsContainerRender = new FilesCollectionsContainerRender(
				$this->getFilesCollectionRender()
			);
		}

		return $this->filesCollectionsContainerRender;
	}


	public function setDocumentRoot(string $path): Engine
	{
		$this->getCompiler()->setDocumentRoot($path);
		return $this;
	}


	public function setOutputDir(string $path): Engine
	{
		$this->getCompiler()->setOutputDir($path);
		return $this;
	}


	public function setPathPlaceholderDelimiter(string $delimiter): Engine
	{
		$this->getCompiler()->setPathPlaceholderDelimiter($delimiter);
		return $this;
	}

}
