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


class CssUrlFilter
{

	const
		URL_REGEXP = '~url\([\'"]*(?<url>(?!(?:data:|.*//))[^\'"]+)[\'"]*\)~U',
		ABSOLUTE_PATH_REGEXP = '~^/~U';

	/**
	 * @var string
	 */
	private $documentRoot;

	/**
	 * @var string
	 */
	private $relativePathToOutputDir;


	public function __construct(string $outputDirPath, string $documentRoot = '/')
	{
		$this->documentRoot = $documentRoot;
		$outputDirPath = preg_replace('~' . $documentRoot . '~', '', rtrim($outputDirPath, '/'));
		$this->relativePathToOutputDir = '/' . str_repeat('../', substr_count($outputDirPath, '/'));
	}


	public function filter(string $code, string $filePath): string
	{
		$pathInfo = preg_replace('~^' . $this->documentRoot . '~', '', pathinfo($filePath)['dirname'], 1);

		return preg_replace_callback(self::URL_REGEXP, function ($urlMatch) use ($pathInfo){
			$cssUrl = $urlMatch['url'];

			if (preg_match(self::ABSOLUTE_PATH_REGEXP, $cssUrl)) {
				return $urlMatch[0];
			}

			$pathInfo = preg_replace('~(/?[^/]+){' . substr_count($cssUrl, '../') . '}$~', '', trim($pathInfo, '/'));
			$url = "url('" . $this->relativePathToOutputDir;

			if ($pathInfo) {
				$url .= $pathInfo . '/';
			}

			$url .= str_replace('../', '', ltrim($cssUrl, '/')) . "')";

			return $url;
		}, $code);
	}

}
