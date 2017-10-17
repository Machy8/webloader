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
		CONFIG_SECTION_CSS_FILES = Compiler::CSS . 'Files',
		CONFIG_SECTION_CSS_FILTERS = Compiler::CSS . 'Filters',
		CONFIG_SECTION_CSS_LOAD_CONTENT = Compiler::CSS . 'LoadContent',
		CONFIG_SECTION_CSS_OUTPUT_ELEMENT_ATTRIBUTES = Compiler::CSS . 'OutputElementAttributes',
		CONFIG_SECTION_JS_FILES = Compiler::JS . 'Files',
		CONFIG_SECTION_JS_FILTERS = Compiler::JS . 'Filters',
		CONFIG_SECTION_JS_LOAD_CONTENT = Compiler::JS . 'LoadContent',
		CONFIG_SECTION_JS_OUTPUT_ELEMENT_ATTRIBUTES = Compiler::JS . 'OutputElementAttributes';

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


	public function __construct(string $name)
	{
		$this->name = $name;
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
