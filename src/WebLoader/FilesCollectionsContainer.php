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


class FilesCollectionsContainer
{

	const
		CONFIG_SECTION_CSS_COLLECTIONS = Compiler::CSS . 'Collections',
		CONFIG_SECTION_JS_COLLECTIONS = Compiler::JS . 'Collections';

	/**
	 * @var string[]
	 */
	private $cssFilesCollections;

	/**
	 * @var string[]
	 */
	private $jsFilesCollections;


	/**
	 * @return string[]
	 */
	public function getCssFilesCollections(): array
	{
		return $this->cssFilesCollections;
	}


	/**
	 * @return string[]
	 */
	public function getJsFilesCollections(): array
	{
		return $this->jsFilesCollections;
	}


	/**
	 * @param string[] $collections
	 */
	public function setCssFilesCollections(array $collections): FilesCollectionsContainer
	{
		$this->cssFilesCollections = $collections;
		return $this;
	}


	/**
	 * @param string[] $collections
	 */
	public function setJsFilesCollections(array $collections): FilesCollectionsContainer
	{
		$this->jsFilesCollections = $collections;
		return $this;
	}

}
