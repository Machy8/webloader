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

	private const LOCK_FILE_NAME = 'webloader.lock';

	/**
	 * @var bool
	 */
	private $cacheEnabled = TRUE;

	/**
	 * @var string
	 */
	private $documentRoot;

	/**
	 * @var FilesCollection[][]
	 */
	private $filesCollections = [
		Engine::CSS => [],
		Engine::JS => []
	];

	/**
	 * @var FilesCollectionsContainer[]
	 */
	private $filesCollectionsContainers = [];

	/**
	 * @var array
	 */
	private $filters = [
		Engine::CSS => [],
		Engine::JS => []
	];

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var string
	 */
	private $pathPlaceholderDelimiter = '%';

	/**
	 * @var array
	 */
	private $pathsPlaceholders = [];

	/**
	 * @var int
	 */
	private $remoteFilesLoadingTimeout = 15;

	/**
	 * @var string
	 */
	private $version;


	/**
	 * Use addFilter
	 * @deprecated
	 */
	public function addCssFilter(string $name, callable $filter, ?bool $forEachFile = NULL): Compiler
	{
		$this->addFilter(Engine::CSS, $name, $filter, $forEachFile);
		return $this;
	}


	public function addFilter(string $type, string $name, callable $filter, ?bool $forEachFile = NULL): Compiler
	{
		if ($this->filterExists($type, $name)) {
			throw new Exception(strtoupper($type) . ' filter "' . $name . '" already exists.');
		}

		$this->filters[$type][$name] = [
			'callback' => $filter,
			'forEachFile' => (bool) $forEachFile
		];

		return $this;
	}


	/**
	 * Use add filter
	 * @deprecated
	 */
	public function addJsFilter(string $name, callable $filter, ?bool $forEachFile = NULL): Compiler
	{
		$this->addFilter(Engine::JS, $name, $filter, $forEachFile);
		return $this;
	}


	public function addPathsPlaceholders(array $placeholders): Compiler
	{
		foreach ($placeholders as $placeholder => $path) {
			if ($this->pathPlaceholderExists($placeholder)) {
				throw new Exception('Placeholder "' . $placeholder . '" already exists.');
			}

			$this->pathsPlaceholders[$placeholder] = $path;
		}

		return $this;
	}


	public function compileAllFilesCollections()
	{
		foreach ($this->filesCollections as $filesCollectionsType => $filesCollections) {
			foreach ($filesCollections as $filesCollectionName => $filesCollection) {
				$this->compileSingleFilesCollection($filesCollection);
			}
		}
	}


	/**
	 * Use compileFilesCollection
	 * @deprecated
	 */
	public function compileCssFilesCollection(string $name)
	{
		$this->compileFilesCollectionByType(Engine::CSS, $name);
	}


	public function compileFilesCollectionByType(string $type, string $name)
	{
		$collection = $this->getFilesCollection($type, $name);
		$this->compileSingleFilesCollection($collection);
	}


	/**
	 * Use compileFilesCollection
	 * @deprecated
	 */
	public function compileJsFilesCollection(string $name)
	{
		$this->compileFilesCollectionByType(Engine::JS, $name);
	}


	public function createFilesCollection(string $type, string $name): FilesCollection
	{
		if ($this->filesCollectionExists($type, $name)) {
			throw new Exception(strtoupper($type) . ' files collection "' . $name . '" already exists.');
		}

		return $this->filesCollections[$type][$name] = new FilesCollection($name, $type);
	}


	/**
	 * Use createFilesCollection
	 * @deprecated
	 */
	public function createCssFilesCollection(string $name): FilesCollection
	{
		return $this->createFilesCollection(Engine::CSS, $name);
	}


	public function createFilesCollectionsContainer(string $name): FilesCollectionsContainer
	{
		if ($this->filesCollectionsContainerExists($name)) {
			throw new Exception('Files collections container "' . $name . '" already exists.');
		}

		return $this->filesCollectionsContainers[$name] = new FilesCollectionsContainer();
	}


	public function createFilesCollectionsContainersFromArray(array $containers): Compiler
	{
		foreach ($containers as $containerName => $sections) {
			$containerName = (string) $containerName;
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
				$container->setCssCollections($cssFilesCollections);
			}

			if ($jsFilesCollections) {
				$container->setJsCollections($jsFilesCollections);
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
			$collectionName = (string) $collectionName;
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
				$cssCollection = $this->createFilesCollection(Engine::CSS, $collectionName)
					->setFiles($cssFiles)
					->setFilters($cssFilters)
					->setOutputElementAttributes($cssOutputElementAttributes);

				if ($cssContentLoadingEnabled) {
					$cssCollection->enableContentLoading();
				}
			}

			if ($jsFiles) {
				$jsCollection = $this->createFilesCollection(Engine::JS, $collectionName)
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


	/**
	 * Use createFilesCollection
	 * @deprecated
	 */
	public function createJsFilesCollection(string $name): FilesCollection
	{
		return $this->createFilesCollection(Engine::JS, $name);
	}


	public function disableCache(): Compiler
	{
		$this->cacheEnabled = FALSE;
		return $this;
	}


	public function getDocumentRoot(): string
	{
		return $this->documentRoot;
	}


	public function getFilesCollection(string $type, string $name): FilesCollection
	{
		if ( ! $this->filesCollectionExists($type, $name)) {
			throw new Exception('Trying to get undefined ' . strtoupper($type) . ' files collection "' . $name . '".');
		}

		return $this->filesCollections[$type][$name];
	}


	public function getFilesCollectionsContainer(string $name): FilesCollectionsContainer
	{
		if ( ! $this->filesCollectionsContainerExists($name)) {
			throw new Exception('Trying to get undefined files collections container "' . $name . '".');
		}

		return $this->filesCollectionsContainers[$name];
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
		if ( ! is_dir($path)) {
			throw new Exception('Given document root "' . $path . '" doesn\'t exists or is not a directory.');
		}

		$this->documentRoot = rtrim($path, '/');
		return $this;
	}


	public function setOutputDir(string $path): Compiler
	{
		if ( ! is_dir($path)) {
			throw new Exception('Given output dir "' . $path . '" doesn\'t exists or is not a directory.');
		}

		if ( ! is_writable($path)) {
			throw new Exception('Given output dir "' . $path . '" is not writable.');
		}

		$this->outputDir = rtrim($path, '/');
		return $this;
	}


	public function setPathPlaceholderDelimiter(string $delimiter): Compiler
	{
		$this->pathPlaceholderDelimiter = $delimiter;
		return $this;
	}


	public function filesCollectionExists(string $type, string $name): bool
	{
		if ( ! $this->isTypeCorrect($type)) {
			throw new Exception('Unknown files collection type "' . $type . '".');
		}

		return array_key_exists($name, $this->filesCollections[$type]);
	}


	public function filesCollectionsContainerExists(string $name): bool
	{
		return array_key_exists($name, $this->filesCollectionsContainers);
	}


	public function filterExists(string $type, string $name): bool
	{
		if ( ! $this->isTypeCorrect($type)) {
			throw new Exception('Unknown filter type "' . $type . '".');
		}

		return array_key_exists($name, $this->filters[$type]);
	}


	public function pathPlaceholderExists(string $placeholder): bool
	{
		return array_key_exists($placeholder, $this->pathsPlaceholders);
	}


	public function setRemoteFilesLoadingTimeout(int $time): Compiler
	{
		$this->remoteFilesLoadingTimeout = $time;
	}


	private function compileSingleFilesCollection(FilesCollection $collection)
	{
		$filePath = $collection->getName() . '.' . $collection->getType();

		if ($this->outputDir) {
			$filePath = $this->outputDir . '/' . $filePath;
		}

		if (file_exists($filePath) && $this->isCacheEnabled()) {
			return;
		}

		$code = $this->loadFilesContent(
			$filePath, $collection->getType(), $collection->getFiles(), $collection->getFilters()
		);

		file_put_contents($filePath, $code);
	}


	private function isTypeCorrect(string $type): bool
	{
		return in_array($type, [Engine::CSS, Engine::JS]);
	}


	private function loadFilesContent(string $collectionPath, string $type, array $files, array $filters): string
	{
		$output = '';
		$filesCount = count($files);
		$oncePerCollectionFilters = [];

		for ($i = 0; $i < $filesCount; $i++) {
			$filePath = $this->replacePathsPlaceholders($files[$i]);
			$filePathIsUrl = filter_var($filePath, FILTER_VALIDATE_URL);

			if ($filePathIsUrl) {
				$context = stream_context_create(['http' => [
					'timeout' => $this->remoteFilesLoadingTimeout
				]]);
				$fileContent = @file_get_contents($filePath, FALSE, $context);

				if ($fileContent === FALSE) {
					throw new Exception('Remote file "' . $filePath . '" could not be loaded.');
				}

			} else {
				if (file_exists($filePath)) {
					$fileContent = file_get_contents($filePath);

				} else {
					throw new Exception('File "' . $filePath . '" not found.');
				}
			}

			foreach ($filters as $filter) {
				if ( ! $this->filterExists($type, $filter)) {
					throw new Exception('Undefined ' . strtoupper($type) .' filter "' . $filter . '".');
				}

				$filter = $this->filters[$type][$filter];

				if ($filter['forEachFile']) {
					$fileContent = $filter['callback']($fileContent, $filePath);

				} else {
					$oncePerCollectionFilters[] = $filter;
				}
			}

			if (($i + 1) < $filesCount) {
				$fileContent .= "\n";
			}

			$output .= $fileContent;
		}

		foreach ($oncePerCollectionFilters as $filter) {
			$filterOutput = $filter['callback']($output, $collectionPath);

			if ($filterOutput !== NULL) {
				$output = $filterOutput;
			}
		}

		return $output;
	}


	private function replacePathsPlaceholders(string $filePath): string
	{
		foreach ($this->pathsPlaceholders as $placeholder => $path) {
			$filePath = str_replace(
				$this->pathPlaceholderDelimiter . $placeholder . $this->pathPlaceholderDelimiter, $path, $filePath
			);
		}

		return $filePath;
	}

}
