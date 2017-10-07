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

	const LOCK_FILE_NAME = 'webloader.lock';

	const
		CSS = 'css',
		JS = 'js';

	/**
	 * @var bool
	 */
	private $cacheEnabled = TRUE;

	/**
	 * @var FilesCollection[][]
	 */
	private $filesCollections = [
		self::CSS => [],
		self::JS => []
	];

	/**
	 * @var FilesCollectionsContainerRender
	 */
	private $filesCollectionsContainerRender;

	/**
	 * @var FilesCollectionsContainer[][]
	 */
	private $filesCollectionsContainers = [];

	/**
	 * @var FilesCollectionRender
	 */
	private $filesCollectionRender;

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
	private $pathPlaceholderCharacter = '%';

	/**
	 * @var array
	 */
	private $pathsPlaceholders = [];

	/**
	 * @var string
	 */
	private $version;


	public function addCssFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::CSS])) {
			throw new SetupException('Css filter "' . $name . '" already exists.');
		}

		$this->filters[Compiler::CSS][$name] = $filter;

		return $this;
	}


	public function addJsFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::JS])) {
			throw new SetupException('Js filter "' . $name . '" already exists.');
		}

		$this->filters[Compiler::JS][$name] = $filter;

		return $this;
	}


	public function addPathsPlaceholders(array $placeholders): Compiler
	{
		foreach ($placeholders as $placeholder => $path) {
			if (array_key_exists($placeholder, $this->pathsPlaceholders)) {
				throw new SetupException('Placeholder "' . $placeholder . '" already exists.');
			}

			$this->pathsPlaceholders[$placeholder] = $path;
		}

		return $this;
	}


	public function compile()
	{
		if ( ! $this->outputDir) {
			throw new CompileException('Output dir is not set.');
		}

		foreach ($this->filesCollections as $filesCollectionsType => $filesCollections) {
			foreach ($filesCollections as $filesCollectionName => $filesCollection) {
				$filePath = $this->outputDir . '/' . $filesCollectionName . '.' . $filesCollectionsType;

				if (file_exists($filePath) && $this->cacheEnabled) {
					continue;
				}

				$code = $this->loadFiles($filesCollection->getFiles());

				foreach ($filesCollection->getFilters() as $filter) {
					if ( ! array_key_exists($filter, $this->filters[$filesCollectionsType])) {
						throw new CompileException('Undefined filter "' . $filter . '".');
					}

					$code = $this->filters[$filesCollectionsType][$filter]($code);
				}

				file_put_contents($filePath, $code);
			}
		}
	}


	public function createCssFilesCollection(string $name): FilesCollection
	{
		if (array_key_exists($name, $this->filesCollections[self::CSS])) {
			throw new CompileException('CSS files collection "' . $name . '" already exists.');
		}

		return $this->filesCollections[self::CSS][$name] = new FilesCollection;
	}


	public function createFilesCollectionsContainer(string $name): FilesCollectionsContainer
	{
		if (array_key_exists($name, $this->filesCollectionsContainers)) {
			throw new CompileException('Files collections container "' . $name . '" already exists.');
		}

		return $this->filesCollectionsContainers[$name] = new FilesCollectionsContainer;
	}


	public function createFilesCollectionsContainersFromConfig(string $configPath): Compiler
	{
		$configPath = $this->replacePathsPlaceholders($configPath);
		$fileContent = file_get_contents($configPath);
		$containers = Neon::decode($fileContent);

		foreach ($containers as $containerName => $sections) {
			$cssFilesCollections = NULL;
			$jsFilesCollections = NULL;

			foreach ($sections as $sectionName => $values) {
				if ( ! $values) {
					continue;
				}

				if ($sectionName === FilesCollectionsContainer::CONFIG_SECTION_CSS_COLLECTIONS) {
					$cssFilesCollections = $values;

				} elseif ($sectionName === FilesCollectionsContainer::CONFIG_SECTION_JS_COLLECTIONS) {
					$jsFilesCollections = $values;

				} else {
					throw new SetupException('Unknown configuration section "' . $sectionName . '".');
				}
			}

			if ( ! $cssFilesCollections && ! $jsFilesCollections) {
				continue;
			}

			$container = $this->createFilesCollectionsContainer($containerName);

			if ($cssFilesCollections) {
				$container->setCssFilesCollections($cssFilesCollections);
			}

			if ($jsFilesCollections) {
				$container->setJsFilesCollections($jsFilesCollections);
			}
		}

		return $this;
	}


	public function createFilesCollectionsFromConfig(string $configPath): Compiler
	{
		$configPath = $this->replacePathsPlaceholders($configPath);
		$fileContent = file_get_contents($configPath);
		$collections = Neon::decode($fileContent);

		foreach ($collections as $collectionName => $sections) {
			$cssCollection = NULL;
			$cssFilters = NULL;
			$jsCollection = NULL;
			$jsFilters = NULL;

			foreach ($sections as $sectionName => $values) {
				if ( ! $values) {
					continue;
				}

				if ($sectionName === FilesCollection::CONFIG_SECTION_CSS) {
					$cssCollection = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS) {
					$jsCollection = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_CSS_FILTERS) {
					$cssFilters = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS_FILTERS) {
					$jsFilters = $values;

				} else {
					throw new SetupException('Unknown configuration section "' . $sectionName . '".');
				}
			}

			if ($cssCollection) {
				$cssCollection = $this->createCssFilesCollection($collectionName)->setFiles($cssCollection);

				if ($cssFilters) {
					$cssCollection->setFilters($cssFilters);
				}
			}

			if ($jsCollection) {
				$jsCollection = $this->createJsFilesCollection($collectionName)->setFiles($jsCollection);

				if ($jsFilters) {
					$jsCollection->setFilters($jsFilters);
				}
			}
		}

		return $this;
	}


	public function createJsFilesCollection(string $name): FilesCollection
	{
		if (array_key_exists($name, $this->filesCollections[self::JS])) {
			throw new CompileException('Javascript files collection "' . $name . '" already exists.');
		}

		return $this->filesCollections[self::JS][$name] = new FilesCollection;
	}


	public function disableCache(): Compiler
	{
		$this->cacheEnabled = FALSE;
		return $this;
	}


	public function getFilesCollections(): array
	{
		return $this->filesCollections;
	}


	public function getFilesCollectionsContainers(): array
	{
		return $this->filesCollectionsContainers;
	}


	public function getFilters(): array
	{
		return $this->filters;
	}


	public function getOutputDir(): string
	{
		return $this->outputDir;
	}


	public function getFilesCollectionsContainerRender(): FilesCollectionsContainerRender
	{
		if ( ! $this->filesCollectionsContainerRender) {
			$this->filesCollectionsContainerRender = new FilesCollectionsContainerRender(
				$this->getFilesCollectionRender(),
				$this->filesCollectionsContainers
			);
		}

		return $this->filesCollectionsContainerRender;
	}


	public function getPathsPlaceholders(): array
	{
		return $this->pathsPlaceholders;
	}


	public function getFilesCollectionRender(): FilesCollectionRender
	{
		if ( ! $this->filesCollectionRender) {
			$this->filesCollectionRender = new FilesCollectionRender(
				$this->filesCollections,
				$this->outputDir,
				$this->getVersion()
			);
		}

		return $this->filesCollectionRender;
	}


	public function getVersion(): string
	{
		if ( ! $this->version) {
			$lock = $this->outputDir . '/' . self::LOCK_FILE_NAME;

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


	public function isCacheEnabled(): bool
	{
		return $this->cacheEnabled;
	}


	public function setOutputDir(string $path): Compiler
	{
		$this->outputDir = $path;
		return $this;
	}


	public function setPathPlaceholderCharacter(string $character): Compiler
	{
		$this->pathPlaceholderCharacter = $character;
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


	private function replacePathsPlaceholders(string $filePath): string
	{
		foreach ($this->pathsPlaceholders as $placeholder => $path) {
			$filePath = str_replace(
				$this->pathPlaceholderCharacter . $placeholder . $this->pathPlaceholderCharacter, $path, $filePath
			);
		}

		return $filePath;
	}

}
