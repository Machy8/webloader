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

class FilesCollectionsContainerRender
{
    /**
     * @var FilesCollectionRender
     */
    private $render;

    /**
     * @var string
     */
    private $selectedContainerName;


    public function __construct(FilesCollectionRender $render)
    {
        $this->render = $render;
    }


    public function css(?string $containerName = null, array $attributes = [], bool $loadContent = false): string
    {
        $cssElements = '';
        $cssFilesCollections = $this->getSelectedContainer($containerName)->getCssCollections();

        foreach ($cssFilesCollections as $collectionName) {
            $cssElements .= $this->render->css($collectionName, $attributes, $loadContent);
        }

        return $cssElements;
    }


    public function cssPrefetch(?string $containerName = null, array $collectionsNames = []): string
    {
        $cssCollectionsFromContainer = $this->getSelectedContainer($containerName)->getCssCollections();
        $cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

        return $this->render->cssPrefetch($cssCollectionsNames);
    }


    public function cssPreload(?string $containerName = null, array $collectionsNames = []): string
    {
        $cssCollectionsFromContainer = $this->getSelectedContainer($containerName)->getCssCollections();
        $cssCollectionsNames = array_merge($cssCollectionsFromContainer, $collectionsNames);

        return $this->render->cssPreload($cssCollectionsNames);
    }


    public function js(?string $containerName = null, array $attributes = [], bool $loadContent = false): string
    {
        $jsElements = '';
        $jsFilesCollections = $this->getSelectedContainer($containerName)->getJsCollections();

        foreach ($jsFilesCollections as $collectionName) {
            $jsElements .= $this->render->js($collectionName, $attributes, $loadContent);
        }

        return $jsElements;
    }


    public function jsPrefetch(?string $containerName = null, array $collectionsNames = []): string
    {
        $jsCollectionsFromContainer = $this->getSelectedContainer($containerName)->getJsCollections();
        $jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

        return $this->render->jsPrefetch($jsCollectionsNames);
    }


    public function jsPreload(?string $containerName = null, array $collectionsNames = []): string
    {
        $jsCollectionsFromContainer = $this->getSelectedContainer($containerName)->getJsCollections();
        $jsCollectionsNames = array_merge($jsCollectionsFromContainer, $collectionsNames);

        return $this->render->jsPreload($jsCollectionsNames);
    }


    public function selectContainer(string $containerName): FilesCollectionsContainerRender
    {
        $this->selectedContainerName = $containerName;
        return $this;
    }


    private function getSelectedContainer(?string $containerName = null): FilesCollectionsContainer
    {
        if (!$containerName && !$this->selectedContainerName) {
            throw new Exception('Trying to call files collections container render on NULL.');
        }

        $containerName = $containerName ?? $this->selectedContainerName;

        return $this->render->getCompiler()->getFilesCollectionsContainer($containerName);
    }
}
