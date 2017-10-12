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

namespace WebLoader\Tests;

use Tester\Assert;
use Tester\TestCase;
use WebLoader\Compiler;
use WebLoader\FilesCollection;


abstract class AbstractTestCase extends TestCase
{

	const PATHS_PLACEHOLDERS = [
		'cssFixtures' => './fixtures/css',
		'jsFixtures' => './fixtures/js',
		'configsDir' => './fixtures/configs'
	];

	const
		WEBTEMP_DIR = './webtemp',
		ACTUAL_DIR = self::WEBTEMP_DIR . '/actual',
		EXPECTED_DIR = self::WEBTEMP_DIR . '/expected';

	private $webloader;


	public function __construct()
	{
		if ( ! is_dir(self::ACTUAL_DIR)) {
			mkdir(self::ACTUAL_DIR);
		}
	}


	public function createCssCollection(string $name): FilesCollection
	{
		return $this->getWebLoader()
			->createCssFilesCollection($name)
			->setFiles([
				'%cssFixtures%/style-a.css',
				'%cssFixtures%/style-b.css'
			]);
	}


	public function createJsCollection(string $name): FilesCollection
	{
		return $this->getWebLoader()
			->createJsFilesCollection($name)
			->setFiles([
				'%jsFixtures%/script-a.js',
				'%jsFixtures%/script-b.js'
			]);
	}


	public function matchCssFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, Compiler::CSS);
	}


	public function matchHtmlFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, 'html');
	}


	public function matchJsFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, Compiler::JS);
	}


	public function setUp()
	{
		parent::setUp();
		$this->webloader = new Compiler(self::ACTUAL_DIR);
		$this->webloader
			->addPathsPlaceholders(self::PATHS_PLACEHOLDERS)
			->disableCache();
	}


	protected function getWebLoader(): Compiler
	{
		return $this->webloader;
	}


	private function matchFile(string $expected, string $actual, string $extension)
	{
		Assert::matchFile(
			self::EXPECTED_DIR . '/' . $expected . '.' . $extension,
			file_get_contents(self::ACTUAL_DIR . '/' . $actual . '.' . $extension)
		);
	}

}
