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

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string[]
	 */
	private $files = [];

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string[]
	 */
	private $filters = [];


	public function __construct(string $name, string $type)
	{
		$this->type = $type;
		$this->name = $name;
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


	public function getFilters(): array
	{
		return $this->filters;
	}


	public function getFiles(): array
	{
		return $this->files;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getType(): string
	{
		return $this->type;
	}

}
