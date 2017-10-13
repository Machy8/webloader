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
use WebLoader\Exception;


/**
 * @testCase
 */
final class ExceptionsTestCase extends AbstractTestCase
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
		}, Exception::class, 'Js filter "Lorem" already exists.');
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
		}, Exception::class, 'Css filter "Lorem" already exists.');
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
		}, Exception::class, 'Placeholder "frontCssDir" already exists.');
	}


	public function testFileNotFoundException()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createJsFilesCollection('test')
				->setFiles(['somefile.js']);

			$webLoader->getFilesCollectionRender();
		}, Exception::class, 'File "somefile.js" not found.');
	}


	public function testUndefinedFilterException()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createJsFilesCollection('test')
					->setFiles(['%cssFixtures%/style-a.css'])
					->setFilters(['test']);

			$webLoader->getFilesCollectionRender();
		}, Exception::class, 'Undefined filter "test".');
	}


	public function testUnknownFilesCollectionsContainerSection()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createFilesCollectionsContainersFromArray([
				'test' => [
					'csCollections' => [
						'collection'
					]
				]
			]);
			$webLoader->getFilesCollectionRender();
		}, Exception::class, 'Unknown configuration section "csCollections" in files collections container "test".');
	}


	public function testUnknownFilesCollectionSection()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createFilesCollectionsFromArray([
				'test' => [
					'csFilters' => [
						'file.css'
					]
				]
			]);
			$webLoader->getFilesCollectionRender();
		}, Exception::class, 'Unknown configuration section "csFilters" in files collection "test".');
	}


}

(new ExceptionsTestCase())->run();
