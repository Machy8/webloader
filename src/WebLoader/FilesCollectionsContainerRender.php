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


class FilesCollectionsContainerRender
{

	/**
	 * @var FilesCollectionsContainer[][]
	 */
	private $collectionsContainers = [];

	/**
	 * @var FilesCollectionRender
	 */
	private $render;

	/**
	 * @var string
	 */
	private $selectedContainerName;


	/**
	 * @param FilesCollectionsContainer[][] $collectionsContainers
	 */
	public function __construct(FilesCollectionRender $render, array $collectionsContainers)
	{
		$this->collectionsContainers = $collectionsContainers;
		$this->render = $render;
	}


	public function css(string $containerName = NULL, array $attributes = [], bool $loadContent = FALSE): string
	{
		$cssElements = '';
		$cssFilesCollections = $this->getSelectedContainer($containerName)->getCssCollections();

		foreach ($cssFilesCollections as $collectionName) {
			$cssElements .= $this->render->css($collectionName, $attributes, $loadContent);
		}

		return $cssElements;
	}


	public function cssPrefetch(string $containerName = NULL, array $collectionsNames = []): string
	{
		$cssCollectionsFromContainer = $this->getSelectedContainer($containerName)->getCssCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

		return $this->render->cssPrefetch($cssCollectionsNames);
	}


	public function cssPreload(string $containerName = NULL, array $collectionsNames = []): string
	{
		$cssCollectionsFromContainer = $this->getSelectedContainer($containerName)->getCssCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

		return $this->render->cssPreload($cssCollectionsNames);
	}


	public function js(string $containerName = NULL, array $attributes = [], bool $loadContent = FALSE): string
	{
		$jsElements = '';
		$jsFilesCollections = $this->getSelectedContainer($containerName)->getJsCollections();

		foreach ($jsFilesCollections as $collectionName) {
			$jsElements .= $this->render->js($collectionName, $attributes, $loadContent);
		}

		return $jsElements;
	}


	public function jsPrefetch(string $containerName = NULL, array $collectionsNames = []): string
	{
		$jsCollectionsFromContainer = $this->getSelectedContainer($containerName)->getJsCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

		return $this->render->jsPrefetch($jsCollectionsNames);
	}


	public function jsPreload(string $containerName = NULL, array $collectionsNames = []): string
	{
		$jsCollectionsFromContainer = $this->getSelectedContainer($containerName)->getJsCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

		return $this->render->jsPreload($jsCollectionsNames);
	}


	public function selectContainer(string $containerName): FilesCollectionsContainerRender
	{
		$this->selectedContainerName = $containerName;
		return $this;
	}


	private function getSelectedContainer(string $containerName = NULL): FilesCollectionsContainer
	{
		if ( ! $containerName && ! $this->selectedContainerName) {
			throw new Exception('Trying to call files collections container render on NULL.');
		}

		$containerName = $containerName ?? $this->selectedContainerName;

		if ( ! array_key_exists($containerName, $this->collectionsContainers)) {
			throw new Exception('Trying to get undefined files collections container "' . $containerName . '".');
		}

		return $this->collectionsContainers[$containerName];
	}

}
