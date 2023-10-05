<?php namespace ASMilano\Seo\Classes;

use ASMilano\Seo\Models\Settings;
use Cms\Classes\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class Robots
{
    public static function generate()
    {
        $domain = self::getDomain();
        $content = Settings::get('robots_txt');
        $content = str_replace('$domain', $domain, $content);
        return $content;
    }

    public static function getDomain()
    {
        if (Request::secure()) {
            return 'https://' . $_SERVER['HTTP_HOST'];
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'];
        }
    }

    public static function response()
    {
        if (!Settings::get('enable_robots_txt')) {
            return App::make(Controller::class)
                ->setStatusCode(404)
                ->run('/404');
        } else {
            return Response::make(self::generate())->header('Content-Type', 'text/plain');
        }
    }
}
