<?php namespace ASMilano\Seo\Updates;

use October\Rain\Database\Updates\Migration;
use System\Classes\PluginManager;
use Winter\Storm\Support\Facades\Schema;

class ExtendRainlabBlogPostsTable extends Migration
{

    public function up()
    {
        if (PluginManager::instance()->hasPlugin('Winter.Blog') &&
            !Schema::hasColumn('winter_blog_posts', 'arcane_seo_options')) {
            Schema::table('winter_blog_posts', static function ($table) {
                $table->text('arcane_seo_options')->nullable();
            });
        }
    }

    public function down()
    {
        if (PluginManager::instance()->hasPlugin('Winter.Blog') &&
            Schema::hasColumn('winter_blog_posts', 'arcane_seo_options')) {
            Schema::table('winter_blog_posts', static function ($table) {
                $table->dropColumn('arcane_seo_options');
            });
        }

    }

}
