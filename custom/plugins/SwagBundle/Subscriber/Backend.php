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

namespace SwagBundle\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use SwagBundle\Models\Article as BundleProduct;

class Backend implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ModelManager $modelManager
     * @param Connection   $connection
     */
    public function __construct(ModelManager $modelManager, Connection $connection)
    {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchBackend',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Article' => 'loadBackendModule',
            'Shopware\Models\Article\Detail::preRemove' => 'preRemoveProductVariant',
        ];
    }

    /**
     * Only needed to load the backend-template, which includes the new bundle-icon
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchBackend(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate('backend/bundle_menu_item.tpl');
    }

    /**
     * The loadBackendModule function is an event listener function which is responsible to
     * load the bundle backend module extension for the product module.
     *
     * @param \Enlight_Controller_ActionEventArgs $arguments
     */
    public function loadBackendModule(\Enlight_Controller_ActionEventArgs $arguments)
    {
        //if the controller action name equals "load" we have to load all application components.
        if ($arguments->getRequest()->getActionName() === 'load') {
            $arguments->getSubject()->View()->extendsTemplate('backend/article/view/detail/bundle_window.js');
        }

        //if the controller action name equals "index" we have to extend the backend product application
        if ($arguments->getRequest()->getActionName() === 'index') {
            $arguments->getSubject()->View()->extendsTemplate('backend/article/bundle_app.js');
        }
    }

    /**
     * Event listener on pre-remove lifecycle event of Shopware\Models\Article\Detail
     *
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function preRemoveProductVariant(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $variantModel */
        $variantModel = $arguments->get('entity');

        $productVariantId = $variantModel->getId();
        $productId = $variantModel->getArticle()->getId();

        $sql = "UPDATE `s_articles_bundles`
            SET active = '0'
            WHERE `articleID`= ? OR id IN (
              SELECT bundle_id
              FROM `s_articles_bundles_articles`
              WHERE `article_detail_id`= ?
            )";

        $this->connection->executeUpdate($sql, [$productId, $productVariantId]);

        // Remove product from bundles
        $builder = $this->modelManager->createQueryBuilder();
        $builder->delete(BundleProduct::class, 'products')
            ->where('products.articleDetailId = :productVariantId')
            ->setParameters(['productVariantId' => $productVariantId])
            ->getQuery()
            ->execute();
    }
}
