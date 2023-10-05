<?php namespace ASMilano\Seo\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Model
 */
class Schema extends Model
{
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'arcane_seo_schemas';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

}
