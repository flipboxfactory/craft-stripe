<?php

namespace flipbox\craft\stripe\tests;

use Codeception\Test\Unit;
use flipbox\craft\stripe\Stripe as StripePlugin;
use flipbox\craft\stripe\services\Cache;
use flipbox\craft\stripe\services\Connections;
use flipbox\craft\psr3\Logger;

class StripeTest extends Unit
{
    /**
     * @var StripePlugin
     */
    private $module;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new StripePlugin('stripe');
    }

    /**
     * Test the component is set correctly
     */
    public function testCacheComponent()
    {
        $this->assertInstanceOf(
            Cache::class,
            $this->module->getCache()
        );

        $this->assertInstanceOf(
            Cache::class,
            $this->module->cache
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testConnectionsComponent()
    {
        $this->assertInstanceOf(
            Connections::class,
            $this->module->getConnections()
        );

        $this->assertInstanceOf(
            Connections::class,
            $this->module->connections
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testPSR3Component()
    {
        $this->assertInstanceOf(
            Logger::class,
            $this->module->getPsrLogger()
        );

        $this->assertInstanceOf(
            Logger::class,
            $this->module->psr3Logger
        );
    }
}
