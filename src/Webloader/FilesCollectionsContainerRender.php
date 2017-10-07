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


	public function css(): string
	{
		$cssElements = '';

		foreach ($this->selectedContainer->getCssFilesCollections() as $collection) {
			$cssElements .= $this->render->css($collection);
		}

		return $cssElements;
	}


	public function cssPrefetch(): string
	{
		return $this->render->cssPrefetch($this->selectedContainer->getCssFilesCollections());
	}


	public function cssPreload(): string
	{
		return $this->render->cssPreload($this->selectedContainer->getCssFilesCollections());
	}


	public function js(): string
	{
		$jsElements = '';

		foreach ($this->selectedContainer->getJsFilesCollections() as $collection) {
			$jsElements .= $this->render->js($collection);
		}

		return $jsElements;
	}


	public function jsPrefetch(): string
	{
		return $this->render->jsPrefetch($this->selectedContainer->getJsFilesCollections());
	}


	public function jsPreload(): string
	{
		return $this->render->jsPreload($this->selectedContainer->getJsFilesCollections());
	}


	public function selectContainer(string $name): FilesCollectionsContainerRender
	{
		$this->selectedContainer = $this->filesCollectionsContainers[$name];

		return $this;
	}

}
