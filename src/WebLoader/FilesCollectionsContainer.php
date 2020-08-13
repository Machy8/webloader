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

class FilesCollectionsContainer
{
    public const
        CONFIG_SECTION_CSS_COLLECTIONS = Engine::CSS . 'Collections',
        CONFIG_SECTION_JS_COLLECTIONS = Engine::JS . 'Collections';

    /**
     * @var string[]
     */
    private $cssCollections;

    /**
     * @var string[]
     */
    private $jsCollections;


    /**
     * @return string[]
     */
    public function getCssCollections(): array
    {
        return $this->cssCollections;
    }


    /**
     * @return string[]
     */
    public function getJsCollections(): array
    {
        return $this->jsCollections;
    }


    /**
     * @param string[] $collections
     */
    public function setCssCollections(array $collections): FilesCollectionsContainer
    {
        $this->cssCollections = $collections;
        return $this;
    }


    /**
     * @param string[] $collections
     */
    public function setJsCollections(array $collections): FilesCollectionsContainer
    {
        $this->jsCollections = $collections;
        return $this;
    }
}
