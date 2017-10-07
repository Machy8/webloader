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

namespace WebLoader;


class FilesCollectionRender
{

	const
		LINK_ELEMENT = 'link',
		SCRIPT_ELEMENT = 'script',
		STYLE_ELEMENT = 'style';

	const
		LINK_PREFETCH = 'prefetch',
		LINK_PRELOAD = 'preload',
		LINK_PRELOAD_AS_CSS = 'style',
		LINK_PRELOAD_AS_JS = 'script';

	const
		SCRIPT_TYPE_ATTRIBUTE = 'text/javascript',
		STYLE_TYPE_ATTRIBUTE = 'text/css';

	const VERSION_MARK = '?v=';

	/**
	 * @var FilesCollection[][]
	 */
	private $filesCollections;

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var int
	 */
	private $version;


	/**
	 * @param FilesCollection[][] $filesCollections
	 */
	public function __construct(array $filesCollections, string $outputDir, string $version)
	{
		$this->filesCollections = $filesCollections;
		$this->outputDir = $outputDir;
		$this->version = $version;
	}


	public function css(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{
		$filesCollection = $this->getFilesCollection($collectionName, Compiler::CSS);
		$attributes = $attributes ?? [];
		$attributes = array_merge($attributes, $filesCollection->getAttributes());
		$attributes['type'] = self::STYLE_TYPE_ATTRIBUTE;
		$element = self::STYLE_ELEMENT;
		$filePath = $this->getCollectionFilePath($collectionName, Compiler::CSS);
		$filePathParameter = NULL;

		if ($content || $filesCollection->isContentLoadingEnabled()) {
			$filePathParameter = $filePath;

		} else {
			$element = self::LINK_ELEMENT;
			$attributes['rel'] = 'stylesheet';
			$attributes['href'] = $this->addVersionToFilePath($filePath);
		}

		return $this->generateElement($element, $attributes, $filePathParameter);
	}


	public function cssPrefetch(array $collectionsNames): string
	{
		return $this->generateMetaLinkElements($collectionsNames, Compiler::CSS, self::LINK_PREFETCH);
	}


	public function cssPreload(array $collectionsNames): string
	{
		return $this->generateMetaLinkElements(
			$collectionsNames, Compiler::CSS, self::LINK_PRELOAD, self::LINK_PRELOAD_AS_CSS
		);
	}


	public function generateMetaLinkElements(
		array $collectionsNames,
		string $collectionsType,
		string $rel,
		string $as = NULL
	): string {
		$tags = '';
		$attributes['rel'] = $rel;

		if ($as) {
			$attributes['as'] = $as;
		}

		foreach ($collectionsNames as $collectionName) {
			$filePath = $this->getCollectionFilePath($collectionName, $collectionsType);
			$attributes['href'] = $this->addVersionToFilePath($filePath);
			$tags .= $this->generateElement(self::LINK_ELEMENT, $attributes);
		}

		return $tags;
	}


	public function js(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{
		$filesCollection = $this->getFilesCollection($collectionName, Compiler::JS);
		$attributes = $attributes ?? [];
		$attributes = array_merge($attributes, $filesCollection->getAttributes());
		$attributes['type'] = self::SCRIPT_TYPE_ATTRIBUTE;
		$filePath = $this->getCollectionFilePath($collectionName, Compiler::JS);
		$filePathParameter = NULL;

		if ($content || $filesCollection->isContentLoadingEnabled()) {
			$filePathParameter = $filePath;

		} else {
			$attributes['src'] = $this->addVersionToFilePath($filePath);
		}

		return $this->generateElement(self::SCRIPT_ELEMENT, $attributes, $filePathParameter);
	}


	public function jsPrefetch(array $collectionsNames): string
	{
		return $this->generateMetaLinkElements($collectionsNames, Compiler::JS, self::LINK_PREFETCH);
	}


	public function jsPreload(array $collectionsNames): string
	{
		return $this->generateMetaLinkElements(
			$collectionsNames, Compiler::JS, self::LINK_PRELOAD, self::LINK_PRELOAD_AS_JS
		);
	}


	private function addVersionToFilePath(string $path): string
	{
		return $path . self::VERSION_MARK . $this->version;
	}


	private function generateElement(string $element, array $attributes, string $filePath = NULL): string
	{
		$tag = '<' . $element;
		$isScriptElement = $element === self::SCRIPT_ELEMENT;

		foreach ($attributes as $attribute => $value) {
			$tag .= ' ' . $attribute;

			if ($value !== TRUE) {
				$tag .= '="' . $value . '"';
			}
		}

		$tag .= ">";

		if ($filePath) {
			$tag .= $this->getFileContent($filePath);

			if ($element === self::STYLE_ELEMENT) {
				$tag .= '</style>';
			}
		}

		if ($isScriptElement) {
			$tag .= '</script>';
		}

		return $tag;
	}


	private function getFileContent(string $path): string
	{
		return "\n" . file_get_contents($path) . "\n";
	}


	private function getCollectionFilePath(string $name, string $type): string
	{
		return $this->outputDir . '/' . $name . '.' . $type;
	}


	/**
	 * @throws CompileException
	 */
	private function getFilesCollection(string $name, string $type): FilesCollection
	{
		if ( ! array_key_exists($name, $this->filesCollections[$type])) {
			throw new CompileException('Undefined files collection "' . $name . '".');
		}

		return $this->filesCollections[$type][$name];
	}

}
