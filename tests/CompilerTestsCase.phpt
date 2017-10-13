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
use tubalmartin\CssMin\Minifier;


/**
 * @testCase
 */
final class CompilerTestsCase extends AbstractTestCase
{

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
			'<link type="text/css" rel="stylesheet" href="' . self::ACTUAL_DIR . '/' . $collectionName . '.css?v=' . $version . '">',
			$compiler->getFilesCollectionRender()->css($collectionName)
		);

		$this->matchCssFile($collectionName, 'simple');
	}


	public function testCssCollectionStyleElement()
	{
		$collectionName = 'test-css-files-collection-style-element';
		$this->createCssCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->getFilesCollectionRender()->css($collectionName, ['amp-custom' => TRUE], TRUE)
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
			$compiler->getFilesCollectionRender()->js($collectionName, ['async' => TRUE])
		);

		$this->matchJsFile($collectionName, 'simple');
	}


	public function testJsCollectionScriptElementWithContent()
	{
		$collectionName = 'test-js-files-collection-script-element-with-content';
		$this->createJsCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->getFilesCollectionRender()->js($collectionName, [], TRUE)
		);

		$this->matchHtmlFile($collectionName);
	}


	public function testFilesCollectionsFromConfig()
	{
		$collectionNameA = 'test-files-collections-from-config-a';
		$collectionNameB = 'test-files-collections-from-config-b';

		$compiler = $this->getWebLoader()
			->createFilesCollectionsFromConfig('%configsDir%/webloader.front.collections.neon');
		$version = $compiler->getVersion();

		$compiler->addJsFilter('googleClosureCompiler', function (string $code) {
			$closureCompiler = new \GoogleClosureCompiler\Compiler;
			$response = $closureCompiler->setJsCode($code)->compile();

			if ($response) {
				return $response->getCompiledCode();
			}

			return $code;
		});

		$render = $compiler->getFilesCollectionRender();

		Assert::equal(
			'<link type="text/css" rel="stylesheet" href="' . self::ACTUAL_DIR . '/' . $collectionNameA . '.css?v=' . $version . '">',
			$render->css($collectionNameA)
		);

		Assert::equal(
			'<script async defer type="text/javascript" src="' . self::ACTUAL_DIR . '/' . $collectionNameB . '.js?v=' . $version . '"></script>',
			$render->js($collectionNameB, ['async' => TRUE, 'defer' => TRUE])
		);

		$this->matchCssFile($collectionNameA);
		$this->matchJsFile($collectionNameB);
	}


	public function testFilesCollectionsContainerFromConfig()
	{
		$collectionNameA = 'test-files-collections-container-from-config-a';
		$collectionNameB = 'test-files-collections-container-from-config-b';

		$compiler = $this->getWebLoader()
			->createFilesCollectionsContainersFromConfig('%configsDir%/webloader.containers.neon')
			->createFilesCollectionsFromConfig('%configsDir%/webloader.admin.collections.neon');

		$version = $compiler->getVersion();

		$compiler->addJsFilter('googleClosureCompiler', function (string $code) {
			$closureCompiler = new \GoogleClosureCompiler\Compiler;
			$response = $closureCompiler->setJsCode($code)->compile();

			if ($response) {
				return $response->getCompiledCode();
			}

			return $code;
		});

		$compiler->addCssFilter('cssMin', function (string $code) {
			$minifier = new Minifier;
			return $minifier->run($code);
		});

		$render = $compiler->getFilesCollectionsContainerRender()->selectContainer('testContainer');

		Assert::equal(
			'<link type="text/css" rel="stylesheet" href="' . self::ACTUAL_DIR . '/' . $collectionNameA . '.css?v=' . $version . '"><link type="text/css" rel="stylesheet" href="' . self::ACTUAL_DIR . '/' . $collectionNameB . '.css?v=' . $version . '">',
			$render->css()
		);

		Assert::equal(
			'<script async defer type="text/javascript" src="' . self::ACTUAL_DIR . '/' . $collectionNameA . '.js?v=' . $version . '"></script><script async defer type="text/javascript" src="' . self::ACTUAL_DIR . '/' . $collectionNameB . '.js?v=' . $version . '"></script>',
			$render->js(NULL, ['async' => TRUE, 'defer' => TRUE])
		);

		$this->matchCssFile($collectionNameA);
		$this->matchJsFile($collectionNameB);
	}

}

(new CompilerTestsCase())->run();
