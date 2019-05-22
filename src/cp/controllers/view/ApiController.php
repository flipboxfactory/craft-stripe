<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\cp\controllers\view;

use craft\helpers\ArrayHelper;
use flipbox\craft\stripe\records\Connection;
use flipbox\craft\stripe\Stripe;
use Craft;
use Stripe\Account;
use Stripe\ApiResource;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\StripeObject;
use yii\helpers\Json;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ApiController extends AbstractController
{
    /**
     * The template base path
     */
    const TEMPLATE_BASE = parent::TEMPLATE_BASE . '/api';

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = self::TEMPLATE_BASE . '/index';

    /**
     * The item view template path
     */
    const TEMPLATE_ITEM = self::TEMPLATE_BASE . '/item';

    /**
     * The collection view template path
     */
    const TEMPLATE_COLLECTION = self::TEMPLATE_BASE . '/collection';

    /**
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        $variables = [];
        $this->baseVariables($variables);

        $results = null;

        if (null !== ($object = Craft::$app->getRequest()->getQueryParam('object'))) {
            $class = 'Stripe\\' . ucfirst($object);

            if (class_exists($class)) {
                $criteria = Stripe::criteria($class);

                $conditions = (array) Craft::$app->getRequest()->getQueryParam('criteria');

                $collection = $criteria->all(
                    array_filter($conditions)
                );

                if ($collection instanceof Collection) {
                    /** @var StripeObject $object */
                    foreach($collection->getIterator() as $object) {
                        $results[] = $object->jsonSerialize();
                    }

                    $results = Json::encode($results, JSON_PRETTY_PRINT);
                }
            }
        }

        $variables['results'] = $results;

        return $this->renderTemplate(
            static::TEMPLATE_INDEX,
            $variables
        );
    }

    /**
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionItem(): Response
    {
        $variables = [];
        $this->baseVariables($variables);

        $results = null;

        if (null !== ($criteria = $this->createCriteria())) {

            $criteria->setId((string) Craft::$app->getRequest()->getQueryParam('id'));

            $object = $criteria->retrieve();

            if ($object instanceof ApiResource) {
                $results = $object->jsonSerialize();
                $results = Json::encode($results, JSON_PRETTY_PRINT);
            }
        }

        $variables['results'] = $results;

        return $this->renderTemplate(
            static::TEMPLATE_ITEM,
            $variables
        );
    }

    /**
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCollection(): Response
    {
        $variables = [];
        $this->baseVariables($variables);

        $results = null;

        if (null !== ($criteria = $this->createCriteria())) {
            $conditions = (array) Craft::$app->getRequest()->getQueryParam('criteria');

            $collection = $criteria->all(
                array_filter($conditions)
            );

            if ($collection instanceof Collection) {
                /** @var StripeObject $object */
                foreach($collection->getIterator() as $object) {
                    $results[] = $object->jsonSerialize();
                }

                $results = Json::encode($results, JSON_PRETTY_PRINT);
            }
        }


        $variables['results'] = $results;

        return $this->renderTemplate(
            static::TEMPLATE_COLLECTION,
            $variables
        );
    }

    /**
     * @return \flipbox\craft\stripe\criteria\Criteria|\flipbox\craft\stripe\criteria\ElementCriteria|\flipbox\craft\stripe\criteria\ResourceBehavior|null
     * @throws \yii\base\InvalidConfigException
     */
    private function createCriteria()
    {
        if (null !== ($object = Craft::$app->getRequest()->getQueryParam('object'))) {
            $class = 'Stripe\\' . ucfirst($object);

            if (class_exists($class)) {
                return Stripe::criteria($class);
            }
        }

        return null;
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/api';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/api';
    }
}
