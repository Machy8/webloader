<?php
/**
 * Created by IntelliJ IDEA.
 * User: machy8
 * Date: 12.10.17
 * Time: 12:22
 */

namespace WebLoader\Filters;


class CssUrlFilter
{

	const URL_REGEXP = '~url\([\'"]*(?<url>(?!(?:data:|.*//))[^\'"]+)[\'"]*\)~U';

	/**
	 * @var string
	 */
	private $relativePathToOutputDir;


	public function __construct(string $outputDirPath, string $documentRoot = '/')
	{
		$outputDirPath = str_replace($documentRoot, '', rtrim($outputDirPath, '/'));
		$this->relativePathToOutputDir = '/' . str_repeat('../', substr_count($outputDirPath, '/'));
	}


	public function filter(string $code, string $filePath): string
	{
		$pathInfo = pathinfo($filePath)['dirname'];
		$relativePathToOutputDir = $this->relativePathToOutputDir;

		return preg_replace_callback(self::URL_REGEXP, function ($urlMatch) use ($relativePathToOutputDir, $pathInfo) {
			$url = $urlMatch['url'];
			$pathInfo = preg_replace('~/?[^/]+$~U', '', $pathInfo, substr_count($url, '../'));

			$url = $pathInfo . '/' . str_replace('../', '', ltrim($url, '/'));
			return "url('" . $relativePathToOutputDir . ltrim($url, '/') ."')";
		}, $code);
	}

}
