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


/**
 * @testCase
 */
final class Compiler extends AbstractTestCase
{

	public function testFilesCollectionsFromConfig()
	{
		$collectionNameA = 'test-files-collections-from-config-a';
		$collectionNameB = 'test-files-collections-from-config-b';
		$compiler = $this->getWebLoader()->createFilesCollectionsFromConfig('%configsDirs%/webloader-a.neon');
		$version = $compiler->getVersion();
		$compiler->addJsFilter('googleClosureCompiler', function (string $code) {
			$closureCompiler = new \GoogleClosureCompiler\Compiler;
			$response = $closureCompiler->setJsCode($code)->compile();

			if ($response) {
				return $response->getCompiledCode();
			}

			return $code;
		});

		Assert::equal(
			'<link rel="stylesheet" type="text/css" href="' . self::ACTUAL_DIR . '/' . $collectionNameA . '.css?v=' . $version . '">',
			$compiler->render()->css($collectionNameA)
		);

		Assert::equal(
			'<script async defer type="text/javascript" src="' . self::ACTUAL_DIR . '/' . $collectionNameB . '.js?v=' . $version . '"></script>',
			$compiler->render()->js($collectionNameB, ['async' => TRUE, 'defer' => TRUE])
		);

		$this->matchCssFile($collectionNameA);
		$this->matchJsFile($collectionNameB);
	}


	public function testCssCollectionLinkElement()
	{
		$collectionName = 'test-css-files-collection-link-element';
		$this->createCssCollection($collectionName)->setFilters(['empty']);
		$compiler = $this->getWebLoader();
		$version = $compiler->getVersion();
		$compiler->addCssFilter('empty', function (string $code) {
			return $code;
		});

		Assert::equal(
			'<link rel="stylesheet" type="text/css" href="' . self::ACTUAL_DIR . '/' . $collectionName . '.css?v=' . $version . '">',
			$compiler->render()->css($collectionName)
		);

		$this->matchCssFile($collectionName, 'simple');
	}


	public function testCssCollectionStyleElement()
	{
		$collectionName = 'test-css-files-collection-style-element';
		$this->createCssCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->render()->css($collectionName, ['amp-custom' => TRUE], TRUE)
		);

		$this->matchHtmlFile($collectionName);
	}


	public function testJsCollectionScriptElement()
	{
		$collectionName = 'test-js-files-collection-script-element';
		$this->createJsCollection($collectionName);
		$compiler = $this->getWebLoader();
		$version = $compiler->getVersion();

		Assert::equal(
			'<script async type="text/javascript" src="' . self::ACTUAL_DIR . '/' . $collectionName . '.js?v=' . $version . '"></script>',
			$compiler->render()->js($collectionName, ['async' => TRUE])
		);

		$this->matchJsFile($collectionName, 'simple');
	}


	public function testJsCollectionScriptElementWithContent()
	{
		$collectionName = 'test-js-files-collection-script-element-with-content';
		$this->createJsCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->render()->js($collectionName, NULL, TRUE)
		);

		$this->matchHtmlFile($collectionName);
	}

}

(new Compiler())->run();
