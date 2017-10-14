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

	const
		CSS = 'css',
		JS = 'js';

	const LOCK_FILE_NAME = 'webloader.lock';

	/**
	 * @var bool
	 */
	private $cacheEnabled = TRUE;

	/**
	 * @var bool
	 */
	private $compiled;

	/**
	 * @var string
	 */
	private $documentRoot;

	/**
	 * @var FilesCollectionRender
	 */
	private $filesCollectionRender;

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


	public function __construct(string $outputDir)
	{
		$this->setOutputDir($outputDir);
	}


	public function addCssFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::CSS])) {
			throw new Exception('Css filter "' . $name . '" already exists.');
		}

		$this->filters[Compiler::CSS][$name] = $filter;
		return $this;
	}


	public function addJsFilter(string $name, callable $filter): Compiler
	{
		if (array_key_exists($name, $this->filters[Compiler::JS])) {
			throw new Exception('Js filter "' . $name . '" already exists.');
		}

		$this->filters[Compiler::JS][$name] = $filter;
		return $this;
	}


	public function addPathsPlaceholders(array $placeholders): Compiler
	{
		foreach ($placeholders as $placeholder => $path) {
			if (array_key_exists($placeholder, $this->pathsPlaceholders)) {
				throw new Exception('Placeholder "' . $placeholder . '" already exists.');
			}

			$this->pathsPlaceholders[$placeholder] = $path;
		}

		return $this;
	}


	public function createCssFilesCollection(string $name): FilesCollection
	{
		if (array_key_exists($name, $this->filesCollections[self::CSS])) {
			throw new Exception('CSS files collection "' . $name . '" already exists.');
		}

		return $this->filesCollections[self::CSS][$name] = new FilesCollection;
	}


	public function createFilesCollectionsContainer(string $name): FilesCollectionsContainer
	{
		if (array_key_exists($name, $this->filesCollectionsContainers)) {
			throw new Exception('Files collections container "' . $name . '" already exists.');
		}

		return $this->filesCollectionsContainers[$name] = new FilesCollectionsContainer;
	}


	public function createFilesCollectionsContainersFromArray(array $containers): Compiler
	{
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
					throw new Exception(
						'Unknown configuration section "' . $sectionName
						. '" in files collections container "' . $containerName . '".'
					);
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


	public function createFilesCollectionsContainersFromConfig(string $configPath): Compiler
	{
		$configPath = $this->replacePathsPlaceholders($configPath);

		if ( ! file_exists($configPath)) {
			throw new Exception('Files collections containers configuration file "' . $configPath . '" not found.');
		}

		$fileContent = file_get_contents($configPath);
		$containers = Neon::decode($fileContent);
		$this->createFilesCollectionsContainersFromArray($containers);

		return $this;
	}


	public function createFilesCollectionsFromArray(array $collections): Compiler
	{
		foreach ($collections as $collectionName => $sections) {
			$cssContentLoadingEnabled = FALSE;
			$cssFiles = [];
			$cssFilters = [];
			$cssOutputElementAttributes = [];
			$jsContentLoadingEnabled = FALSE;
			$jsFiles = [];
			$jsFilters = [];
			$jsOutputElementAttributes = [];

			foreach ($sections as $sectionName => $values) {
				if ( ! $values) {
					continue;
				}

				if ($sectionName === FilesCollection::CONFIG_SECTION_CSS_FILES) {
					$cssFiles = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_CSS_FILTERS) {
					$cssFilters = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_CSS_LOAD_CONTENT && $values === TRUE) {
					$cssContentLoadingEnabled = TRUE;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_CSS_OUTPUT_ELEMENT_ATTRIBUTES) {
					$cssOutputElementAttributes = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS_FILES) {
					$jsFiles = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS_FILTERS) {
					$jsFilters = $values;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS_LOAD_CONTENT && $values === TRUE) {
					$jsContentLoadingEnabled = TRUE;

				} elseif ($sectionName === FilesCollection::CONFIG_SECTION_JS_OUTPUT_ELEMENT_ATTRIBUTES) {
					$jsOutputElementAttributes = $values;

				} else {
					throw new Exception(
						'Unknown configuration section "'
						. $sectionName . '" in files collection "' . $collectionName . '".'
					);
				}
			}

			if ($cssFiles) {
				$cssCollection = $this->createCssFilesCollection($collectionName)
					->setFiles($cssFiles)
					->setFilters($cssFilters)
					->setOutputElementAttributes($cssOutputElementAttributes);

				if ($cssContentLoadingEnabled) {
					$cssCollection->enableContentLoading();
				}
			}

			if ($jsFiles) {
				$jsCollection = $this->createJsFilesCollection($collectionName)
					->setFiles($jsFiles)
					->setFilters($jsFilters)
					->setOutputElementAttributes($jsOutputElementAttributes);

				if ($jsContentLoadingEnabled) {
					$jsCollection->enableContentLoading();
				}
			}
		}

		return $this;
	}


	public function createFilesCollectionsFromConfig(string $configPath): Compiler
	{
		$configPath = $this->replacePathsPlaceholders($configPath);

		if ( ! file_exists($configPath)) {
			throw new Exception('Files collections configuration file "' . $configPath . '" not found.');
		}

		$fileContent = file_get_contents($configPath);
		$collections = Neon::decode($fileContent);
		$this->createFilesCollectionsFromArray($collections);

		return $this;
	}


	public function createJsFilesCollection(string $name): FilesCollection
	{
		if (array_key_exists($name, $this->filesCollections[self::JS])) {
			throw new Exception('Javascript files collection "' . $name . '" already exists.');
		}

		return $this->filesCollections[self::JS][$name] = new FilesCollection;
	}


	public function disableCache(): Compiler
	{
		$this->cacheEnabled = FALSE;
		return $this;
	}


	public function getFilesCollectionRender(): FilesCollectionRender
	{
		if ( ! $this->filesCollectionRender) {
			$this->compile();
			$basePath = $this->outputDir;

			if ($this->documentRoot) {
				$basePath = str_replace($this->documentRoot, '', $this->outputDir);
			}

			$this->filesCollectionRender = new FilesCollectionRender(
				$this->filesCollections,
				$basePath,
				$this->getVersion()
			);
		}

		return $this->filesCollectionRender;
	}


	public function getFilesCollections(): array
	{
		return $this->filesCollections;
	}


	public function getFilesCollectionsContainerRender(): FilesCollectionsContainerRender
	{
		if ( ! $this->filesCollectionsContainerRender) {
			$this->compile();

			$this->filesCollectionsContainerRender = new FilesCollectionsContainerRender(
				$this->getFilesCollectionRender(),
				$this->filesCollectionsContainers
			);
		}

		return $this->filesCollectionsContainerRender;
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


	public function getPathsPlaceholders(): array
	{
		return $this->pathsPlaceholders;
	}


	public function getVersion(): string
	{
		if ( ! $this->version) {
			$lock = $this->outputDir . '/' . self::LOCK_FILE_NAME;

			if (file_exists($lock)) {
				$time = file_get_contents($lock);

			} else {
				$time = time();

				if ($this->isCacheEnabled()) {
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


	public function setDocumentRoot(string $path): Compiler
	{
		$path = rtrim($path, '/');

		if ( ! is_dir($path)) {
			throw new Exception('Given document root "' . $path . '" doesn\'t exists or is not a directory.');
		}

		$this->documentRoot = $path;
		return $this;
	}


	public function setOutputDir(string $path): Compiler
	{
		$path = rtrim($path, '/');

		if ( ! is_dir($path)) {
			throw new Exception('Given output dir "' . $path . '" doesn\'t exists or is not a directory.');
		}

		if ( ! is_writable($path)) {
			throw new Exception('Given output dir "' . $path . '" is not writable.');
		}

		$this->outputDir = $path;
		return $this;
	}


	public function setPathPlaceholderCharacter(string $character): Compiler
	{
		$this->pathPlaceholderCharacter = $character;
		return $this;
	}


	private function compile()
	{
		foreach ($this->filesCollections as $filesCollectionsType => $filesCollections) {
			foreach ($filesCollections as $filesCollectionName => $filesCollection) {
				$filePath = $this->outputDir . '/' . $filesCollectionName . '.' . $filesCollectionsType;

				if (file_exists($filePath) && $this->isCacheEnabled()) {
					continue;
				}

				$code = $this->loadCssJsFiles(
					$filesCollectionsType, $filesCollection->getFiles(), $filesCollection->getFilters()
				);

				file_put_contents($filePath, $code);
			}
		}

		$this->compiled = TRUE;
	}


	private function loadCssJsFiles(string $type, array $files, array $filters): string
	{
		$output = '';
		$filesCount = count($files);

		for ($i = 0; $i < $filesCount; $i++) {
			$file = $this->replacePathsPlaceholders($files[$i]);

			if ( ! file_exists($file)) {
				throw new Exception('File "' . $file . '" not found.');
			}

			$output .= file_get_contents($file);

			foreach ($filters as $filter) {
				if ( ! array_key_exists($filter, $this->filters[$type])) {
					throw new Exception('Undefined filter "' . $filter . '".');
				}

				$output = $this->filters[$type][$filter]($output, $file);
			}

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
