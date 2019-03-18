<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe\cp\controllers\settings\view;

use craft\helpers\UrlHelper;
use flipbox\craft\stripe\connections\SavableConnectionInterface;
use flipbox\craft\stripe\Stripe;
use flipbox\craft\stripe\records\Connection;
use yii\di\Instance;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ConnectionsController extends AbstractController
{
    /**
     * The template base path
     */
    const TEMPLATE_BASE = parent::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'connections';

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = self::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'index';

    /**
     * The index view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['connections'] = Connection::find()->all();

        $variables['types'] = $this->getAvailableConnections();

        return $this->renderTemplate(
            static::TEMPLATE_INDEX,
            $variables
        );
    }

    /**
     * @param null $identifier
     * @param Connection|null $connection
     * @return Response
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($identifier = null, Connection $connection = null): Response
    {
        if (null === $connection) {
            if (null !== $identifier) {
                $connection = Connection::getOne($identifier);
            }
        }

        if (empty($connection)) {
            $connection = new Connection();
        }

        $variables = [];
        if ($connection->getIsNewRecord()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $connection);
        }

        $variables['types'] = $this->getAvailableConnections();
        $variables['connection'] = $connection;
        $variables['fullPageForm'] = true;

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getAvailableConnections()
    {
        $classes = Stripe::getInstance()->getCp()->getAvailableConnections();

        $connections = [];
        foreach ($classes as $connection) {
            if (!$connection instanceof SavableConnectionInterface) {
                $connection = Instance::ensure($connection, SavableConnectionInterface::class);
            }

            $connections[get_class($connection)] = $connection;
        }

        return $connections;
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/connections';
    }

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/connections';
    }

    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Connection $connection
     */
    protected function updateVariables(array &$variables, Connection $connection)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Stripe::t('Edit') . ' ' . $connection->handle;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $connection->getId());
        $variables['crumbs'][] = [
            'label' => $connection->handle,
            'url' => UrlHelper::url($variables['continueEditingUrl'])
        ];
    }
}
