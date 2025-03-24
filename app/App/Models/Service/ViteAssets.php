<?php

namespace App\Models\Service;

use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Nette\Utils\Json;
class ViteAssets
{
    public function __construct(
        private string $viteServer,
        private string $manifestFile,
        private bool $productionMode,
        private string $baseUrl
    ){}

    public function printTags(string $entrypoint = 'js/main.js'): void
    {
        $scripts = [];
        $styles = [];
        $baseUrl = $this->baseUrl;

        if ($this->productionMode) {
            if (file_exists($this->manifestFile)) {
                $manifest = Json::decode(FileSystem::read($this->manifestFile), JSON_OBJECT_AS_ARRAY);
                $scripts = [$manifest[$entrypoint]['file']];
                $styles = $manifest[$entrypoint]['css'] ?? [];
            } else {
                trigger_error('Missing manifest file: ' . $this->manifestFile, E_USER_WARNING);
            }

        } else {
            $baseUrl = $this->viteServer . '/';
            $scripts = ['@vite/client', $entrypoint];
        }

        foreach ($styles as $path) {
            echo Html::el('link')->rel('stylesheet')->href($baseUrl . $path);
        }

        foreach ($scripts as $path) {
            echo Html::el('script')->type('module')->src($baseUrl . $path);
        }
    }

}