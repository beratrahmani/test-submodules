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

namespace SwagLiveShopping\Subscriber;

use Doctrine\ORM\AbstractQuery;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Attribute\OrderBasket;
use Shopware\Models\Order\Basket;
use SwagLiveShopping\Components\LiveShoppingBasketInterface;
use SwagLiveShopping\Components\LiveShoppingInterface;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;

class FrontendCheckoutSubscriber implements SubscriberInterface
{
    /**
     * @var LiveShoppingBasketInterface
     */
    private $liveShoppingBasket;

    /**
     * @var LiveShoppingInterface
     */
    private $liveShopping;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param LiveShoppingBasketInterface $liveShoppingBasket
     * @param LiveShoppingInterface       $liveShopping
     * @param ModelManager                $modelManager
     */
    public function __construct(
        LiveShoppingBasketInterface $liveShoppingBasket,
        LiveShoppingInterface $liveShopping,
        ModelManager $modelManager
    ) {
        $this->liveShoppingBasket = $liveShoppingBasket;
        $this->liveShopping = $liveShopping;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onFrontendCheckoutPreDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onFrontendCheckoutPostDispatch',
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after' => 'onSaveOrder',
        ];
    }

    /**
     * Enlight event listener function.
     * Fired when the customer enters the checkout section.
     *
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onFrontendCheckoutPreDispatch(\Enlight_Event_EventArgs $arguments)
    {
        /* @var \Enlight_Controller_Action $subject  */
        $subject = $arguments->get('subject');

        /* @var \Enlight_Controller_Request_RequestHttp  $request */
        $request = $subject->Request();

        /* @var \Enlight_View_Default $view  */
        $view = $subject->View();

        $liveShoppings = $this->liveShopping->getBasketLiveShoppingProducts();

        if (empty($liveShoppings)) {
            return;
        }

        $validations = [];

        /* @var LiveShoppingModel $liveShopping  */
        foreach ($liveShoppings as $basketId => $liveShopping) {
            $validation = $this->liveShopping->validateLiveShopping($liveShopping);

            $timeValidation = $this->liveShopping->isLiveShoppingDateActive($liveShopping);

            $basketItem = $this->liveShoppingBasket->getItem(
                $basketId,
                AbstractQuery::HYDRATE_OBJECT
            );

            $quantityValidation = $this->checkLiveShoppingBasketQuantity($liveShopping, $basketItem);

            if ($validation === true && $timeValidation === true && $quantityValidation === true) {
                continue;
            }

            if ($validation !== true) {
                $validation['basketId'] = $basketId;
                $validations[] = $validation;
            } elseif ($timeValidation === false) {
                $validations[] = [
                    'outOfDate' => true,
                    'basketId' => $basketId,
                    'article' => $this->liveShopping->getLiveShoppingProductName($liveShopping),
                ];
            } elseif ($quantityValidation === false) {
                $validations[] = [
                    'stockOverFlow' => true,
                    'basketId' => $basketId,
                    'article' => $this->liveShopping->getLiveShoppingProductName($liveShopping),
                ];
            }
        }

        if (empty($validations)) {
            return;
        }

        if ($request->getActionName() === 'finish') {
            $subject->forward('confirm', 'checkout', 'frontend', ['sLiveShoppingValidation' => $validations]);
        } else {
            $view->assign('sLiveShoppingValidation', $validations);
        }
    }

    /**
     * Enlight event listener function of Shopware_Controllers_Frontend_Checkout::postDispatch function.
     *
     * Used to extends the checkout tempalte to add the live shopping flag in the basket rows.
     *
     * @param \Enlight_Event_EventArgs $arguments
     */
    public function onFrontendCheckoutPostDispatch(\Enlight_Event_EventArgs $arguments)
    {
        /* @var \Enlight_Controller_Action $subject  */
        $subject = $arguments->get('subject');

        /* @var \Enlight_View_Default $view  */
        $view = $subject->View();

        $basket = $view->getAssign('sBasket');

        if (!empty($basket['content'])) {
            foreach ($basket['content'] as &$item) {
                if (empty($item['id'])) {
                    continue;
                }
                $model = $this->liveShoppingBasket->getItem(
                    $item['id'],
                    AbstractQuery::HYDRATE_OBJECT
                );

                /** @var Basket $model */
                if ($model instanceof Basket
                    && ($model->getAttribute() instanceof OrderBasket)
                ) {
                    $item['swagLiveShoppingId'] = $model->getAttribute()->getSwagLiveShoppingId();
                }
            }
            unset($item);
        }

        $view->assign('sBasket', $basket);
    }

    /**
     * Enlight hook for the Shopware_Controllers_Frontend_Checkout saveOrder function.
     * The hook fired after the saveOrder function passed.
     * The saveOrder function returns the new order number
     *
     * @param \Enlight_Hook_HookArgs $arguments
     *
     * @throws \Exception
     */
    public function onSaveOrder(\Enlight_Hook_HookArgs $arguments)
    {
        $orderNumber = $arguments->getReturn();

        if ($orderNumber === '') {
            return;
        }

        $connection = $this->modelManager->getConnection();

        $sql = <<<SQL
            SELECT s_order_details.articleordernumber, s_articles_details.articleID, s_articles_details.id AS variantId, s_order_details.quantity
            FROM s_order_details
                INNER JOIN s_articles_details
                    ON s_order_details.articleordernumber = s_articles_details.ordernumber
            WHERE s_order_details.ordernumber = :ordernumber
SQL;
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('ordernumber', $orderNumber);
        $stmt->execute();

        $products = $stmt->fetchAll();

        foreach ($products as $productData) {
            if (!empty($productData['articleID'])) {
                $liveShopping = $this->liveShopping->getActiveLiveShoppingForProduct(
                    $productData['articleID']
                );

                if (!$liveShopping instanceof LiveShoppingModel) {
                    continue;
                }
                if (!empty($productData['variantId'])) {
                    $variant = $this->modelManager->find(Detail::class, $productData['variantId']);
                    if ($variant instanceof Detail
                        && !$this->liveShopping->isVariantAllowed($liveShopping, $variant)
                    ) {
                        continue;
                    }
                }

                $quantity = 1;
                if ($productData['quantity']) {
                    $quantity = $productData['quantity'];
                }
                try {
                    $this->liveShopping->decreaseLiveShoppingStock($liveShopping, $quantity);
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @param LiveShoppingModel $liveShopping
     * @param Basket            $basketItem
     *
     * @return bool
     */
    private function checkLiveShoppingBasketQuantity(LiveShoppingModel $liveShopping, Basket $basketItem)
    {
        if (!$basketItem instanceof Basket) {
            return true;
        }

        if (!$liveShopping instanceof LiveShoppingModel) {
            return true;
        }

        if (!$liveShopping->getLimited()) {
            return true;
        }

        $variant = $this->modelManager
            ->getRepository(Detail::class)
            ->findOneBy(['number' => $basketItem->getOrderNumber()]);

        if (!$variant instanceof Detail) {
            return true;
        }

        $basketQuantity = $this->liveShoppingBasket->getSummarizedQuantityOfVariant($variant, 0);

        return !($liveShopping->getQuantity() < $basketQuantity);
    }
}
