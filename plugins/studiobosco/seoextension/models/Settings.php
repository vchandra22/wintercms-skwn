<?php

namespace StudioBosco\SeoExtension\models;

use Model;

class Settings extends Model{

    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'studiobosco_seoextension_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    protected $cache = [];

    public $attachOne = [
        'og_image' => ['System\Models\File']
    ];

    public function afterSave()
    {
        if (self::get('use_for_blog_posts')) {
            $migration = new \StudioBosco\SeoExtension\Database\Migrations\ExtendBlogPostsTable();
            $migration->up();
        }
    }
}
