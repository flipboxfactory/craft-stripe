<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\models;

use craft\base\Model;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\stripe\helpers\TransformerHelper;
use flipbox\craft\stripe\services\Cache;
use flipbox\craft\stripe\services\Connections;
use flipbox\craft\stripe\transformers\CreateUpsertPayloadFromElement;
use flipbox\craft\stripe\transformers\PopulateElementFromResponse;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public $environmentTablePostfix = '';

    /**
     * @var string
     */
    private $defaultCache = Cache::APP_CACHE;

    /**
     * @var string
     */
    private $defaultConnection = Connections::DEFAULT_CONNECTION;

    /**
     * @param string $key
     * @return $this
     */
    public function setDefaultConnection(string $key)
    {
        $this->defaultConnection = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->defaultConnection;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setDefaultCache(string $key)
    {
        $this->defaultCache = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultCache(): string
    {
        return $this->defaultCache;
    }

    /**
     * @return callable
     */
    public function getSyncUpsertPayloadTransformer(): callable
    {
        return TransformerHelper::resolveTransformer([
            'class' => CreateUpsertPayloadFromElement::class,
            'action' => 'sync'
        ]);
    }

    /**
     * @return callable
     */
    public function getSyncPopulateElementTransformer(): callable
    {
        return TransformerHelper::resolveTransformer([
            'class' => PopulateElementFromResponse::class,
            'action' => 'sync'
        ]);
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'defaultConnection',
                'defaultCache'
            ]
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'defaultConnection',
                        'defaultCache'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
