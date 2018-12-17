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

namespace SwagPromotion\Tests\Rules;

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\Rules\StreamRule;

/**
 * @small
 */
class StreamRuleTest extends TestCase
{
    public static function tearDownAfterClass()
    {
        self::cleanup();
        parent::tearDownAfterClass();
    }

    public static function createStream()
    {
        self::cleanup();
        $sql = <<<EOF
INSERT INTO `s_product_streams` (`name`, `conditions`, `type`, `sorting`, `description`) VALUES
('UnitTestStream',	NULL,	2,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\PopularitySorting\":{\"direction\":\"desc\"}}',	'UnitTestStream');
EOF;
        Shopware()->Db()->exec($sql);
        $id = Shopware()->Db()->lastInsertId();

        $sql = <<<EOF
INSERT INTO `s_product_streams_selection` (`stream_id`, `article_id`) VALUES
($id, 4),
($id, 6),
($id, 8);
EOF;
        Shopware()->Db()->exec($sql);

        return $id;
    }

    public function testProductStreamRule()
    {
        $id = $this->createStream();

        $rule1 = new StreamRule(
            [['ordernumber' => 'SW10006']],
            [$id]
        );

        $rule2 = new StreamRule(
            [['ordernumber' => 'non-existing-number']],
            [$id]
        );

        $this->assertTrue($rule1->validate());
        $this->assertFalse($rule2->validate());
    }

    private static function cleanup()
    {
        $sql = <<<EOF
            DELETE s, a
            FROM s_product_streams s
            LEFT JOIN s_product_streams_selection a
                ON a.stream_id = s.id
            WHERE s.name = "UnitTestStream"
EOF;
        Shopware()->Db()->exec($sql);
    }
}
