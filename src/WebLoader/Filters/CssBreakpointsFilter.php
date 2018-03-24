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

namespace WebLoader\Filters;


class CssBreakpointsFilter
{

	const
		MEDIA_QUERIES_REGULAR_EXPRESSION = '~@media(?<parameters>[^{]+){(?:[^{}]*{[^{}]*})+[^}]+}~',
		MIN_WIDTH_REGULAR_EXPRESSION = '~\(min-width\s*:\s*(?<value>\d+)\s*(?<unit>\S+)\)~';

	/**
	 * @var array
	 */
	private $breakpoints;

	/**
	 * @var bool
	 */
	private $cacheEnabled = TRUE;

	/**
	 * @var callable[]
	 */
	private $outputFilesFilters = [];


	public function __construct(array $breakpoints)
	{
		$this->breakpoints = $breakpoints;
	}



	public function filter(string $code, string $collectionPath): string
	{
		$pathInfo = pathinfo($collectionPath);
		$fileName = $pathInfo['basename'];
		$outputDir = $pathInfo['dirname'];
		$usedMediaQueries = [];
		$previousBreakpoints = NULL;
		preg_match_all(self::MEDIA_QUERIES_REGULAR_EXPRESSION, $code, $mediaQueries, PREG_SET_ORDER);

		foreach ($this->breakpoints as $filePrefix => $breakpoints) {
			$fileContent = '';
			$mediaQueriesCount = count($mediaQueries);
			$outputFilePath = $outputDir . '/' . $filePrefix . '.' . $fileName;

			if (file_exists($outputFilePath) && $this->cacheEnabled) {
				$previousBreakpoints = $breakpoints;
				continue;
			}

			for($i = 0; $i < $mediaQueriesCount; $i++) {
				$mediaQuery = $mediaQueries[$i];

				if ( ! $mediaQuery) {
					continue;
				}

				if (preg_match(self::MIN_WIDTH_REGULAR_EXPRESSION, $mediaQuery['parameters'], $minWidthMatch)) {
					$minWidthUnit = $minWidthMatch['unit'];
					$minWidthValue = $minWidthMatch['value'];

					if (in_array('*', $breakpoints) && $previousBreakpoints
						&& (int) $minWidthValue > $previousBreakpoints[$minWidthUnit][1]
						|| array_key_exists($minWidthUnit, $breakpoints)
							&& (int) $minWidthValue >= $breakpoints[$minWidthUnit][0]
							&& (int) $minWidthValue <= $breakpoints[$minWidthUnit][1]
					) {
						$fileContent .= $mediaQuery[0];
						$usedMediaQueries[] = $mediaQuery;
						$mediaQueries[$i] = NULL;
					}
				}
			}

			foreach ($this->outputFilesFilters as $outputFilesFilter) {
				$fileContent = $outputFilesFilter($fileContent);
			}

			file_put_contents($outputFilePath, $fileContent);
			$previousBreakpoints = $breakpoints;
		}

		// Override default generated file with content without min-width breakpoints
		$defaultFileContent = $code;
		foreach ($usedMediaQueries as $mediaQuery) {
			if (strpos($mediaQuery['parameters'], 'min-width') !== FALSE) {
				$defaultFileContent = str_replace($mediaQuery[0], '', $defaultFileContent);
			}
		}

		file_put_contents($collectionPath, $defaultFileContent);

		return $defaultFileContent;
	}


	public function disableCache(): CssBreakpointsFilter
	{
		$this->cacheEnabled = FALSE;
		return $this;
	}


	public function addOutputFilesFilter(callable $filter): CssBreakpointsFilter
	{
		$this->outputFilesFilters[] = $filter;
		return $this;
	}

}
