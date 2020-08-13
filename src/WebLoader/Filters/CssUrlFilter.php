<?php

/**
 *
 * Copyright (c) Vladimír Macháček
 *
 * For the full copyright and license information, please view the file license.md
 * that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace WebLoader\Filters;

class CssUrlFilter
{
    private const
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
        $documentRoot = trim($documentRoot, '/');
        $outputDirPath = str_replace($documentRoot, '', trim($outputDirPath, '/'));
        $this->documentRoot = $documentRoot;
        $this->relativePathToOutputDir = '/' . str_repeat('../', substr_count($outputDirPath, '/'));
    }


    public function filter(string $code, string $filePath): string
    {
        $filePath = ltrim($filePath, '/');
        $pathInfo = str_replace($this->documentRoot, '', pathinfo($filePath)['dirname']);
        $pathInfo = trim($pathInfo, '/');

        return preg_replace_callback(self::URL_REGEXP, function ($urlMatch) use ($pathInfo) {
            $cssUrl = $urlMatch['url'];

            if (preg_match(self::ABSOLUTE_PATH_REGEXP, $cssUrl)) {
                return $urlMatch[0];
            }

            $pathInfo = preg_replace('~(/?[^/]+){' . substr_count($cssUrl, '../') . '}$~', '', $pathInfo);
            $url = "url('" . $this->relativePathToOutputDir;

            if ($pathInfo) {
                $url .= $pathInfo . '/';
            }

            $url .= str_replace('../', '', ltrim($cssUrl, '/')) . "')";

            return $url;
        }, $code);
    }
}
