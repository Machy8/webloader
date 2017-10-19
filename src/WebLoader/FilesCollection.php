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


class FilesCollection
{

	const
		CONFIG_SECTION_CSS_FILES = Engine::CSS . 'Files',
		CONFIG_SECTION_CSS_FILTERS = Engine::CSS . 'Filters',
		CONFIG_SECTION_CSS_LOAD_CONTENT = Engine::CSS . 'LoadContent',
		CONFIG_SECTION_CSS_OUTPUT_ELEMENT_ATTRIBUTES = Engine::CSS . 'OutputElementAttributes',
		CONFIG_SECTION_JS_FILES = Engine::JS . 'Files',
		CONFIG_SECTION_JS_FILTERS = Engine::JS . 'Filters',
		CONFIG_SECTION_JS_LOAD_CONTENT = Engine::JS . 'LoadContent',
		CONFIG_SECTION_JS_OUTPUT_ELEMENT_ATTRIBUTES = Engine::JS . 'OutputElementAttributes';

	/**
	 * @var array
	 */
	private $outputElementAttributes = [];

	/**
	 * @var string[]
	 */
	private $files = [];

	/**
	 * @var string[]
	 */
	private $filters = [];

	/**
	 * @var bool
	 */
	private $loadContent = FALSE;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $type;


	public function __construct(string $name, string $type)
	{
		$this->name = $name;
		$this->type = $type;
	}


	public function enableContentLoading(): FilesCollection
	{
		$this->loadContent = TRUE;
		return $this;
	}


	public function getOutputElementAttributes(): array
	{
		return $this->outputElementAttributes;
	}


	public function getFiles(): array
	{
		return $this->files;
	}


	public function getFilters(): array
	{
		return $this->filters;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function isContentLoadingEnabled(): bool
	{
		return $this->loadContent;
	}


	public function setFiles(array $files): FilesCollection
	{
		$this->files = $files;
		return $this;
	}


	public function setFilters(array $filters): FilesCollection
	{
		$this->filters = $filters;
		return $this;
	}


	public function setOutputElementAttributes(array $attributes): FilesCollection
	{
		$this->outputElementAttributes = $attributes;
		return $this;
	}

}
