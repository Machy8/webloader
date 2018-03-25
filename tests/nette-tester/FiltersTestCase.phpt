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

use WebLoader\Filters\CssBreakpointsFilter;
use WebLoader\Filters\CssUrlFilter;


/**
 * @testCase
 */
final class FiltersTestCase extends AbstractTestCase
{

	public function testCssUrlFilter(): void
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


	public function testCssBreakpointsFilter(): void
	{
		$collectionName = 'test-css-breakpoints-filter';
		$webLoader = $this->getWebLoader();

		$webLoader->addCssFilter('cssBreakpointsFilter', function (string $code, string $collectionPath) {
			$breakpoints = [
				'medium' => [
					'px' => [640, 1023],
					'em' => [40, 63]
				],
				'large' => [
					'px' => [1024, 1119],
					'em' => [64, 87]
				],
				'extra-large' => ['*']
			];

			$filter = new CssBreakpointsFilter($breakpoints);
			return $filter->disableCache()->filter($code, $collectionPath);
		});

		$webLoader->createCssFilesCollection($collectionName)
			->setFiles(['%cssFixtures%/style-d.css'])
			->setFilters(['cssBreakpointsFilter']);

		$webLoader->getFilesCollectionRender()->css($collectionName);

		$this->matchCssFile($collectionName);
		$this->matchCssFile('medium.' . $collectionName);
		$this->matchCssFile('large.' .$collectionName);
		$this->matchCssFile('extra-large.' .$collectionName);
	}

}

(new FiltersTestCase())->run();
