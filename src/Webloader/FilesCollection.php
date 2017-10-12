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
		CONFIG_SECTION_CSS_OUTPUT_SETUP = Compiler::CSS . 'OutputSetup',
		CONFIG_SECTION_JS_FILES = Compiler::JS . 'Files',
		CONFIG_SECTION_JS_FILTERS = Compiler::JS . 'Filters',
		CONFIG_SECTION_JS_OUTPUT_SETUP = Compiler::JS . 'OutputSetup';

	/**
	 * @var array
	 */
	private $attributes = [];

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


	public function enableContentLoading(): FilesCollection
	{
		$this->loadContent = TRUE;
		return $this;
	}


	public function getAttributes(): array
	{
		return $this->attributes;
	}


	public function getFiles(): array
	{
		return $this->files;
	}


	public function getFilters(): array
	{
		return $this->filters;
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

}
