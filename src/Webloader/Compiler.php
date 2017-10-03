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

use Nette\Neon\Neon;


class Compiler
{

	/**
	 * @internal
	 */
	const
		CSS = 'css',
		JS = 'js';

	/**
	 * @internal
	 */
	const
		CONFIG_SECTION_CSS = self::CSS,
		CONFIG_SECTION_JS = self::JS,
		CONFIG_SECTION_FILTERS = 'filters';

	/**
	 * @var FilesCollection[]
	 */
	private $filesCollections = [];

	/**
	 * @var array
	 */
	private $filters = [
		self::CSS => [],
		self::JS => []
	];

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var int
	 */
	private $version;

	/**
	 * @var array
	 */
	private $pathsPlaceholders = [];


	public function setPathsPlaceholders(array $placeholders): Compiler
	{
		$this->pathsPlaceholders = array_merge($this->pathsPlaceholders, $placeholders);
		return $this;
	}


	public function createCollectionsFromConfig(string $file): Compiler
	{
		$fileContent = file_get_contents($file);
		$collections = Neon::decode($fileContent);

		foreach ($collections as $collectionSections) {
			if (isset($collectionSections['filters'])) {
				if (isset($collectionSections['filters']['css'])) {

				}
			}
		}

		return $this;
	}


	public function createCssFilesCollection(string $name): FilesCollection
	{
		return $this->filesCollections[] = new FilesCollection($name, Compiler::CSS);
	}


	public function createJsFilesCollection(string $name): FilesCollection
	{
		return $this->filesCollections[] = new FilesCollection($name, Compiler::JS);
	}


	public function compile()
	{
		$this->version = $this->getVersion($cached);

		if ($cached) {
			return;
		}

		foreach ($this->filesCollections as $filesCollection) {
			$file = $this->outputDir . '/' . $filesCollection->getName() . '.' . $filesCollection->getType();
			$output = $this->loadFiles($filesCollection->getFiles());

			foreach ($filesCollection->getFilters() as $filter) {
				$output = $this->filters[$filesCollection->getType()][$filter]();
			}

			file_put_contents($file, $output);
		}
	}


	public function getRender(): Render
	{
		return new Render($this->outputDir, $this->version);
	}


	public function addCssFilter(string $name, callable $filter): Compiler
	{
		if (in_array($name, $this->filters[Compiler::CSS])) {
			throw new SetupException("Css filter with name \"{$name}\" already exists.");
		}

		$this->filters[Compiler::CSS][$name] = $filter;
		return $this;
	}


	public function addJsFilter(string $name, callable $filter): Compiler
	{
		if (in_array($name, $this->filters[Compiler::JS])) {
			throw new SetupException("Js filter with name \"{$name}\" already exists.");
		}

		$this->filters[Compiler::JS][$name] = $filter;
		return $this;
	}


	public function setOutputDir(string $path): Compiler
	{
		$this->outputDir = $path;
		return $this;
	}


	private function loadFiles(array $files): string
	{
		$output = '';

		foreach ($files as $file) {
			$file = $this->replacePathsPlaceholders($file);
			$output .= file_get_contents($file);
		}

		return $output;
	}


	private function getVersion(bool &$cached = NULL): int
	{
		$lock = $this->outputDir . '/webloader.lock';

		if (file_exists($lock)) {
			$cached = TRUE;
			$time = file_get_contents($lock);

		} else {
			$time = time();
			file_put_contents($this->outputDir . '/webloader.lock', $time);
		}

		return $time;
	}


	private function replacePathsPlaceholders(string $file): string
	{
		foreach ($this->pathsPlaceholders as $pathPlaceholderKey => $path) {
			$file = str_replace('%' . $pathPlaceholderKey . '%', $path, $file);
		}

		return $file;
	}

}
