<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */
use SwagPromotion\Components\Rules\Registry\Registry;
use SwagPromotion\Components\Rules\Registry\Type\Callback;
use SwagPromotion\Components\Rules\Registry\Type\Instance;
use SwagPromotion\Components\Rules\Registry\Type\Name;
use SwagPromotion\Components\Rules\Rule;
use SwagPromotion\Components\Rules\Rule\Container\AndRule;

class TestRule implements Rule
{
    public function validate()
    {
        return true;
    }
}

class TestRule2 implements Rule
{
    public function validate()
    {
        return true;
    }
}

class RuleFactory
{
    public static function getTestRule()
    {
        return new TestRule();
    }
}

/**
 * @small
 */
class RuleRegistryTest extends PHPUnit_Framework_TestCase
{
    public function testTypeCallbackClosure()
    {
        $registry = $this->getRuleRegistry();
        $registry->add(
            'test',
            new Callback(
                function () {
                    return new TestRule();
                }
            )
        );
        $this->assertTrue($registry->get('test')->validate());
    }

    public function testTypeCallbackStatic()
    {
        $registry = $this->getRuleRegistry();
        $registry->add('test2', new Callback(['RuleFactory', 'getTestRule']));
        $this->assertTrue($registry->get('test2')->validate());
    }

    public function testTypeCallbackInstance()
    {
        $registry = $this->getRuleRegistry();
        $registry->add('test3', new Callback([new RuleFactory(), 'getTestRule']));
        $this->assertTrue($registry->get('test3')->validate());
    }

    public function testTypeInstance()
    {
        $registry = $this->getRuleRegistry();
        $registry->add('test4', new Instance(new TestRule()));
        $this->assertTrue($registry->get('test4')->validate());
    }

    public function testTypeName()
    {
        $registry = $this->getRuleRegistry();
        $registry->add('test5', new Name('TestRule'));
        $this->assertTrue($registry->get('test5')->validate());

        $name = new Name('TestRule2', true);
        $this->assertTrue($name->isContainer());
        $registry->add('test6', $name);
        $this->assertTrue($registry->get('test6')->validate());
    }

    public function testIsContainer()
    {
        $reg = $this->getRuleRegistry()->add('foo', new AndRule());
        $this->assertTrue($reg->isContainer('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidRule()
    {
        $this->getRuleRegistry()->add('foo', []);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRuleNotFoundInContainerCheck()
    {
        $this->getRuleRegistry()->isContainer('foo');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotExistingRule()
    {
        $this->getRuleRegistry()->get('notExistingRule');
    }

    /**
     * @return Registry
     */
    private function getRuleRegistry()
    {
        return new Registry();
    }
}
