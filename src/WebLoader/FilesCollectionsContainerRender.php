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
		$cssFilesCollections = $this->getContainer($containerName)->getCssCollections();

		foreach ($cssFilesCollections as $collectionName) {
			$cssElements .= $this->render->css($collectionName, $attributes, $loadContent);
		}

		return $cssElements;
	}


	public function cssPrefetch(string $containerName = NULL, array $collectionsNames = []): string
	{
		$cssCollectionsFromContainer = $this->getContainer($containerName)->getCssCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

		return $this->render->cssPrefetch($cssCollectionsNames);
	}


	public function cssPreload(string $containerName = NULL, array $collectionsNames = []): string
	{
		$cssCollectionsFromContainer = $this->getContainer($containerName)->getCssCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

		return $this->render->cssPreload($cssCollectionsNames);
	}


	public function js(string $containerName = NULL, array $attributes = [], bool $loadContent = FALSE): string
	{
		$jsElements = '';
		$jsFilesCollections = $this->getContainer($containerName)->getJsCollections();

		foreach ($jsFilesCollections as $collectionName) {
			$jsElements .= $this->render->js($collectionName, $attributes, $loadContent);
		}

		return $jsElements;
	}


	public function jsPrefetch(string $containerName = NULL, array $collectionsNames = []): string
	{
		$jsCollectionsFromContainer = $this->getContainer($containerName)->getJsCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

		return $this->render->jsPrefetch($jsCollectionsNames);
	}


	public function jsPreload(string $containerName = NULL, array $collectionsNames = []): string
	{
		$jsCollectionsFromContainer = $this->getContainer($containerName)->getJsCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

		return $this->render->jsPreload($jsCollectionsNames);
	}


	public function selectContainer(string $containerName): FilesCollectionsContainerRender
	{
		$this->selectedContainerName = $containerName;
		return $this;
	}


	private function getContainer(string $containerName = NULL): FilesCollectionsContainer
	{
		if ( ! $containerName && ! $this->selectedContainerName) {
			throw new Exception('Trying to call files collections container render on NULL.');
		}

		$containerName = $containerName ?? $this->selectedContainerName;
		return $this->collectionsContainers[$containerName];
	}

}
