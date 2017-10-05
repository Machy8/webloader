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


class Render
{

	/**
	 * @internal
	 */
	const
		LINK_ELEMENT = 'link',
		SCRIPT_ELEMENT = 'script',
		STYLE_ELEMENT = 'style';

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var int
	 */
	private $version;


	public function __construct(string $outputDir, string $version)
	{
		$this->outputDir = $outputDir;
		$this->version = $version;
	}


	public function css(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{
		$attributes = $attributes ?? [];
		$element = self::STYLE_ELEMENT;

		if ( ! $content) {
			$element = self::LINK_ELEMENT;
			$attributes['rel'] = 'stylesheet';
			$attributes['type'] = 'text/css';
		}

		return $this->generateElement($collectionName, $element, $attributes, $content, Compiler::CSS);
	}


	public function js(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{
		$attributes = $attributes ?? [];
		$attributes['type'] = 'text/javascript';

		return $this->generateElement($collectionName, self::SCRIPT_ELEMENT, $attributes, $content, Compiler::JS);
	}


	private function generateElement(
		string $collectionName,
		string $element,
		array $attributes,
		bool $content,
		string $fileExtension
	): string {
		$tag = '<' . $element;
		$filePath = $this->outputDir . '/' . $collectionName . '.' . $fileExtension;
		$isScriptElement = $element === self::SCRIPT_ELEMENT;

		if ( ! $content) {
			if ($element === self::LINK_ELEMENT) {
				$attributes['href'] = $filePath . '?v=' . $this->version;

			} elseif ($isScriptElement) {
				$attributes['src'] = $filePath . '?v=' . $this->version;
			}
		}

		foreach ($attributes as $attribute => $value) {
			$tag .= ' ' . $attribute;

			if ($value !== TRUE) {
				$tag .= '="' . $value . '"';
			}
		}

		$tag .= ">";

		if ($content) {
			$tag .= "\n" . file_get_contents($filePath) . "\n";

			if ($element === self::STYLE_ELEMENT) {
				$tag .= '</style>';
			}
		}

		if ($isScriptElement) {
			$tag .= '</script>';
		}

		return $tag;
	}

}
