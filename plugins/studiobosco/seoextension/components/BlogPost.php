<?php namespace StudioBosco\SeoExtension\Components;

use Cms\Classes\ComponentBase;
use Event;

class BlogPost extends ComponentBase
{

    public $page;
    public $seo_title;
    public $seo_description;
    public $seo_keywords;
    public $canonical_url;
    public $redirect_url;
    public $robot_index;
    public $robot_follow;

    public function componentDetails()
    {
        return [
            'name'        => 'studiobosco.seoextension::lang.component.blog.name',
            'description' => 'studiobosco.seoextension::lang.component.blog.description'
        ];
    }

    public function defineProperties()
    {
        return [
            "post" => [
                "title" => "data",
                "default" => "post"
            ]
        ];
    }

}
