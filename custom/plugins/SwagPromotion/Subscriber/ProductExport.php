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

namespace SwagPromotion\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use sExport;
use Shopware\Components\Plugin\ConfigReader;
use SwagPromotion\Components\ProductExport\PromotionExportInterface;

class ProductExport implements SubscriberInterface
{
    /**
     * @var PromotionExportInterface
     */
    private $promotionExporter;

    /**
     * @var ConfigReader
     */
    private $pluginConfigReader;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @param PromotionExportInterface              $promotionExporter
     * @param ConfigReader                          $configReader
     * @param \Enlight_Components_Session_Namespace $session
     * @param string                                $pluginName
     */
    public function __construct(
        PromotionExportInterface $promotionExporter,
        ConfigReader $configReader,
        \Enlight_Components_Session_Namespace $session,
        $pluginName
    ) {
        $this->promotionExporter = $promotionExporter;
        $this->pluginConfigReader = $configReader;
        $this->pluginName = $pluginName;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Export_ExportResult_Filter_Fixed' => 'onFilterProductExport',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onFilterProductExport(Enlight_Event_EventArgs $args)
    {
        $extendExport = (bool) $this->pluginConfigReader->getByPluginName($this->pluginName)['promotionPricesInProductExport'];
        if (!$extendExport) {
            return;
        }

        /** @var sExport $sExport */
        $sExport = $args->get('subject');

        /** @var array $settings */
        $settings = $sExport->sSettings;
        $customerGroup = $sExport->sCustomergroup;

        if ($customerGroup !== null) {
            //Required, so that the ListProductService can determine the current user's customer group and not just the
            //shop's default customer group.
            $this->session->offsetSet('sUserGroup', $customerGroup['groupkey']);
        }

        /** @var array $exportProducts */
        $exportProducts = $args->getReturn();
        $exportProducts = $this->promotionExporter->handleExport($exportProducts, $settings);

        $args->setReturn($exportProducts);
    }
}
