<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\controllers;

use craft\helpers\ArrayHelper;
use craft\web\Controller;
use flipbox\craft\stripe\actions\webhook\Signed;
use Stripe\Webhook;

class WebhooksController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $allowAnonymous = ['process-webhook'];

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge(
            parent::actions(),
            [
                'process-webhook' => [
                    'class' => Signed::class
                ],
                'process' => [
                    'class' => Signed::class
                ]
            ]
        );
    }
}
