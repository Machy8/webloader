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

	public function testDuplicatedCssFilesCollectionException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->createCssFilesCollection('test');
			$this->getWebLoader()->createCssFilesCollection('test');
		}, Exception::class, 'CSS files collection "test" already exists.');
	}


	public function testDuplicatedJsFilesCollectionException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->createJsFilesCollection('test');
			$this->getWebLoader()->createJsFilesCollection('test');
		}, Exception::class, 'JS files collection "test" already exists.');
	}


	public function testDuplicatedFilesCollectionsContainerException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->createFilesCollectionsContainer('test');
			$this->getWebLoader()->createFilesCollectionsContainer('test');
		}, Exception::class, 'Files collections container "test" already exists.');
	}


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
		}, Exception::class, 'JS filter "Lorem" already exists.');
	}


	public function testDuplicatedJsFilterException()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->addCssFilter('Lorem', function () {
					return '';
				})
				->addCssFilter('Lorem', function () {
					return '';
				});
		}, Exception::class, 'CSS filter "Lorem" already exists.');
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
			$webLoader->createJsFilesCollection('test')->setFiles(['somefile.js']);
			$webLoader->getCompiler()->compileAllFilesCollections();
		}, Exception::class, 'File "somefile.js" not found.');
	}


	public function testMissingFilesCollectionsContainerConfigurationFileException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->createFilesCollectionsContainersFromConfig('path/to/config.neon');
		}, Exception::class, 'Files collections containers configuration file "path/to/config.neon" not found.');
	}


	public function testMissingFilesCollectionConfigurationFileException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->createFilesCollectionsFromConfig('path/to/config.neon');
		}, Exception::class, 'Files collections configuration file "path/to/config.neon" not found.');
	}


	public function testNullFilesCollectionsContainerException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->getFilesCollectionsContainerRender()->css();
		}, Exception::class, 'Trying to call files collections container render on NULL.');
	}


	public function testNullFilesCollectionException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->getFilesCollectionRender()->jsPrefetch();
		}, Exception::class, 'Trying to call files collection render on NULL.');
	}


	public function testUndefinedFilesCollectionsContainerException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->getFilesCollectionsContainerRender()->selectContainer('test')->css();
		}, Exception::class, 'Trying to get undefined files collections container "test".');
	}


	public function testUndefinedFilesCollectionException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->getFilesCollectionRender()->selectCollection('test')->css();
		}, Exception::class, 'Trying to get undefined CSS files collection "test".');
	}


	public function testUndefinedFilterException()
	{
		Assert::exception(function () {
			$webLoader = $this->getWebLoader();
			$webLoader->createJsFilesCollection('test')
					->setFiles(['%cssFixtures%/style-a.css'])
					->setFilters(['test']);
			$webLoader->getCompiler()->compileAllFilesCollections();
		}, Exception::class, 'Undefined JS filter "test".');
	}


	public function testUnknownFilesCollectionsContainerSection()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->createFilesCollectionsContainersFromArray([
					'test' => [
						'csCollections' => [
							'collection'
						]
					]
				])
				->getFilesCollectionRender();
		}, Exception::class, 'Unknown configuration section "csCollections" in files collections container "test".');
	}


	public function testUnknownFilesCollectionSection()
	{
		Assert::exception(function () {
			$this->getWebLoader()
				->createFilesCollectionsFromArray([
					'test' => [
						'csFilters' => [
							'file.css'
						]
					]
				])
				->getFilesCollectionRender();
		}, Exception::class, 'Unknown configuration section "csFilters" in files collection "test".');
	}


	public function testWrongDocumentRootException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->setDocumentRoot('path/to/some/dir');
		}, Exception::class, 'Given document root "path/to/some/dir" doesn\'t exists or is not a directory.');
	}


	public function testWrongOutputDirException()
	{
		Assert::exception(function () {
			$this->getWebLoader()->setOutputDir('path/to/some/dir');
		}, Exception::class, 'Given output dir "path/to/some/dir" doesn\'t exists or is not a directory.');
	}

}

(new ExceptionsTestCase())->run();
