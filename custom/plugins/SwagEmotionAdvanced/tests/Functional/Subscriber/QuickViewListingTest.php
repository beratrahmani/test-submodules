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

namespace SwagEmotionAdvanced\tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Shop\Shop;
use SwagEmotionAdvanced\Services\Dependencies\DependencyProvider;
use SwagEmotionAdvanced\Subscriber\QuickViewListing;

class QuickViewListingTest extends TestCase
{
    public function test_getSubscribedEvents()
    {
        $events = QuickViewListing::getSubscribedEvents();

        $this->assertCount(3, $events);
        $this->assertSame('addQuickView', $events['Enlight_Controller_Action_PreDispatch_Widgets_Listing']);
    }

    public function test_addQuickView_mode_off()
    {
        $subscriber = new QuickViewListing(
            'SwagEmotionAdvanced',
            new ConfigReaderMock(),
            new ShopwareConfigMock(),
            new DependencyProviderMock()
        );

        $args = new \Enlight_Controller_ActionEventArgs();
        $args->set('subject', new FrontendTestControllerMock(new FrontendTestViewMock()));

        $result = $subscriber->addQuickView($args);
        $this->assertNull($result);
        $this->assertEmpty($args->getSubject()->View()->getAssign());
    }

    public function test_addQuickView_mode_only_details_button_no_buy_button()
    {
        $subscriber = new QuickViewListing(
            'SwagEmotionAdvanced',
            new ConfigReaderMock(3),
            new ShopwareConfigMock(false),
            new DependencyProviderMock()
        );

        $args = new \Enlight_Controller_ActionEventArgs();
        $args->set('subject', new FrontendTestControllerMock(new FrontendTestViewMock()));

        $result = $subscriber->addQuickView($args);
        $this->assertNull($result);
        $this->assertEmpty($args->getSubject()->View()->getAssign());
    }

    public function test_addQuickView_mode_only_details_button()
    {
        $subscriber = new QuickViewListing(
            'SwagEmotionAdvanced',
            new ConfigReaderMock(3),
            new ShopwareConfigMock(),
            new DependencyProviderMock()
        );

        $args = new \Enlight_Controller_ActionEventArgs();
        $args->set('subject', new FrontendTestControllerMock(new FrontendTestViewMock()));

        $subscriber->addQuickView($args);
        $result = $args->getSubject()->View()->getAssign();

        $this->assertSame(3, $result['additionalQuickViewMode']);
    }

    public function test_addQuickView_mode_everywhere()
    {
        $subscriber = new QuickViewListing(
            'SwagEmotionAdvanced',
            new ConfigReaderMock(2),
            new ShopwareConfigMock(),
            new DependencyProviderMock()
        );

        $args = new \Enlight_Controller_ActionEventArgs();
        $args->set('subject', new FrontendTestControllerMock(new FrontendTestViewMock()));

        $subscriber->addQuickView($args);
        $result = $args->getSubject()->View()->getAssign();

        $this->assertSame(2, $result['additionalQuickViewMode']);
    }
}

class FrontendTestControllerMock extends \Shopware_Controllers_Frontend_Listing
{
    /**
     * @var FrontendTestViewMock
     */
    public $view;

    /**
     * @param FrontendTestViewMock $view
     */
    public function __construct(FrontendTestViewMock $view)
    {
        $this->view = $view;
    }

    /**
     * @return FrontendTestViewMock
     */
    public function View()
    {
        return $this->view;
    }
}

class FrontendTestViewMock
{
    /**
     * @var array
     */
    public $assign;

    public function __construct()
    {
        $this->assign = [];
    }

    /**
     * @return mixed
     */
    public function getAssign()
    {
        return $this->assign;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function assign($key, $value)
    {
        $this->assign[$key] = $value;
    }
}

class ConfigReaderMock extends CachedConfigReader
{
    /**
     * @var int
     */
    private $additionalQuickViewMode;

    /**
     * @param int $additionalQuickViewMode
     */
    public function __construct($additionalQuickViewMode = 1)
    {
        $this->additionalQuickViewMode = $additionalQuickViewMode;
    }

    /**
     * @param string    $pluginName
     * @param Shop|null $shop
     *
     * @return array
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        return [
            'additionalQuickViewMode' => $this->additionalQuickViewMode,
        ];
    }
}

class ShopwareConfigMock extends \Shopware_Components_Config
{
    /**
     * @param bool $displayListingBuyButton
     */
    public function __construct($displayListingBuyButton = true)
    {
        $this->_data = [
            'displayListingBuyButton' => $displayListingBuyButton,
        ];
    }
}

class DependencyProviderMock extends DependencyProvider {

    public function __construct()
    {
    }

    public function getShop()
    {
        $shop = new Shop();
        $shop->fromArray(['id' => 1]);

        return $shop;
    }

}
