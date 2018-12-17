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
use Shopware\Models\Article\Detail;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Models\Cart\CartItem;

/**
 * Class Shopware_Controllers_Backend_SwagAdvancedCart
 */
class Shopware_Controllers_Backend_SwagAdvancedCart extends Shopware_Controllers_Backend_Application
{
    protected $model = Cart::class;
    protected $alias = 'Cart';

    /**
     * {@inheritdoc}
     */
    public function getListQuery()
    {
        $builder = parent::getListQuery();

        $builder->addSelect('customer', 'billing', 'items', 'shop')
            ->leftJoin('Cart.customer', 'customer')
            ->leftJoin('customer.defaultBillingAddress', 'billing')
            ->leftJoin('Cart.cartItems', 'items')
            ->leftJoin('Cart.shop', 'shop');

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $data = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        foreach ($data['data'] as &$cart) {
            $cart = $this->prepareCartValues($cart);
        }

        return $data;
    }

    /**
     * gets product data for the detail window
     */
    public function articlesAction()
    {
        $cartId = $this->Request()->getParam('id', 0);

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['items', 'prices.price as price'])
            ->from(CartItem::class, 'items')
            ->where('items.basket_id = :cartId')
            ->innerJoin('items.details', 'detail')
            ->innerJoin('detail.prices', 'prices', 'WITH', 'prices.customerGroupKey = :customerGroupKey AND prices.from=1', 'prices.id')
            ->setParameter('customerGroupKey', 'EK')
            ->setParameter('cartId', $cartId);

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        $products = [];

        foreach ($data as $item) {
            // Article Information
            $productRepository = $this->getModelManager()->getRepository(Detail::class);
            /** @var Detail $product */
            $product = $productRepository->findOneBy(['number' => $item[0]['productOrderNumber']]);

            // Article Name
            $products[] = [
                'id' => $item[0]['id'],
                'articleOrderNumber' => $product->getNumber(),
                'articleId' => $product->getArticle()->getId(),
                'name' => $product->getArticle()->getName(),
                'quantity' => $item[0]['quantity'],
                'price' => $item['price'],
                'sumPrice' => $item[0]['quantity'] * $item['price'],
            ];
        }

        $this->View()->assign(['success' => true, 'data' => $products, 'total' => $count]);
    }

    /**
     * deletes one cart item
     */
    public function deleteItemsAction()
    {
        $cartItemId = $this->Request()->getParam('id', 0);

        $repo = $this->getModelManager()->getRepository(CartItem::class);
        $cartItem = $repo->find($cartItemId);

        $this->getModelManager()->remove($cartItem);
        $this->getModelManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Helper method to prepare the values of a single cart.
     * E.g. dye unknown values and set the single cart-positions.
     *
     * @param array $cart
     *
     * @return array
     */
    private function prepareCartValues(array &$cart)
    {
        /** @var \Shopware_Components_Snippet_Manager $snippetManager */
        $snippetManager = $this->get('snippets');

        // Style live carts
        if (empty($cart['name'])) {
            $cart['isSessionCart'] = true;
            $cart['name'] = $snippetManager->getNamespace('backend/swag_advanced_cart/view/main')->get(
                'sessionWishlist',
                'Session Wishlist'
            );
        }

        // Get Customer Information
        $customer = $cart['customer'];
        $billingAddress = $customer['defaultBillingAddress'];
        $fullName = $billingAddress['firstname'] . ' ' . $billingAddress['lastname'];

        if (empty($billingAddress['firstname']) && empty($billingAddress['lastname'])) {
            $fullName = $snippetManager->getNamespace('backend/swag_advanced_cart/view/main')
                ->get('unknown', 'Unknown');
        }

        $cart['customer'] = $fullName;

        // Get Customer Id
        $cart['customerId'] = $customer['id'];

        // Get Article data
        $countItems = count($cart['cartItems']);
        $cart['cartItems'] = str_replace(
            '__COUNT__',
            $countItems,
            $snippetManager->getNamespace('backend/swag_advanced_cart/view/main')->get(
                'articleCount',
                '__COUNT__ item(s)'
            )
        );

        return $cart;
    }
}
