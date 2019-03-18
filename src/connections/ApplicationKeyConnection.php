<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\connections;

use Craft;
use flipbox\craft\integration\connections\AbstractSaveableConnection;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ApplicationKeyConnection extends AbstractSaveableConnection implements SavableConnectionInterface
{
    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $publishableKey;

    /**
     * @var string|null
     */
    public $accountId;

    /**
     * @var string|null
     */
    public $version;

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return 'Application Key';
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'stripe/_components/connections/applicationKey',
            [
                'connection' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @inheritdoc
     */
    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'apiKey',
                        'publishableKey'
                    ],
                    'required'
                ],
                [
                    [
                        'apiKey',
                        'publishableKey',
                        'version'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
