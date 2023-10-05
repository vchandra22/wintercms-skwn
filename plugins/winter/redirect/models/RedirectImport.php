<?php

declare(strict_types=1);

namespace Winter\Redirect\Models;

use Backend\Models\ImportModel;
use Throwable;
use Winter\Redirect\Classes\PublishManager;
use Winter\Storm\Argon\Argon;

final class RedirectImport extends ImportModel
{
    public $table = 'winter_redirect_redirects';

    /**
     * Basic validation rules.
     * More (conditional) rules will be applied when importing.
     */
    public array $rules = [
        'from_url' => 'required',
        'match_type' => 'required|in:exact,placeholders,regex',
        'target_type' => 'required|in:path_or_url,cms_page,static_page,none',
        'status_code' => 'required|in:301,302,303,404,410',
    ];

    private static array $nullableAttributes = [
        'category_id',
        'from_date',
        'to_date',
        'last_used_at',
        'to_url',
        'test_url',
        'cms_page',
        'static_page',
        'requirements',
        'test_lab_path',
    ];

    private static array $dateAttributes = [
        'from_date',
        'to_date',
    ];

    private static array $dateTimeAttributes = [
        'last_used_at',
        'created_at',
        'updated_at',
    ];

    public function importData($results, $sessionKey = null)
    {
        foreach ((array) $results as $row => $data) {
            try {
                $source = Redirect::make();

                $except = ['id'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    if ($attribute === 'requirements') {
                        $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } elseif (!empty($value) && in_array($attribute, self::$dateAttributes)) {
                        $value = Argon::parse($value)->toDateString();
                    } elseif (!empty($value) && in_array($attribute, self::$dateTimeAttributes)) {
                        $value = Argon::parse($value)->toDateTimeString();
                    } elseif (empty($value) && in_array($attribute, self::$nullableAttributes, true)) {
                        $value = null;
                    }

                    $source->setAttribute($attribute, $value);
                }

                $source->save();

                $this->logCreated();
            } catch (Throwable $e) {
                $this->logError($row, $e->getMessage());
            }
        }

        $createCount = $this->resultStats['created'] ?? 0;

        if ($createCount === 0) {
            return;
        }

        /** @var PublishManager $publishManager */
        $publishManager = resolve(PublishManager::class);
        $publishManager->publish();
    }
}
