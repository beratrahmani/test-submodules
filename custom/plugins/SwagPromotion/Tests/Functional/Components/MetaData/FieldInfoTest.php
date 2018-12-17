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

namespace SwagPromotion\Tests\Functional;

use Shopware\Components\Test\Plugin\TestCase;
use Shopware\Models\Article\Article;
use SwagPromotion\Components\MetaData\FieldInfo;

/**
 * @small
 */
class FieldInfoTest extends TestCase
{
    /**
     * @var array
     */
    protected static $ensureLoadedPlugins = [
        'SwagPromotion' => [],
    ];

    public function testMetaData()
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $result */
        $result = $this->callMethod(new FieldInfo(), 'getTypesForModel', [Article::class]);

        $this->assertEquals('integer', $result['id']);
    }

    public function testProductData()
    {
        $info = new FieldInfo();
        $result = $info->get();
    }

    public function testFieldInfo()
    {
        $info = new FieldInfo();
        $result = $info->get();

        $this->assertEquals('integer', $result['customer']['user::id']);
        $this->assertEquals('string', $result['customer']['address::additional_address_line1']);
    }

    /**
     * @param $obj
     * @param $name
     * @param array $args
     *
     * @return mixed
     */
    protected function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
