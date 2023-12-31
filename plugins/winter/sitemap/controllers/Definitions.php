<?php

namespace Winter\Sitemap\Controllers;

use ApplicationException;
use Backend;
use Backend\Classes\Controller;
use BackendMenu;
use Cms\Classes\Theme;
use Exception;
use Request;
use System\Classes\SettingsManager;
use Url;
use Winter\Sitemap\Classes\DefinitionItem as SitemapItem;
use Winter\Sitemap\Models\Definition;

/**
 * Definitions Back-end Controller
 */
class Definitions extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
    ];

    public $requiredPermissions = ['winter.sitemap.access_definitions'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Winter.System', 'system', 'settings');
        SettingsManager::setContext('Winter.Sitemap', 'definitions');

        $this->addJs('/modules/backend/assets/js/winter.treeview.js', 'core');
        $this->addJs('/plugins/winter/sitemap/assets/js/sitemap-definitions.js');
    }

    /**
     * Index action. Find or create a new Definition model,
     * then redirect to the update form.
     */
    public function index()
    {
        try {
            if (!$theme = Theme::getEditTheme()) {
                throw new ApplicationException('Unable to find the active theme.');
            }

            return $this->redirectToThemeSitemap($theme);
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    /**
     * Update action. Add the theme object to the page vars.
     */
    public function update($recordId = null, $context = null)
    {
        $this->bodyClass = 'compact-container';

        try {
            if (!$editTheme = Theme::getEditTheme()) {
                throw new ApplicationException('Unable to find the active theme.');
            }

            $result = $this->asExtension('FormController')->update($recordId, $context);

            $model = $this->formGetModel();
            $theme = Theme::load($model->theme);

            /*
             * Not editing the active sitemap definition
             */
            if ($editTheme->getDirName() != $theme->getDirName()) {
                return $this->redirectToThemeSitemap($editTheme);
            }

            $this->vars['theme'] = $theme;
            $this->vars['themeName'] = $theme->getConfigValue('name', $theme->getDirName());
            $this->vars['sitemapUrl'] = Url::to('/sitemap.xml');

            return $result;
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    public function onGetItemTypeInfo(): array
    {
        $type = Request::input('type');

        return [
            'sitemapItemTypeInfo' => SitemapItem::getTypeInfo($type)
        ];
    }

    //
    // Helpers
    //

    protected function redirectToThemeSitemap($theme)
    {
        $model = Definition::firstOrCreate(['theme' => $theme->getDirName()]);
        $updateUrl = sprintf('winter/sitemap/definitions/update/%s', $model->getKey());

        return Backend::redirect($updateUrl);
    }
}
