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

namespace SwagPromotion\Tests\IntegrationTests;

use Enlight_Components_Session_Namespace;
use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Subscriber\PromotionSubscriber;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotion\Tests\PromotionTestCase;

/**
 * @medium
 * @group integration
 */
class AbsoluteDiscountTest extends PromotionTestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
    }

    public function testAbsoluteDiscount()
    {
        $this->dispatch('');

        Shopware()->Front()->setRequest($this->Request());

        $sql = file_get_contents(__DIR__ . '/Fixtures/absoluteDiscount.sql');
        $this->container->get('dbal_connection')->exec($sql);

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->container->get('session');
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sessionId', 'sessionId');
        $session->offsetSet('sUserPassword', 'a256a310bc1e5db755fd392c524028a8');
        $session->offsetSet('sUserMail', 'test@example.com');

        $arguments = new AbsoluteDiscountTestArgumentsMock($this, []);
        $arguments->setReturn(Shopware()->Modules()->Basket()->sGetBasket());

        $subscriber = new PromotionSubscriber(
            $this->container->get('dbal_connection'),
            $this->container->get('template'),
            $this->container->get('config'),
            $this->container->get('session'),
            $this->container->get('shopware_storefront.context_service'),
            $this->container->get('swag_promotion.service.article_service'),
            $this->container->get('swag_promotion.promotion_selector')
        );
        $subscriber->afterGetBasket($arguments);

        $basket = $arguments->getReturn();

        $this->assertTrue(
            abs($basket['AmountNumeric'] - (19.95 - 5)) <= 0.01,
            "Unexpected basket amount: {$basket['AmountNumeric']}"
        );
    }

    public function testShouldNotApply()
    {
        Shopware()->Front()->setRequest($this->Request());
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>', 20]]],
                        'amount' => 3,
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        // 5.95 is the default price + 5 surcharge for small bassket
        // Promotion should not apply, as the basket amount < 20
        $this->assertTrue(
            abs($basket['AmountNumeric'] - (10.95)) <= 0.01,
            "Unexpected basket amount: {$basket['AmountNumeric']}"
        );
    }
}

class AbsoluteDiscountTestArgumentsMock extends \Enlight_Hook_HookArgs
{
    /**
     * @var mixed
     */
    public $returnData;

    /**
     * @param mixed $data
     */
    public function setReturn($data)
    {
        $this->returnData = $data;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->returnData;
    }
}
