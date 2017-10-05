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
		CONFIG_SECTION_CSS = self::CSS . 'Files',
		CONFIG_SECTION_JS = self::JS . 'Files',
		CONFIG_SECTION_CSS_FILTERS = self::CSS . 'Filters',
		CONFIG_SECTION_JS_FILTERS = self::JS . 'Filters';

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
	 * @var string
	 */
	private $version;

	/**
	 * @var array
	 */
	private $pathsPlaceholders = [];

	/**
	 * @var bool
	 */
	private $cacheEnabled = TRUE;


	public function addPathsPlaceholders(array $placeholders): Compiler
	{
		foreach ($placeholders as $placeholder => $path) {
			if (array_key_exists($placeholder, $this->pathsPlaceholders)) {
				throw new SetupException('Placeholder "' . $placeholder .'" already exists.');
			}

			$this->pathsPlaceholders[$placeholder] = $path;
		}
		return $this;
	}


	public function getPathsPlaceholders(): array
	{
		return $this->pathsPlaceholders;
	}


	public function disableCache(): Compiler
	{
		$this->cacheEnabled = FALSE;
		return $this;
	}


	public function createCollectionsFromConfig(string $file): Compiler
	{
		$file = $this->replacePathsPlaceholders($file);
		$fileContent = file_get_contents($file);
		$collections = Neon::decode($fileContent);
		$cssCollection = NULL;
		$jsCollection = NULL;
		$cssFilters = NULL;
		$jsFilters = NULL;

		foreach ($collections as $collectionName => $sections) {
			foreach($sections as $sectionName => $values) {
				if ( ! $values) {
					continue;
				}

				if ($sectionName === self::CONFIG_SECTION_CSS) {
					$cssCollection = $this->createCssFilesCollection($collectionName)->setFiles($values);

				} elseif ($sectionName === self::CONFIG_SECTION_JS) {
					$jsCollection = $this->createJsFilesCollection($collectionName)->setFiles($values);

				} elseif ($sectionName === self::CONFIG_SECTION_CSS_FILTERS) {
					$cssFilters = $values;

				} elseif ($sectionName === self::CONFIG_SECTION_JS_FILTERS) {
					$jsFilters = $values;

				} else {
					throw new SetupException('Unknown configuration section "' . $sectionName . '".');
				}
			}

			if ($cssCollection) {
				if ($cssFilters) {
					$cssCollection->setFilters($cssFilters);
				}

				$this->filesCollections[] = $cssCollection;
			}

			if ($jsCollection) {
				if ($jsFilters) {
					$jsCollection->setFilters($jsFilters);
				}

				$this->filesCollections[] = $jsCollection;
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


	public function render(): Render
	{
		if ( ! $this->outputDir) {
			throw new CompileException('Output dir is not set.');
		}

		foreach ($this->filesCollections as $filesCollection) {
			$file = $this->outputDir . '/' . $filesCollection->getName() . '.' . $filesCollection->getType();

			if (file_exists($file) && $this->cacheEnabled) {
				continue;
			}

			$code = $this->loadFiles($filesCollection->getFiles());

			foreach ($filesCollection->getFilters() as $filter) {
				if ( ! array_key_exists($filter, $this->filters[$filesCollection->getType()])) {
					throw new CompileException('Undefined filter "' . $filter . '"');
				}
				$code = $this->filters[$filesCollection->getType()][$filter]($code);
			}

			file_put_contents($file, $code);
		}
		return new Render($this->outputDir, $this->getVersion());
	}


	public function addCssFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::CSS])) {
			throw new SetupException("Css filter \"{$name}\" already exists.");
		}

		$this->filters[Compiler::CSS][$name] = $filter;
		return $this;
	}


	public function getFilters(): array
	{
		return $this->filters;
	}


	public function addJsFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::JS])) {
			throw new SetupException("Js filter \"{$name}\" already exists.");
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

		$filesCount = count($files);
		for ($i = 0; $i < $filesCount; $i++) {
			$file = $this->replacePathsPlaceholders($files[$i]);

			if ( ! file_exists($file)) {
				throw new CompileException('File "' . $file . '" not found.');
			}

			$output .= file_get_contents($file);

			if (($i + 1) < $filesCount) {
				$output .= "\n";
			}
		}

		return $output;
	}


	public function getVersion(): string
	{
		if ( ! $this->version) {
			$lock = $this->outputDir . '/webloader.lock';

			if (file_exists($lock)) {
				$time = file_get_contents($lock);

			} else {
				$time = time();

				if ($this->cacheEnabled) {
					file_put_contents($lock, $time);
				}
			}
			$this->version = (string) $time;
		}

		return $this->version;
	}


	private function replacePathsPlaceholders(string $file): string
	{
		foreach ($this->pathsPlaceholders as $pathPlaceholderKey => $path) {
			$file = str_replace('%' . $pathPlaceholderKey . '%', $path, $file);
		}

		return $file;
	}

}
