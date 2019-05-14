<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/force/license
 * @link       https://www.flipboxfactory.com/software/force/
 */

namespace flipbox\craft\stripe;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use flipbox\craft\ember\helpers\UrlHelper;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\craft\psr3\Logger;
use flipbox\craft\stripe\criteria\Criteria as BaseCriteria;
use flipbox\craft\stripe\criteria\Criteria;
use flipbox\craft\stripe\criteria\ResourceBehavior;
use flipbox\craft\stripe\fields\Customers as ObjectsField;
use flipbox\craft\stripe\models\Settings as SettingsModel;
use flipbox\craft\stripe\records\ObjectAssociation;
use flipbox\craft\stripe\web\twig\variables\Stripe as StripeVariable;
use Stripe\Stripe as StripeSDK;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 *
 * @property services\Cache $cache
 * @property services\Connections $connections
 * @property Logger $psr3Logger
 */
class Stripe extends Plugin
{
    use LoggerTrait;

    /**
     * @var string
     */
    public static $category = 'stripe';

    /**
     * @inheritdocfind
     */
    public function init()
    {
        parent::init();

        // Components
        $this->setComponents([
            'cache' => services\Cache::class,
            'connections' => services\Connections::class,
            'psr3Logger' => function () {
                return Craft::createObject([
                    'class' => Logger::class,
                    'logger' => Craft::getLogger(),
                    'category' => self::$category
                ]);
            }
        ]);

        // Modules
        $this->setModules([
            'cp' => cp\Cp::class
        ]);

        // Fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ObjectsField::class;
            }
        );

        // Template variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('stripe', StripeVariable::class);
            }
        );

        // Integration template directory
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['flipbox/integration'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-integration/src/templates';
            }
        );

        // CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );

        // Make sure we have a table
        ObjectAssociation::ensureEnvironmentTableExists();

        /*******************************************
         * BOOTSTRAP SDK
         *******************************************/

        // Logger to Stripe SDK
        StripeSDK::setLogger(static::getPsrLogger());

        // Set App Info to Stripe SDK
        StripeSDK::setAppInfo(
            $this->name ?: $this->getUniqueId(),
            $this->getVersion()
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        return array_merge(
            parent::getCpNavItem(),
            [
                'subnav' => [
                    'stripe.settings' => [
                        'label' => static::t('Settings'),
                        'url' => 'stripe/settings',
                    ]
                ]
            ]
        );
    }

    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @inheritdoc
     * @return SettingsModel
     */
    public function createSettingsModel()
    {
        return new SettingsModel();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\ExitException
     */
    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('stripe/settings')
        );

        Craft::$app->end();
    }


    /*******************************************
     * CRITERIA
     *******************************************/

    /**
     * @param string $resource
     * @return Criteria|ResourceBehavior
     * @throws \yii\base\InvalidConfigException
     */
    public static function criteria(string $resource): Criteria
    {
        return Craft::createObject([
            'class' => Criteria::class,
            'as behaviorName' => [
                'class' => ResourceBehavior::class,
                'resource' => $resource
            ]
        ]);
    }


    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Cache
     */
    public function getCache(): services\Cache
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('cache');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Connections
     */
    public function getConnections(): services\Connections
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('connections');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Logger
     */
    public function getPsrLogger(): Logger
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('psr3Logger');
    }


    /*******************************************
     * MODULES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return cp\Cp
     */
    public function getCp(): cp\Cp
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getModule('cp');
    }


    /*******************************************
     * TRANSLATE
     *******************************************/

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\Craft::t()]].
     *     *
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($message, $params = [], $language = null)
    {
        return Craft::t('stripe', $message, $params, $language);
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $event->rules = array_merge(
            $event->rules,
            [
                // ??
                'stripe' => 'stripe/cp/settings/view/general/index',

                // SETTINGS
                'stripe/settings' => 'stripe/cp/settings/view/general/index',
                'stripe/settings/limits' => 'stripe/cp/settings/view/limits/index',

                // SETTINGS: CONNECTIONS
                'stripe/settings/connections' => 'stripe/cp/settings/view/connections/index',
                'stripe/settings/connections/new' => 'stripe/cp/settings/view/connections/upsert',
                'stripe/settings/connections/<identifier:\d+>' => 'stripe/cp/settings/view/connections/upsert',
            ]
        );
    }
}
