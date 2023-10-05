<?php namespace ASMilano\Seo;

use ASMilano\Seo\Classes\Helper;
use ASMilano\Seo\Classes\Schema;
use ASMilano\Seo\Components\SchemaArticle;
use ASMilano\Seo\Components\SchemaProduct;
use ASMilano\Seo\Components\SchemaVideo;
use ASMilano\Seo\Components\Seo;
use ASMilano\Seo\Models\Settings;
use Backend\Facades\BackendAuth;
use Cms\Classes\Theme;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use Twig\Extension\StringLoaderExtension;
use Winter\Storm\Support\Facades\Event;
use Winter\Storm\Support\Facades\Yaml;

/**
 * Arcane Plugin Information File
 */
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            Seo::class => 'seo',
            SchemaVideo::class => 'schemaVideo',
            SchemaArticle::class => 'schemaArticle',
            SchemaProduct::class => 'schemaProduct',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Arcane SEO settings',
                'description' => 'Configure Arcane SEO',
                'icon' => 'icon-search',
                'category' => SettingsManager::CATEGORY_CMS,
                'class' => Settings::class,
                'order' => 100,
                'permissions' => ['arcane.manage_seo'],
            ],
        ];
    }

    public function registerMarkupTags()
    {
        $twig = $this->app->make('twig.environment');

        $stringLoader = new StringLoaderExtension();
        $stringLoaderFunc = $stringLoader->getFunctions();

        $helper = new Helper();
        $schema = Schema::class;
        return [
            'filters' => [
                'arcane_seo_schema' => [$schema, 'toScript'],
                'seotitle'    => [$helper, 'generateTitle'],
                'removenulls' => [$helper, 'removeNullsFromArray'],
                'fillparams'  => [Helper::class, 'replaceUrlPlaceholders'],
                'url' => [$helper, 'url'],
            ],
            'functions' => [
                'template_from_string' => function ($template) use ($twig, $stringLoaderFunc) {
                    $callable = $stringLoaderFunc[0]->getCallable();
                    return $callable($twig, $template);
                },
            ],
        ];
    }

    public function registerPageSnippets()
    {
        return [
            SchemaVideo::class => 'schemaVideo',
            SchemaArticle::class => 'schemaArticle',
            SchemaProduct::class => 'schemaProduct',
        ];
    }

    public function register()
    {
        Event::listen('backend.form.extendFields', function ($widget) {
            if ($widget->isNested === false) {
                if (!($theme = Theme::getEditTheme())) {
                    throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
                }

                if (
                    PluginManager::instance()->hasPlugin('Winter.Pages') &&
                    $widget->model instanceof \Winter\Pages\Classes\Page
                ) {
                    $widget->removeField('viewBag[meta_title]');
                    $widget->removeField('viewBag[meta_description]');
                    $widget->addFields(array_except($this->staticSeoFields(), ['viewBag[model_class]']), 'primary');
                }

                if (
                    PluginManager::instance()->hasPlugin('Winter.Blog') &&
                    $widget->model instanceof \Winter\Blog\Models\Post
                ) {
                    $widget->addFields(
                        array_except($this->blogSeoFields(), [
                            'arcane_seo_options[model_class]',
                            'arcane_seo_options[lastmod]',
                            'arcane_seo_options[use_updated_at]',
                            'arcane_seo_options[changefreq]',
                            'arcane_seo_options[priority]',
                        ]),
                        'secondary',
                    );
                }

                if (!$widget->model instanceof \Cms\Classes\Page) {
                    return;
                }

                $widget->removeField('settings[meta_title]');
                $widget->removeField('settings[meta_description]');
                $widget->addFields($this->cmsSeoFields(), 'primary');
            }
        });
    }

    private function blogSeoFields()
    {
        return collect($this->seoFields())
            ->mapWithKeys(function ($item, $key) {
                return ["arcane_seo_options[$key]" => $item];
            })
            ->toArray();
    }

    private function staticSeoFields()
    {
        return collect($this->seoFields())
            ->mapWithKeys(function ($item, $key) {
                return ["viewBag[$key]" => $item];
            })
            ->toArray();
    }

    private function cmsSeoFields()
    {
        return collect($this->seofields())
            ->mapWithKeys(function ($item, $key) {
                return ["settings[$key]" => $item];
            })
            ->toArray();
    }

    private function seoFields()
    {
        $user = BackendAuth::getUser();
        // remove form fields when current users doesn't have access
        return array_except(
            Yaml::parseFile(plugins_path('asmilano/seo/config/seofields.yaml')),
            array_merge(
                [],
                !$user->hasPermission(['arcane.seo.og'])
                    ? ['og_title', 'og_description', 'og_image', 'og_type', 'og_ref_image']
                    : [],
                !$user->hasPermission(['arcane.seo.sitemap'])
                    ? ['enabled_in_sitemap', 'model_class', 'use_updated_at', 'lastmod', 'changefreq', 'priority']
                    : [],
                !$user->hasPermission(['arcane.seo.meta'])
                    ? [
                        'meta_title',
                        'meta_description',
                        'canonical_url',
                        'robot_index',
                        'robot_follow',
                        'robot_advanced',
                    ]
                    : [],
                !$user->hasPermission(['arcane.seo.schema']) ? ['schemas'] : [],
            ),
        );
    }
}
