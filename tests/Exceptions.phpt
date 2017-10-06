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

require_once 'bootstrap.php';

use Tester\Assert;
use WebLoader\CompileException;
use WebLoader\SetupException;


/**
 * @testCase
 */
final class Exceptions extends AbstractTestCase
{

	public function testDuplicatedCssFilterException()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->addJsFilter('Lorem', function () {
					return '';
				})
				->addJsFilter('Lorem', function () {
					return '';
				});
		}, SetupException::class, 'Js filter "Lorem" already exists.');
	}


	public function testDuplicatedJsFilterException()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->addCssFilter('Lorem', function ()
				{
					return '';
				})
				->addCssFilter('Lorem', function ()
				{
					return '';
				});
		}, SetupException::class, 'Css filter "Lorem" already exists.');
	}


	public function testDuplicatedPlaceholderException()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->addPathsPlaceholders([
					'frontCssDir' => __DIR__ . '/front/css',
				])
				->addPathsPlaceholders([
					'frontCssDir' => __DIR__ . '/front/css',
				]);
		}, SetupException::class, 'Placeholder "frontCssDir" already exists.');
	}


	public function testFileNotFoundException()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createJsFilesCollection('test')
				->setFiles(['somefile.js']);
			$webLoader->render()->js('test');
		}, CompileException::class, 'File "somefile.js" not found.');
	}


	public function testUndefinedFilterException()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createJsFilesCollection('test')
					->setFiles(['%cssFixtures%/style-a.css'])
					->setFilters(['test']);
			$webLoader->render()->js('test');
		}, CompileException::class, 'Undefined filter "test".');
	}

}

(new Exceptions())->run();
