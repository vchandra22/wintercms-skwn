<?php namespace StudioBosco\SeoExtension;

use Lang;
use Winter\Storm\Exception\ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use StudioBosco\SeoExtension\Classes\Helper;
use StudioBosco\SeoExtension\Models\Settings;

/**
 * SeoExtension Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'studiobosco.seoextension::lang.plugin.name',
            'description' => 'studiobosco.seoextension::lang.plugin.description',
            'author'      => 'AnandPatel, Ondrej Brinkel <ondrej@studiobosco.de>',
            'icon'        => 'icon-search'
        ];
    }


    public function registerComponents()
    {
        return [
            'StudioBosco\SeoExtension\Components\BlogPost' => 'SeoBlogPost',
            'StudioBosco\SeoExtension\Components\StaticPage' => 'SeoStaticPage',
            'StudioBosco\SeoExtension\Components\CmsPage' => 'SeoCmsPage',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'studiobosco.seoextension::lang.settings.label',
                'description' => 'studiobosco.seoextension::lang.settings.description',
                'icon'        => 'icon-search',
                'category'    =>  SettingsManager::CATEGORY_MYSETTINGS,
                'permissions' => ['studiobosco.seoextension.settings.edit'],
                'class'       => 'StudioBosco\SeoExtension\Models\Settings',
                'order'       => 100
            ]
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'generateTitle' => [$this, 'generateTitle'],
                'generateCanonicalUrl' => [$this, 'generateCanonicalUrl'],
                'otherMetaTags' => [$this ,'otherMetaTags'],
                'generateOgTags' => [$this,'generateOgTags']
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'studiobosco.seoextension.settings.edit' => [
                'label' => 'studiobosco.seoextension::lang.settings.permissions.settings_edit',
                'tab' => 'studiobosco.seoextension::lang.plugin.name'
            ]
        ];
    }

    public function generateOgTags($post)
    {
        $helper = new Helper();

        $ogMetaTags = $helper->generateOgMetaTags($post);
        return $ogMetaTags;
    }

    public function otherMetaTags()
    {
        $helper = new Helper();

        $otherMetaTags = $helper->otherMetaTags();
        return $otherMetaTags;
    }

    public function generateTitle($title)
    {
        $helper = new Helper();
        $title = $helper->generateTitle($title);
        return $title;
    }

    public function generateCanonicalUrl($url)
    {
        $helper = new Helper();
        $canonicalUrl = $helper->generateCanonicalUrl();
        return $canonicalUrl;
    }


    public function register()
    {
        \Event::listen('backend.form.extendFields', function ($widget) {
            $isTranslatable = Helper::hasTranslatePlugin() && Helper::hasMulitpleLocales();

            if (PluginManager::instance()->hasPlugin('Winter.Pages') && $widget->model instanceof \Winter\Pages\Classes\Page && !$widget->isNested) {
                if (Settings::get('hide_static_page_meta_fields', false)) {
                    $widget->removeField('viewBag[meta_title]');
                    $widget->removeField('viewBag[meta_description]');
                }

                $widget->addFields([
                        'viewBag[seo_title]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.meta_title',
                        'type'    => $isTranslatable ? 'mltext' : 'text',
                        'tab'     => 'cms::lang.editor.meta'
                        ],
                        'viewBag[seo_description]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.meta_description',
                            'type'    => $isTranslatable ? 'mltextarea' : 'textarea',
                            'size'    => 'tiny',
                            'tab'     => 'cms::lang.editor.meta'
                        ],
                        'viewBag[seo_keywords]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.meta_keywords',
                            'type'    => $isTranslatable ? 'mltextarea' : 'textarea',
                            'size'    => 'tiny',
                            'tab'     => 'cms::lang.editor.meta'
                        ],
                        'viewBag[canonical_url]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.canonical_url',
                            'type'    => 'text',
                            'tab'     => 'cms::lang.editor.meta',
                            'span'    => 'left'
                        ],
                        'viewBag[redirect_url]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.redirect_url',
                            'type'    => $isTranslatable ? 'mltext' : 'text',
                            'tab'     => 'cms::lang.editor.meta',
                            'span'    => 'right'

                        ],
                        'viewBag[robot_index]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.robot_index',
                            'type'    => 'dropdown',
                            'tab'     => 'cms::lang.editor.meta',
                            'options' => $this->getIndexOptions(),
                            'default' => 'index',
                            'span'    => 'left'
                        ],
                        'viewBag[robot_follow]' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.robot_follow',
                            'type'    => 'dropdown',
                            'tab'     => 'cms::lang.editor.meta',
                            'options' => $this->getFollowOptions(),
                            'default' => 'follow',
                            'span'    => 'right'
                        ],
                ],
                'primary');
            }

            if (PluginManager::instance()->hasPlugin('Winter.Blog') && $widget->model instanceof \Winter\Blog\Models\Post && !$widget->isNested && Settings::get('use_for_blog_posts')) {
                $widget->addFields([
                        'seo_title' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.meta_title',
                            'type'    => $isTranslatable ? 'mltext' : 'text',
                            'tab'     => 'SEO'
                        ],
                        'seo_description' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.meta_description',
                            'type'    => $isTranslatable ? 'mltextarea' : 'textarea',
                            'size'    => 'tiny',
                            'tab'     => 'SEO'
                        ],
                        'seo_keywords' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.meta_keywords',
                            'type'    => $isTranslatable ? 'mltextarea' : 'textarea',
                            'size'    => 'tiny',
                            'tab'     => 'SEO'
                        ],
                        'canonical_url' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.canonical_url',
                            'type'    => 'text',
                            'tab'     => 'SEO',
                            'span'    => 'left'
                        ],
                        'redirect_url' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.redirect_url',
                            'type'    => $isTranslatable ? 'mltext' : 'text',
                            'tab'     => 'SEO',
                            'span'    => 'right'

                        ],
                        'robot_index' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.robot_index',
                            'type'    => 'dropdown',
                            'tab'     => 'SEO',
                            'options' => $this->getIndexOptions(),
                            'default' => 'index',
                            'span'    => 'left'
                        ],
                        'robot_follow' => [
                            'label'   => 'studiobosco.seoextension::lang.editor.robot_follow',
                            'type'    => 'dropdown',
                            'tab'     => 'SEO',
                            'options' => $this->getFollowOptions(),
                            'default' => 'follow',
                            'span'    => 'right'
                        ],
                    ],
                    'secondary');
            }

            if (!$widget->model instanceof \Cms\Classes\Page) {
                return;
            }

            if (!($theme = Theme::getEditTheme())) {
                throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
            }

            if ($widget->isNested) { return; }

            $widget->addFields(
                [
                    'settings[seo_keywords]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.meta_keywords',
                        'type'    => $isTranslatable ? 'mltextarea' : 'textarea',
                        'tab'     => 'cms::lang.editor.meta',
                        'size'    => 'tiny',
                        'placeholder' => "hello"
                    ],
                    'settings[canonical_url]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.canonical_url',
                        'type'    => 'text',
                        'tab'     => 'cms::lang.editor.meta',
                        'span'    => 'left'
                    ],
                    'settings[redirect_url]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.redirect_url',
                        'type'    => $isTranslatable ? 'mltext' : 'text',
                        'tab'     => 'cms::lang.editor.meta',
                        'span'    => 'right'

                    ],
                    'settings[robot_index]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.robot_index',
                        'type'    => 'dropdown',
                        'tab'     => 'cms::lang.editor.meta',
                        'options' => $this->getIndexOptions(),
                        'default' => 'index',
                        'span'    => 'left'
                    ],
                    'settings[robot_follow]' => [
                        'label'   => 'studiobosco.seoextension::lang.editor.robot_follow',
                        'type'    => 'dropdown',
                        'tab'     => 'cms::lang.editor.meta',
                        'options' => $this->getFollowOptions(),
                        'default' => 'follow',
                        'span'    => 'right'
                    ],
                ],
                'primary'
            );
        });
    }


    private function getIndexOptions()
    {
        return ["index"=>"index","noindex"=>"noindex"];
    }

    private function getFollowOptions()
    {
        return ["follow"=>"follow","nofollow"=>"nofollow"];
    }
}
