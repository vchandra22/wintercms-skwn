<?php namespace ASMilano\Seo\Classes;

use ASMilano\Seo\Models\Settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use System\Classes\PluginManager;
use Winter\Storm\Exception\ApplicationException;

class Helper
{
    public $settings;

    public function __construct()
    {
        $this->settings = Settings::instance();
    }

    public function generateTitle($title)
    {
        $settings = $this->settings;
        $new_title = '';

        $position = $settings->site_name_position;
        $site_name = $settings->site_name;

        if ($position == 'prefix') {
            $new_title = "$site_name {$settings->site_name_separator} $title";
        } else {
            if ($position == 'suffix') {
                $new_title = "{$title} {$settings->site_name_separator} {$site_name}";
            } else {
                $new_title = $title;
            }
        }

        return $new_title;
    }

    public function removeNullsFromArray($array)
    {
        if (!is_array($array)) {
            throw new \ApplicationException('removenulls can only accept an array as argument');
        }

        return array_filter($array);
    }

    public static function replaceUrlPlaceholders($url, $model)
    {
        if (!is_string($url)) {
            throw new \ApplicationException("Parameter \$url must be a string");
        }
        $params = [];
        preg_match_all('/:(\w+)/', $url, $params, PREG_SET_ORDER);
        $extract = array_pluck($params, '1', '0'); // ex: [':slug' => 'slug' ]

        $replacedUrl = $url;

        foreach ($extract as $param => $prop) {
            $replacedUrl = str_replace_first($param, $model->$prop, $replacedUrl);
        }

        return $replacedUrl;
    }

    public function url($str)
    {
        return $str ? url($str) : Request::url();
    }

    public static function w3cDatetime($date_str)
    {
        return (new Carbon($date_str))->format('c');
    }

    public static function rainlabPagesExists()
    {
        return PluginManager::instance()->hasPlugin('Winter.Pages');
    }

    public static function parseIfTwigSyntax($str, $env = null)
    {
        $str = trim($str);

        $isTwig = starts_with($str, '{{') && ends_with($str, '}}');

        if (!$isTwig) {
            return $str;
        }

        return self::parseTwig($str, $env);
    }

    public static function parseTwig($twigString, $env = null)
    {
        try {
            $env = $env ?: App::make(\Cms\Classes\Controller::class)->vars;

            return (new \October\Rain\Parse\Twig())->parse($twigString, $env);
        } catch (\Exception $ex) {
            throw new ApplicationException($ex->getMessage() . " ---> $twigString ");
        }
    }
}
