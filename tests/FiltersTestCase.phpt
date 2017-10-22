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

use WebLoader\Filters\CssUrlFilter;


/**
 * @testCase
 */
final class FiltersTestCase extends AbstractTestCase
{

	public function testCssUrlFilter()
	{
		$collectionName = 'test-css-url-filter';
		$webLoader = $this->getWebLoader();
		$outputDir = $webLoader->getCompiler()->getOutputDir();
		$documentRoot = $webLoader->getCompiler()->getDocumentRoot();
		$webLoader->addCssFilter('cssUrlFilter', function (string $code, string $file) use ($outputDir, $documentRoot) {
			$filter = new CssUrlFilter($outputDir, $documentRoot);
			return $filter->filter($code, $file);
		}, TRUE);

		$webLoader->createCssFilesCollection($collectionName)
			->setFiles(['%cssFixtures%/style-c.css'])
			->setFilters(['cssUrlFilter']);

		$webLoader->getFilesCollectionRender()->css($collectionName);

		$this->matchCssFile($collectionName);
	}

}

(new FiltersTestCase())->run();
