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
use GoogleClosureCompiler\Compiler;



/**
 * @testCase
 */
final class CompilerTestsCase extends AbstractTestCase
{

	public function testCssCollectionLinkElement(): void
	{
		$collectionName = 'test-css-files-collection-link-element';
		$this->createCssCollection($collectionName)->setFilters(['empty']);
		$webLoader = $this->getWebLoader();
		$webLoader->addCssFilter('empty', function (string $code) {
			return $code;
		});

		Assert::equal(
			'<link rel="stylesheet" href="/' . self::BASE_PATH . '/' . $collectionName . '.css?v=' . $this->getFilesVersion() . '">',
			$webLoader->getFilesCollectionRender()->css($collectionName)
		);

		$this->matchCssFile($collectionName, 'simple');
	}


	public function testCssCollectionStyleElement(): void
	{
		$collectionName = 'test-css-files-collection-style-element';
		$this->createCssCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->getFilesCollectionRender()->css($collectionName, ['amp-custom' => TRUE], TRUE)
		);

		$this->matchHtmlFile($collectionName);
	}


	public function testJsCollectionScriptElement(): void
	{
		$collectionName = 'test-js-files-collection-script-element';
		$this->createJsCollection($collectionName);
		$webLoader = $this->getWebLoader();

		Assert::equal(
			'<script async src="/' . self::BASE_PATH . '/' . $collectionName . '.js?v=' . $this->getFilesVersion() . '"></script>',
			$webLoader->getFilesCollectionRender()->js($collectionName, ['async' => TRUE])
		);

		$this->matchJsFile($collectionName, 'simple');
	}


	public function testJsCollectionScriptElementWithContent(): void
	{
		$collectionName = 'test-js-files-collection-script-element-with-content';
		$this->createJsCollection($collectionName);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionName . '.html',
			$this->getWebLoader()->getFilesCollectionRender()->js($collectionName, [], TRUE)
		);

		$this->matchHtmlFile($collectionName);
	}


	public function testFilesCollectionsFromConfig(): void
	{
		$collectionNameA = 'test-files-collections-from-config-a';
		$collectionNameB = 'test-files-collections-from-config-b';

		$webLoader = $this->getWebLoader()
			->createFilesCollectionsFromConfig('%configsDir%/webloader.front.collections.neon');

		$webLoader->addJsFilter('googleClosureCompiler', function (string $code) {
			$closureCompiler = new Compiler;
			$response = $closureCompiler->setJsCode($code)->compile();

			if ($response) {
				return $response->getCompiledCode();
			}

			return $code;
		});

		$render = $webLoader->getFilesCollectionRender();

		Assert::equal(
			'<link rel="stylesheet" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.css?v=' . $this->getFilesVersion() . '">',
			$render->css($collectionNameA)
		);

		Assert::equal(
			'<script defer async src="/' . self::BASE_PATH . '/' . $collectionNameB . '.js?v=' . $this->getFilesVersion() . '"></script>',
			$render->js($collectionNameB, ['defer' => TRUE])
		);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionNameA . '.html',
			$render->js($collectionNameA)
		);

		file_put_contents(
			self::ACTUAL_DIR . '/' . $collectionNameB . '.html',
			$render->css($collectionNameB)
		);

		$this->matchCssFile($collectionNameA);
		$this->matchJsFile($collectionNameB);
		$this->matchHtmlFile($collectionNameA);
		$this->matchHtmlFile($collectionNameB);
	}


	public function testFilesCollectionsContainerFromConfig(): void
	{
		$collectionNameA = 'test-files-collections-container-from-config-a';
		$collectionNameB = 'test-files-collections-container-from-config-b';

		$webLoader = $this->getWebLoader()
			->createFilesCollectionsContainersFromConfig('%configsDir%/webloader.containers.neon')
			->createFilesCollectionsFromConfig('%configsDir%/webloader.admin.collections.neon');

		$webLoader->addJsFilter('googleClosureCompiler', function (string $code) {
			$closureCompiler = new \GoogleClosureCompiler\Compiler;
			$response = $closureCompiler->setJsCode($code)->compile();

			if ($response && $response->isWithoutErrors()) {
				return $response->getCompiledCode();
			}

			return $code;
		});

		$webLoader->addCssFilter('cssMin', function (string $code) {
			$minifier = new Minifier;
			return $minifier->run($code);
		});

		$render = $webLoader->getFilesCollectionsContainerRender()->selectContainer('testContainer');

		$collectionALink = '<link rel="stylesheet" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.css?v=' . $this->getFilesVersion() . '">';
		$collectionBLink = '<link rel="stylesheet" href="/' . self::BASE_PATH . '/' . $collectionNameB . '.css?v=' . $this->getFilesVersion() . '">';
		Assert::equal($collectionALink . $collectionBLink, $render->css());

		$collectionALink = '<link rel="prefetch" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.css?v=' . $this->getFilesVersion() . '">';
		$collectionBLink = '<link rel="prefetch" href="/' . self::BASE_PATH . '/' . $collectionNameB . '.css?v=' . $this->getFilesVersion() . '">';
		Assert::equal($collectionALink . $collectionBLink, $render->cssPrefetch());

		$collectionALink = '<link rel="preload" as="style" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.css?v=' . $this->getFilesVersion() . '">';
		$collectionBLink = '<link rel="preload" as="style" href="/' . self::BASE_PATH . '/' . $collectionNameB . '.css?v=' . $this->getFilesVersion() . '">';
		Assert::equal($collectionALink . $collectionBLink, $render->cssPreload());

		$collectionALink = '<script async defer src="/' . self::BASE_PATH . '/' . $collectionNameA . '.js?v=' . $this->getFilesVersion() . '"></script>';
		$collectionBLink = '<script async defer src="/' . self::BASE_PATH . '/' . $collectionNameB . '.js?v=' . $this->getFilesVersion() . '"></script>';
		Assert::equal($collectionALink . $collectionBLink, $render->js(NULL, ['async' => TRUE, 'defer' => TRUE]));

		$collectionALink = '<link rel="prefetch" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.js?v=' . $this->getFilesVersion() . '">';
		$collectionBLink = '<link rel="prefetch" href="/' . self::BASE_PATH . '/' . $collectionNameB . '.js?v=' . $this->getFilesVersion() . '">';
		Assert::equal($collectionALink . $collectionBLink, $render->jsPrefetch());

		$collectionALink = '<link rel="preload" as="script" href="/' . self::BASE_PATH . '/' . $collectionNameA . '.js?v=' . $this->getFilesVersion() . '">';
		$collectionBLink = '<link rel="preload" as="script" href="/' . self::BASE_PATH . '/' . $collectionNameB . '.js?v=' . $this->getFilesVersion() . '">';
		Assert::equal($collectionALink . $collectionBLink, $render->jsPreload());

		$this->matchCssFile($collectionNameA);
		$this->matchJsFile($collectionNameB);
	}


	public function testRemoteFilesLoading(): void
	{
		$collectionName = 'test-remote-files-loading';

		$collection = $this->getWebLoader()->createFilesCollectionsFromConfig('%configsDir%/webloader.remote.collections.neon');
		$collection->getFilesCollectionRender()->css($collectionName);

		$this->matchCssFile($collectionName);
	}

}

(new CompilerTestsCase())->run();
