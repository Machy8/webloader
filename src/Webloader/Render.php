<?php
/**
 * Created by IntelliJ IDEA.
 * User: machy8
 * Date: 03.10.17
 * Time: 10:08
 */

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

	public function __construct(string $outputDir, int $version)
	{
		$this->outputDir = $outputDir;
		$this->version = $version;
	}


	private function generateElement(string $collectionName, string $element, array $attributes, bool $content, string $fileExtension): string
	{
		$tag = '<' . $element;
		$filePath = $this->outputDir . '/' . $collectionName . '.' . $fileExtension;
		$isScriptElement = $element === self::SCRIPT_ELEMENT;

		foreach ($attributes as $attribute => $value) {
			$tag .= ' ' . $attribute . '="' . $value . '"';
		}

		if ($content) {
			$fileContent = file_get_contents($filePath);
			$tag .= ">\n" . $fileContent . "\n";

			if ($isScriptElement) {
				$tag .= '</script>';

			} else {
				$tag .= '</style>';
			}

		} elseif ($element === self::LINK_ELEMENT) {
			$tag .= ' href="' . $filePath . '?v=' . $this->version . '">';

		} elseif ($isScriptElement) {
			$tag .= 'src="' . $filePath . '?v= ' . $this->version . '"></script>';
		}

		return $tag;
	}


	public function css(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{
		$attributes = $attributes ?? [];
		$element = $content ? self::STYLE_ELEMENT : self::LINK_ELEMENT;
		return $this->generateElement($collectionName, $element, $attributes, $content, Compiler::CSS);
	}


	public function js(string $collectionName, array $attributes = NULL, bool $content = FALSE): string
	{

		$attributes= $attributes ?? [];
		$element = $content ? self::STYLE_ELEMENT : self::LINK_ELEMENT;
		return $this->generateElement($collectionName, $element, $attributes, $content, Compiler::JS);
	}

}
