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
	private $filesCollectionsContainers = [];

	/**
	 * @var FilesCollectionRender
	 */
	private $render;

	/**
	 * @var FilesCollectionsContainer
	 */
	private $selectedContainer;


	/**
	 * @param FilesCollectionsContainer[][] $filesCollections
	 */
	public function __construct(FilesCollectionRender $render, array $filesCollectionsContainers)
	{
		$this->filesCollectionsContainers = $filesCollectionsContainers;
		$this->render = $render;
	}


	public function css(array $attributes = NULL,  bool $content = FALSE): string
	{
		$cssElements = '';

		foreach ($this->selectedContainer->getCssFilesCollections() as $collection) {
			$cssElements .= $this->render->css($collection, $attributes, $content);
		}

		return $cssElements;
	}


	public function cssPrefetch(array $collectionsNames): string
	{
		$collectionsNames = $collectionsNames ?? [];
		$cssCollectionsFromContainer = $this->selectedContainer->getCssFilesCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);
		return $this->render->cssPrefetch($cssCollectionsNames);
	}


	public function cssPreload(array $collectionsNames): string
	{
		$collectionsNames = $collectionsNames ?? [];
		$cssCollectionsFromContainer = $this->selectedContainer->getCssFilesCollections();
		$cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);
		return $this->render->cssPreload($cssCollectionsNames);
	}


	public function js(array $attributes = NULL,  bool $content = FALSE): string
	{
		$jsElements = '';

		foreach ($this->selectedContainer->getJsFilesCollections() as $collection) {
			$jsElements .= $this->render->js($collection, $attributes, $content);
		}

		return $jsElements;
	}


	public function jsPrefetch(array $collectionsNames = NULL): string
	{
		$collectionsNames = $collectionsNames ?? [];
		$jsCollectionsFromContainer = $this->selectedContainer->getJsFilesCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);
		return $this->render->jsPrefetch($jsCollectionsNames);
	}


	public function jsPreload(array $collectionsNames): string
	{
		$collectionsNames = $collectionsNames ?? [];
		$jsCollectionsFromContainer = $this->selectedContainer->getJsFilesCollections();
		$jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);
		return $this->render->jsPreload($jsCollectionsNames);
	}


	public function selectContainer(string $name): FilesCollectionsContainerRender
	{
		$this->selectedContainer = $this->filesCollectionsContainers[$name];

		return $this;
	}

}
