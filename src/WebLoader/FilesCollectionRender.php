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

namespace WebLoader;

class FilesCollectionRender
{
    private const LINK_ELEMENT = 'link';
    private const SCRIPT_ELEMENT = 'script';
    private const STYLE_ELEMENT = 'style';

    private const LINK_PREFETCH = 'prefetch';
    private const LINK_PRELOAD = 'preload';
    private const LINK_PRELOAD_AS_CSS = 'style';
    private const LINK_PRELOAD_AS_JS = 'script';

    private const SCRIPT_TYPE_ATTRIBUTE = 'text/javascript';
    private const STYLE_TYPE_ATTRIBUTE = 'text/css';

    private const VERSION_MARK = '?v=';

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $selectedCollectionName;

    /**
     * @var string
     */
    private $selectedPrefix;


    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
        $this->basePath = $this->createBasePath($compiler->getDocumentRoot(), $compiler->getOutputDir());
    }


    public function css(?string $collectionName = null, array $attributes = [], bool $loadContent = false): string
    {
        $collection = $this->getSelectedCollection(Engine::CSS, $collectionName);
        $collectionName = $collection->getName();
        $this->compiler->compileFilesCollectionByType(Engine::CSS, $collectionName);
        $attributes = array_merge($attributes, $collection->getOutputElementAttributes());
        $attributes['type'] = self::STYLE_TYPE_ATTRIBUTE;
        $element = self::STYLE_ELEMENT;
        $basePath = $this->getCollectionBasePath($collectionName, Engine::CSS);
        $filePathParameter = null;

        if ($loadContent || $collection->isContentLoadingEnabled()) {
            $filePathParameter = $this->getCollectionFilePath($basePath);
        } else {
            $element = self::LINK_ELEMENT;
            $attributes['rel'] = 'stylesheet';
            $basePath = $this->compiler->getPublicPathPrefix() . $basePath;
            $attributes['href'] = $this->addVersionToBasePath($basePath);
        }

        return $this->generateElement($element, $attributes, $filePathParameter);
    }


    /**
     * @param array|string|NULL $collectionsNames
     * @return string
     */
    public function cssPrefetch($collectionsNames = null): string
    {
        return $this->generateMetaLinkElements(Engine::CSS, self::LINK_PREFETCH, $collectionsNames);
    }


    /**
     * @param array|string|NULL $collectionsNames
     * @return string
     */
    public function cssPreload($collectionsNames = null): string
    {
        return $this->generateMetaLinkElements(
            Engine::CSS,
            self::LINK_PRELOAD,
            $collectionsNames,
            self::LINK_PRELOAD_AS_CSS
        );
    }


    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }


    public function js(?string $collectionName = null, array $attributes = [], bool $loadContent = false): string
    {
        $collection = $this->getSelectedCollection(Engine::JS, $collectionName);
        $collectionName = $collection->getName();
        $this->compiler->compileFilesCollectionByType(Engine::JS, $collectionName);
        $attributes = array_merge($attributes, $collection->getOutputElementAttributes());
        $attributes['type'] = self::SCRIPT_TYPE_ATTRIBUTE;
        $basePath = $this->getCollectionBasePath($collectionName, Engine::JS);
        $filePathParameter = null;

        if ($loadContent || $collection->isContentLoadingEnabled()) {
            $filePathParameter = $this->getCollectionFilePath($basePath);
        } else {
            $basePath = $this->compiler->getPublicPathPrefix() . $basePath;
            $attributes['src'] = $this->addVersionToBasePath($basePath);
        }

        return $this->generateElement(self::SCRIPT_ELEMENT, $attributes, $filePathParameter);
    }


    /**
     * @param array|string|NULL $collectionsNames
     * @return string
     */
    public function jsPrefetch($collectionsNames = null): string
    {
        return $this->generateMetaLinkElements(Engine::JS, self::LINK_PREFETCH, $collectionsNames);
    }


    /**
     * @param array|string|NULL $collectionsNames
     * @return string
     */
    public function jsPreload($collectionsNames = null): string
    {
        return $this->generateMetaLinkElements(
            Engine::JS,
            self::LINK_PRELOAD,
            $collectionsNames,
            self::LINK_PRELOAD_AS_JS
        );
    }


    public function selectCollection(?string $collectionName = null): FilesCollectionRender
    {
        $this->selectedCollectionName = $collectionName;
        return $this;
    }


    public function setPrefix(?string $prefix = null): FilesCollectionRender
    {
        $this->selectedPrefix = $prefix;
        return $this;
    }


    private function addVersionToBasePath(string $path): string
    {
        return $path . self::VERSION_MARK . $this->compiler->getVersion();
    }


    private function createBasePath(string $documentRoot, string $outputDir): string
    {
        $basePath = $outputDir;

        if ($documentRoot) {
            $basePath = str_replace($documentRoot, '', $outputDir);
        }

        return trim($basePath, '/');
    }


    private function generateElement(string $element, array $attributes, ?string $filePath = null): string
    {
        $tag = '<' . $element;

        foreach ($attributes as $attribute => $value) {
            $tag .= ' ' . $attribute;

            if ($value !== true) {
                $tag .= '="' . $value . '"';
            }
        }

        $tag .= ">";

        if ($filePath) {
            $tag .= $this->getFileContent($filePath);
        }

        if ($element !== self::LINK_ELEMENT) {
            $tag .= '</' . $element . '>';
        }

        $this->selectedPrefix = null;

        return $tag;
    }


    /**
     * @param array|string|NULL $collectionsNames
     */
    private function generateMetaLinkElements(
        string $collectionsType,
        string $rel,
        $collectionsNames = null,
        ?string $as = null
    ): string {
        $tags = '';
        $attributes['rel'] = $rel;

        if ($as) {
            $attributes['as'] = $as;
        }

        if (!$collectionsNames) {
            $collectionsNames[] = $this->getSelectedCollection($collectionsType)->getName();
        } elseif (is_string($collectionsNames)) {
            $collectionsNames = [$this->getSelectedCollection($collectionsType, $collectionsNames)->getName()];
        } else {
            foreach ($collectionsNames as $collectionName) {
                $this->compiler->getFilesCollection($collectionsType, $collectionName);
            }
        }

        foreach ($collectionsNames as $collectionName) {
            $basePath = $this->getCollectionBasePath($collectionName, $collectionsType);
            $basePath = $this->compiler->getPublicPathPrefix() . $basePath;
            $attributes['href'] = $this->addVersionToBasePath($basePath);
            $tags .= $this->generateElement(self::LINK_ELEMENT, $attributes);
        }

        return $tags;
    }


    private function getCollectionBasePath(string $collectionName, string $type): string
    {
        if ($this->selectedPrefix) {
            $collectionName = $this->selectedPrefix . '.' . $collectionName;
        }

        $basePath = '/' . $collectionName . '.' . $type;

        if ($this->basePath) {
            $basePath = '/' . $this->basePath . $basePath;
        }

        return $basePath;
    }


    private function getCollectionFilePath(string $path): string
    {
        if ($this->compiler->getDocumentRoot()) {
            $path = rtrim($this->compiler->getDocumentRoot(), '/') . $path;
        }

        return $path;
    }


    private function getFileContent(string $path): string
    {
        return "\n" . file_get_contents($path) . "\n";
    }


    private function getSelectedCollection(string $type, ?string $collectionName = null): FilesCollection
    {
        if (!$collectionName && !$this->selectedCollectionName) {
            throw new Exception('Trying to call files collection render on NULL.');
        }

        $collectionName = $collectionName ?? $this->selectedCollectionName;

        return $this->compiler->getFilesCollection($type, $collectionName);
    }
}
