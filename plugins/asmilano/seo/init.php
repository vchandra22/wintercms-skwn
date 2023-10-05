<?php

use System\Classes\PluginManager;
use Winter\Storm\Support\Facades\Event;

// add js dependencies in the backend
Event::listen('backend.page.beforeDisplay', static function ($controller, $action, $params) {
    $controller->addJs('/plugins/asmilano/seo/assets/arcane.seo.js');
    $controller->addJs('https://cdn.jsdelivr.net/npm/vue');
});

// make our Post fields jsonable
if (PluginManager::instance()->hasPlugin('Winter.Blog')) {
    \Winter\Blog\Models\Post::extend(static function ($model) {
        $model->addJsonable('arcane_seo_options');
    });
}
