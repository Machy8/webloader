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
use WebLoader\SetupException;


/**
 * @testCase
 */
final class Exceptions extends AbstractTestCase
{

	public function testDuplicatedCssFilterException()
	{
		Assert::exception(function () {
			$this->getWebloader()
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
			$this->getWebloader()
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
			$this->getWebloader()
				->addPathsPlaceholders([
					'frontCssDir' => __DIR__ . '/front/css',
				])
				->addPathsPlaceholders([
					'frontCssDir' => __DIR__ . '/front/css',
				]);
		}, SetupException::class, 'Placeholder "frontCssDir" already exists.');
	}

	/**
	public function testUndefinedFilterException()
	{
		Assert::exception(function () {
			$this->getWebloader()
				->createJsFilesCollection('test')
				->setFiles(['%jsFixtures%/exceptions.js'])
				->setFilters(['testFilter']);
		}, CompileException::class, 'Undefined filter "test".');
	}*/


	/**
	public function testFileNotFoundException()
	{
		Assert::exception(function () {
			$this->getWebloader()
				->createJsFilesCollection('test')
				->setFiles(['somefile.js']);
		}, CompileException::class, 'Undefined filter "test".');
	}*/

}

(new Exceptions())->run();
