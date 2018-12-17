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

namespace SwagAdvancedCart\Services;

use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Components\Random;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Basket;
use Shopware\Models\Shop\Shop;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Models\Cart\CartItem;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;
use SwagAdvancedCart\Services\Dependencies\PluginDependenciesInterface;

/**
 * Handles all important operations to be done from the frontend controller
 */
class CartHandler implements CartHandlerInterface
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var WishlistAuthServiceInterface
     */
    private $authService;

    /**
     * @var ListProductGatewayInterface
     */
    private $listProductGateway;

    /**
     * @var CachedConfigReader
     */
    private $configReader;

    /**
     * @var PluginDependenciesInterface
     */
    private $pluginDependencies;

    /**
     * @param string                       $pluginName
     * @param DependencyProviderInterface  $dependencyProvider
     * @param PluginDependenciesInterface  $pluginDependencies
     * @param ModelManager                 $modelManager
     * @param ContextServiceInterface      $contextService
     * @param WishlistAuthServiceInterface $authService
     * @param ListProductGatewayInterface  $listProductGateway
     * @param CachedConfigReader           $configReader
     */
    public function __construct(
        $pluginName,
        DependencyProviderInterface $dependencyProvider,
        PluginDependenciesInterface $pluginDependencies,
        ModelManager $modelManager,
        ContextServiceInterface $contextService,
        WishlistAuthServiceInterface $authService,
        ListProductGatewayInterface $listProductGateway,
        CachedConfigReader $configReader
    ) {
        $this->pluginName = $pluginName;
        $this->dependencyProvider = $dependencyProvider;
        $this->pluginDependencies = $pluginDependencies;
        $this->modelManager = $modelManager;
        $this->contextService = $contextService;
        $this->authService = $authService;
        $this->listProductGateway = $listProductGateway;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function saveCart($name, $published)
    {
        $session = $this->dependencyProvider->getSession()->get('sessionId');
        $customerId = $this->dependencyProvider->getSession()->get('sUserId');

        $shop = $this->modelManager->find(Shop::class, $this->dependencyProvider->getShop()->getId());
        $published = $published ? 1 : 0;

        if (!$customerId) {
            return [];
        }

        try {
            /** @var Customer $customer */
            $customer = $this->modelManager->find(Customer::class, $customerId);
            if (!$customer) {
                throw new \RuntimeException("Could not find customer for ID {$customerId}");
            }

            $this->checkIfBasketNameExists($name, $customerId);
        } catch (\Exception $ex) {
            return [
                'success' => false,
            ];
        }

        $cart = $this->createCart($name, $customer, $published, $shop);
        $basketItems = $this->getItemsFromSessionBasket($session);

        return $this->prepareAndSaveCart($basketItems, $cart);
    }

    /**
     * {@inheritdoc}
     */
    public function addToList(array $postData)
    {
        $orderNumber = $postData['ordernumber'];
        $quantity = $postData['quantity'] ?: 1;
        /** @var array $lists */
        $lists = $postData['lists'];
        $newList = $postData['newlist'];
        $customerId = $this->dependencyProvider->getSession()->get('sUserId');

        if (!$customerId) {
            return [];
        }

        if (!$orderNumber) {
            throw new \RuntimeException('Order-number is missing');
        }

        if (!$lists && !$newList) {
            return ['success' => true];
        }

        // Create new list
        if ($newList) {
            $wishListId = $this->createWishList($newList);
            // Add new list for interaction
            $lists[] = $wishListId;
        }

        /** Shopware\Models\Customer\Customer $customer */
        $customer = $this->modelManager->find(Customer::class, $customerId);
        if (!$customer) {
            throw new \RuntimeException("Could not find customer for ID {$customerId}");
        }

        $detail = $this->modelManager->getRepository(Detail::class)
            ->findOneBy(['number' => $orderNumber]);
        if (!$detail) {
            throw new \RuntimeException("Could not find product detail with number $orderNumber");
        }

        // Check if user basket exists and have permissions
        $repo = $this->modelManager->getRepository(Cart::class);
        foreach ($lists as $listId) {
            $existingList = $repo->findOneBy(
                [
                    'id' => $listId,
                    'customer' => $customerId,
                    'shopId' => $this->dependencyProvider->getShop()->getId(),
                ]
            );

            if (!$existingList) {
                throw new \RuntimeException("List with cartId {$listId} doesn't exists");
            }

            if (!$this->authService->authenticateById($listId)) {
                throw new \RuntimeException('User not authorized');
            }

            // Check if product exists already
            $itemRepo = $this->modelManager->getRepository(CartItem::class);
            /** @var CartItem $cartItem */
            $cartItem = $itemRepo->findOneBy(['productOrderNumber' => $orderNumber, 'cart' => $listId]);
            // Article exists in listId already, increase quantity
            if ($cartItem) {
                $newQuantity = $cartItem->getQuantity() + $quantity;
                $cartItem->setQuantity($newQuantity);

                $this->modelManager->persist($cartItem);

                continue;
            }

            $cartItem = new CartItem();
            $cartItem->setDetail($detail);
            $cartItem->setQuantity($quantity);
            $cartItem->setCart($existingList);
            $this->modelManager->persist($cartItem);
        }

        $this->modelManager->flush();

        return [
            'success' => true,
            'lists' => $lists,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createWishList($name)
    {
        // Check if user is logged in
        $customerId = $this->dependencyProvider->getSession()->get('sUserId');
        if (!$customerId) {
            return 0;
        }

        /** Shopware\Models\Customer\Customer $customer */
        $customer = $this->modelManager->find(Customer::class, $customerId);
        if (!$customer) {
            throw new \RuntimeException("Could not find customer for ID {$customerId}");
        }

        $wishList = $this->modelManager->getRepository(Cart::class)
            ->findOneBy([
                'customer' => $customer,
                'name' => $name,
                'shopId' => $this->dependencyProvider->getShop()->getId(),
            ]);

        if ($wishList) {
            throw new \RuntimeException('Wish-List with that name already exists');
        }

        // Save all stored items into new wish-list
        $cart = new Cart();
        $cart->setShop($this->modelManager->find(Shop::class, $this->dependencyProvider->getShop()->getId()));
        $cart->setName($name);
        $cart->setCustomer($customer);
        $cart->setExpire($this->getExpiryDate());
        $cart->setHash($this->generateHash());
        $cart->setPublished(0);
        $cart->setModified(date('Y-m-d H:i:s'));

        $this->modelManager->persist($cart);
        $this->modelManager->flush();

        return $cart->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function prepareCartForModal(array $carts)
    {
        $newCarts = [];
        foreach ($carts as $cartData) {
            $cartData['hash'] = $cartData['cookie_value'];
            $cartData['shopId'] = $cartData['shop_id'];
            $orderNumbers = explode(',', $cartData['orderNumbers']);

            unset(
                $cartData['cookie_value'],
                $cartData['orderNumbers'],
                $cartData['shop_id'],
                $cartData['user_id']
            );

            $cartData['cartItems'] = $this->getCartItemsByOrderNumbers($orderNumbers, $cartData['id']);
            $newCarts[] = $cartData;
        }

        return $newCarts;
    }

    /**
     * {@inheritdoc}
     */
    public function getCartItemsByOrderNumbers(array $orderNumbers, $basketId = null)
    {
        /** @var ShopContext $context */
        $context = $this->contextService->getShopContext();
        $result = $this->listProductGateway->getList($orderNumbers, $context);

        $cartItems = [];
        /** @var ListProduct $product */
        foreach ($result as $product) {
            $newItem['id'] = $product->getId();
            $newItem['articleOrderNumber'] = $product->getNumber();

            if ($basketId) {
                $newItem['basket_id'] = $basketId;
            }
            $cartItems[] = $newItem;
        }

        return $cartItems;
    }

    /**
     * {@inheritdoc}
     */
    public function checkIfBasketNameExists($name, $userID)
    {
        /** @var ModelRepository $repo */
        $repo = $this->modelManager->getRepository(Cart::class);
        $existingBasket = $repo->findBy(
            [
                'name' => $name,
                'customer' => $userID,
                'shopId' => $this->dependencyProvider->getShop()->getId(),
            ]
        );

        if ($existingBasket) {
            throw new \RuntimeException("User has already a saved basket with the name {$name}");
        }
    }

    /**
     * Generate the wish-list token
     *
     * @return string
     */
    private function generateHash()
    {
        return Random::getAlphanumericString(50);
    }

    /**
     * Helper method to calculate the expiry-date
     *
     * @return string
     */
    private function getExpiryDate()
    {
        $config = $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop());
        $expiryDateInDays = $config['expireDateInDays'];

        if (!$expiryDateInDays) {
            // Set expiry date to one year, if config could not be loaded.
            $expiryDateInDays = 365;
        }

        return date('Y-m-d', strtotime('+' . $expiryDateInDays . ' days'));
    }

    /**
     * @param Detail $detail
     * @param Basket $basketItem
     * @param Cart   $cart
     *
     * @return CartItem
     */
    private function createCartItem(Detail $detail, Basket $basketItem, Cart $cart)
    {
        $cartItem = new CartItem();
        $cartItem->setDetail($detail);
        $cartItem->setQuantity($basketItem->getQuantity());
        $cartItem->setCart($cart);

        return $cartItem;
    }

    /**
     * @param array $basketItems
     * @param Cart  $cart
     *
     * @return array
     */
    private function prepareAndSaveCart(array $basketItems, Cart $cart)
    {
        //Indicates if a regular, none customized product has been placed to the wish-list
        $hasRegularItem = false;
        $hasCustomItem = false;
        $basketItemIds = [];

        /** @var Basket $basketItem */
        foreach ($basketItems as $basketItem) {
            $detail = $this->modelManager->getRepository(Detail::class)
                ->findOneBy(['number' => $basketItem->getOrderNumber()]);

            if (!$detail) {
                //Unknown detail error so don't add it to the wish-list
                continue;
            }

            $basketItemIds[] = $basketItem->getId();
            $hasRegularItem = true;

            $cartItem = $this->createCartItem($detail, $basketItem, $cart);
            $this->modelManager->persist($cartItem);
        }

        //Only save the wish-list if a regular product has been added to prevent empty wish-lists.
        if ($hasRegularItem) {
            $this->modelManager->persist($cart);
            $this->modelManager->flush();
        }

        return [
            'success' => true,
            'basketId' => $cart->getId(),
            'hash' => $cart->getHash(),
            'customizedItem' => $hasCustomItem,
            'regularItem' => $hasRegularItem,
            'requireBundleMessage' => $this->requireBundleMessage($basketItemIds),
        ];
    }

    /**
     * @param string $sessionId
     *
     * @return array
     */
    private function getItemsFromSessionBasket($sessionId)
    {
        // Get all stored items from session Basket
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select('basket')
            ->from(Basket::class, 'basket')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber != :empty')
            ->andWhere('basket.articleId != :empty')
            ->setParameter('empty', '')
            ->setParameter('sessionId', $sessionId);

        return $builder->getQuery()->getResult();
    }

    /**
     * Checks if SwagBundle is installed and if any bundles in the basket
     *
     * @param array $basketItemIds
     *
     * @return bool
     */
    private function requireBundleMessage(array $basketItemIds)
    {
        $requireBundleMessage = false;

        if ($this->pluginDependencies->isPluginInstalled('SwagBundle')
            && $this->pluginDependencies->isBundleArticleInBasket($basketItemIds)
        ) {
            $requireBundleMessage = true;
        }

        return $requireBundleMessage;
    }

    /**
     * @param string   $name
     * @param Customer $customer
     * @param bool     $published
     * @param Shop     $shop
     *
     * @return Cart
     */
    private function createCart($name, Customer $customer, $published, Shop $shop)
    {
        // Save all stored items into database Basket
        $cart = new Cart();
        $cart->setShop($shop);
        $cart->setName($name);
        $cart->setCustomer($customer);
        $cart->setExpire($this->getExpiryDate());
        $cart->setPublished($published);
        $cart->setModified(date('Y-m-d H:i:s'));
        $cart->setHash($this->generateHash());

        return $cart;
    }
}
