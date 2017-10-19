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
use WebLoader\Engine;
use WebLoader\FilesCollection;


abstract class AbstractTestCase extends TestCase
{

	const
		ACTUAL_DIR = self::WEBTEMP_DIR . '/' . self::ACTUAL_DIR_NAME,
		ACTUAL_DIR_NAME = 'actual',
		BASE_PATH = self::WEBTEMP_DIR_NAME . '/' . self::ACTUAL_DIR_NAME,
		DOCUMENT_ROOT = __DIR__,
		EXPECTED_DIR = self::WEBTEMP_DIR . '/expected',
		WEBTEMP_DIR = self::DOCUMENT_ROOT . '/' . self::WEBTEMP_DIR_NAME,
		WEBTEMP_DIR_NAME = 'webtemp';

	const PATHS_PLACEHOLDERS = [
		'cssFixtures' => 'fixtures/css',
		'configsDir' => 'fixtures/configs',
		'jsFixtures' => 'fixtures/js'
	];

	/**
	 * @var string
	 */
	private $filesVersion;

	/**
	 * @var Engine
	 */
	private $webloader;


	public function __construct()
	{
		if ( ! file_exists(self::ACTUAL_DIR)) {
			mkdir(self::ACTUAL_DIR, 0777, TRUE);
		}
	}


	protected function createCssCollection(string $name): FilesCollection
	{
		return $this->getWebLoader()
			->setPathPlaceholderDelimiter('#')
			->createCssFilesCollection($name)
			->setFiles([
				'#cssFixtures#/style-a.css',
				'#cssFixtures#/style-b.css'
			]);
	}


	protected function createJsCollection(string $name): FilesCollection
	{
		return $this->getWebLoader()
			->createJsFilesCollection($name)
			->setFiles([
				'%jsFixtures%/script-a.js',
				'%jsFixtures%/script-b.js'
			]);
	}


	protected function matchCssFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, Engine::CSS);
	}


	protected function matchHtmlFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, 'html');
	}


	protected function matchJsFile(string $actual, string $expected = NULL)
	{
		$expected = $expected ?? $actual;
		$this->matchFile($expected, $actual, Engine::JS);
	}


	protected function setUp()
	{
		parent::setUp();
		$this->webloader = new Engine(self::ACTUAL_DIR);
		$this->filesVersion = $this->webloader->getCompiler()->getVersion();
		$this->webloader
			->addPathsPlaceholders(self::PATHS_PLACEHOLDERS)
			->setDocumentRoot(self::DOCUMENT_ROOT)
			->disableCache();
	}


	protected function getFilesVersion(): string
	{
		return $this->filesVersion;
	}


	protected function getWebLoader(): Engine
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
