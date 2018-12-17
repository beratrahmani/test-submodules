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
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Theme\Inheritance;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository;
use Shopware\Models\Order\Basket;
use Shopware\Models\Partner\Partner;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Models\Cart\CartItem;
use SwagAdvancedCart\Models\Cart\SharedCart;
use SwagAdvancedCart\Services\CartHandler;
use SwagAdvancedCart\Services\WishlistAuthServiceInterface;

/**
 * Class Shopware_Controllers_Frontend_Wishlist
 *
 * @category   Shopware
 *
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Wishlist extends Enlight_Controller_Action
{
    const ERROR_GENERAL = 100;

    const ERROR_NO_NAME = 110;

    const ERROR_NAME_ALREADY_EXISTS = 120;

    /**
     * @var Repository
     */
    private $mediaRepository;

    /**
     * @var WishlistAuthServiceInterface
     */
    private $authService;

    /**
     * preDispatch method for disabling renderer and removing expired carts
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);

        if ($this->container->has('shopware_account.store_front_greeting_service')) {
            $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
        }

        $setNoRenderArray = [
            'search',
            'save',
            'changeQuantity',
            'share',
            'changeName',
            'getArticle',
            'changePublished',
            'removeOne',
            'addToList',
        ];

        // Disable View renderer for ajax controller
        if (in_array($this->Request()->getActionName(), $setNoRenderArray, true)) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }

        // Remove expired carts
        $this->removeExpiredCarts();

        $this->authService = $this->container->get('swag_advanced_cart.wishlist_auth_service');

        // Check if user is trying to receive a basket token
        $request = $this->Request();
        $token = $request->getActionName();
        $tokenLength = strlen($token);
        if ($tokenLength !== 30) {
            return;
        }

        $this->receiveBasket($token);
    }

    /**
     * Display saved baskets or redirect to login, if user is not logged in
     */
    public function indexAction()
    {
        $view = $this->View();
        $userID = $this->get('session')->get('sUserId');

        // Redirect user to login form
        if (!$userID) {
            $this->redirect(['controller' => 'account', 'sTarget' => 'wishlist']);
        }

        $view->assign($this->prepareCarts($userID));

        $session = $this->container->get('session');

        // show partner statistic menu
        $partnerModel = $this->container->get('models')->getRepository(Partner::class)->findOneBy([
            'customerId' => $session->offsetGet('sUserId'),
        ]);

        if (!empty($partnerModel)) {
            $session->offsetSet('partnerId', $partnerModel->getId());
            $this->View()->assign('partnerId', $partnerModel->getId());
        }
    }

    /**
     * Display the public view of a wish-list
     * throw 404 if wish-list doesn't exist
     */
    public function publicAction()
    {
        $request = $this->Request();
        $response = $this->Response();
        $hash = $request->getParam('id');
        $view = $this->View();

        if (!$hash) {
            throw new Exception('id parameter is required');
        }

        $cart = $this->getCartByHash($hash);

        if (!$cart) {
            $response->setHttpResponseCode(404);

            return;
        }

        $items = $this->prepareCartItems($cart);

        $wishList = [
            'basketID' => $cart->getId(),
            'name' => $cart->getName(),
            'user_id' => $cart->getCustomer()->getId(),
            'user_firstname' => $cart->getCustomer()->getDefaultBillingAddress()->getFirstName(),
            'user_lastname' => $cart->getCustomer()->getDefaultBillingAddress()->getLastName(),
            'modified' => $cart->getModified(),
            'hash' => $cart->getHash(),
            'items' => $items,
        ];

        $view->assign('wishlist', $wishList);
    }

    /**
     * save basket with own name
     */
    public function createCartAction()
    {
        // Get Basket Name
        $name = strip_tags($this->request->getPost('name', null));
        if ($name === null || $name === '') {
            $this->View()->assign('sErrorFlag', true);
            $this->forward('index');

            return;
        }

        $cartHandler = $this->container->get('swag_advanced_cart.cart_handler');

        try {
            $id = $cartHandler->createWishList($name);
            if ($id) {
                $this->addProductToWishList($id, $this->get('session')->get('keepWishList'));
                $this->get('session')->offsetUnset('keepWishList');
            }

            $this->redirect(['controller' => 'wishlist']);
        } catch (\Exception $ex) {
            $this->View()->assign('sErrorFlag', true);
            $this->forward('index');
        }
    }

    /**
     * save basket with own name
     */
    public function saveAction()
    {
        $name = strip_tags($this->Request()->getPost('name'));
        $published = $this->Request()->getPost('published');
        $cartHandler = $this->container->get('swag_advanced_cart.cart_handler');

        $errors = $this->validateWishlistName($name, $cartHandler);

        if ($errors['success'] === false) {
            echo json_encode($errors);

            return;
        }

        $cartData = $cartHandler->saveCart($name, $published);

        if (!$cartData) {
            $errors['success'] = false;
            $errors['error'][] = self::ERROR_GENERAL;
            echo json_encode($errors);

            return;
        }

        echo json_encode($cartData);
    }

    /**
     * remove saved baskets
     */
    public function removeAction()
    {
        $basketID = $this->Request()->get('id');
        $userID = $this->get('session')->get('sUserId');

        $customer = null;
        if ($userID) {
            $customer = $this->getModelManager()->find(Customer::class, $userID);
        }

        if (!$customer) {
            throw new \RuntimeException("Customer by ID {$userID} not found");
        }

        if (!$this->authService->authenticateById($basketID)) {
            throw new \RuntimeException('User not authorized');
        }

        $repo = $this->getModelManager()->getRepository(Cart::class);
        $cart = $repo->findOneBy(['id' => $basketID, 'customer' => $customer]);
        $this->getModelManager()->remove($cart);
        $this->getModelManager()->flush();

        $url = $this->get('router')->assemble(['controller' => 'wishlist']);
        $this->redirect($url);
    }

    /**
     * remove one product in saved basket
     */
    public function removeOneAction()
    {
        $userID = $this->get('session')->get('sUserId');
        $cartItemId = $this->Request()->get('cartItemId');

        $builder = $this->getModelManager()->createQueryBuilder();
        $builder->select('item')
            ->from(CartItem::class, 'item')
            ->where('item.id = :itemId')
            ->innerJoin('item.cart', 'cart')
            ->innerJoin('cart.customer', 'customer')
            ->andWhere('customer.id = :userId')
            ->setParameter('itemId', $cartItemId)
            ->setParameter('userId', $userID);

        /** CartItem $cartItem */
        $cartItem = $builder->getQuery()->getOneOrNullResult();

        if (!$cartItem) {
            throw new \RuntimeException("CartItem by ID {$cartItemId} not found");
        }

        // Update last modified Date
        /** @var Cart $cart */
        $cartRepo = $this->getModelManager()->getRepository(Cart::class);
        $cart = $cartRepo->findOneBy(['id' => $cartItem->getCart()->getId()]);
        $cart->setModified(date('Y-m-d H:i:s'));

        if (!$this->authService->authenticateById($cart->getId())) {
            throw new \RuntimeException('User not authorized');
        }

        $this->getModelManager()->remove($cartItem);
        $this->getModelManager()->flush();

        $cartCount = $cart->getCartItems()->count();

        echo json_encode(['success' => true, 'count' => $cartCount]);
    }

    /**
     * change the quantity of an item inside a saved basket
     */
    public function changeQuantityAction()
    {
        $post = $this->Request()->getPost();
        $cartItemId = $post['itemId'];
        $quantity = $post['quantity'];

        $repo = $this->getModelManager()->getRepository(CartItem::class);
        /** @var CartItem $cartItemModel */
        $cartItemModel = $repo->find($cartItemId);
        if (!$cartItemModel instanceof CartItem) {
            echo json_encode(['success' => false, 'message' => "Could not find cardItemModel with ID $cartItemId."]);

            return;
        }

        if (!$this->authService->authenticateById($cartItemModel->getCart()->getId())) {
            throw new \RuntimeException('User not authorized');
        }

        $cartItemModel->setQuantity($quantity);
        $this->getModelManager()->flush();

        $product = $this->getProductByNumber($cartItemModel->getProductOrderNumber());
        $singlePrice = str_replace(',', '.', $product['price']);
        $totalPrice = $quantity * $singlePrice;
        $totalPrice = $this->formatPrice($totalPrice, true);

        echo json_encode(['success' => true, 'totalPrice' => $totalPrice, 'quantity' => $quantity]);
    }

    /**
     * restore whole baskets
     */
    public function restoreAction()
    {
        // Get Basket Name
        $session = $this->get('session')->get('sessionId');
        $basketID = $this->Request()->get('id');

        if (!$this->authService->authenticateById($basketID)) {
            if (!$this->authService->isPublic($basketID)) {
                throw new \RuntimeException('User not authorized');
            }
        }

        // Delete customer's basket if this was configured
        if ($this->get('config')->getByNamespace('SwagAdvancedCart', 'clearBasketOnRestore', true)) {
            $builder = $this->getModelManager()->createQueryBuilder();
            $builder->delete(Basket::class, 'basket')
                ->where('basket.sessionId = :sessionId')
                ->setParameter('sessionId', $session);

            $builder->getQuery()->execute();
        }

        /** @var Cart $cartModel */
        $cartModel = $this->getModelManager()->find(Cart::class, $basketID);

        /** @var CartItem $cartItem */
        foreach ($cartModel->getCartItems() as $key => $cartItem) {
            $item = [
                'ordernumber' => $cartItem->getProductOrderNumber(),
                'quantity' => $cartItem->getQuantity(),
            ];

            // Remove products without category
            $productid = $this->get('modules')->Articles()->sGetArticleIdByOrderNumber($item['ordernumber']);
            $exist = $this->existsInMainCategory($productid);

            if (!$exist || !$item['ordernumber']) {
                // remove inactive product
                unset($item[$key]);
                continue;
            }

            // Insert products into Basket
            $this->get('modules')->Basket()->sAddArticle($item['ordernumber'], $item['quantity']);
        }

        $url = $this->get('router')->assemble(
            [
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'cart',
            ]
        );
        $this->redirect($url);
    }

    /**
     * restores one product from saved basket
     *
     * @deprecated since 1.2.3 will be removed in 2.0
     */
    public function restoreOneAction()
    {
        $orderNumber = $this->Request()->get('id');
        $quantity = $this->Request()->get('quantity');

        $sql = 'SELECT articleID FROM s_articles_details WHERE ordernumber=?';
        $id = $this->get('db')->fetchOne($sql, [$orderNumber]);

        if (!$this->existsInMainCategory($id)) {
            throw new Exception('This product is not available');
        }

        // Insert Article into Basket
        $this->get('modules')->Basket()->sAddArticle($orderNumber, $quantity);

        $url = $this->get('router')->assemble(['controller' => 'checkout', 'action' => 'cart']);
        $this->redirect($url);
    }

    /**
     * Get shared Basket
     */
    public function shareAction()
    {
        // Get items
        $request = $this->Request();
        $post = $request->getPost();
        $to = $post['to'];

        if (!$this->authService->authenticateByHash($request->get('hash'))) {
            throw new \RuntimeException('User not authorized');
        }

        $to_exploded = str_replace(' ', '', $to);
        $to_exploded = explode(',', $to_exploded);

        // Filter double entries
        $to_exploded = array_unique($to_exploded);

        // Check valid mail
        $validator = new Zend_Validate_EmailAddress();
        foreach ($to_exploded as $address) {
            if (!$address || !$validator->isValid($address)) {
                echo json_encode(
                    [
                        'success' => false,
                        'message' => str_replace(
                            '__ADDRESS__',
                            $address,
                            $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/controller_messages')
                                ->get('invalidEmailAddress', 'The address __ADDRESS__ is not valid.')
                        ),
                    ]
                );

                return;
            }
        }

        // Get user information
        $userData = $this->get('modules')->Admin()->sGetUserData();
        $firstName = $userData['billingaddress']['firstname'];
        $lastName = $userData['billingaddress']['lastname'];

        // Parse smarty vars to template mailer
        $context = [
            'name' => $firstName . ' ' . $lastName,
            'shopName' => $this->get('shop')->getName(),
            'url' => $this->getWishListUrl($request, $post['hash']),
            'message' => $post['message'],
        ];

        /** \Shopware\Models\Mail\Mail $mailModel */
        $mailModel = $this->getModelManager()->getRepository('Shopware\Models\Mail\Mail')
            ->findOneBy(['name' => 'sSHARECART']);

        if (!$mailModel) {
            throw new RuntimeException('Could not retrieve MailModel');
        }

        /** @var Shopware_Components_TemplateMail $templateMailer */
        $templateMailer = $this->get('templatemail');
        $mail = $templateMailer->createMail($mailModel, $context, $this->get('shop'));
        foreach ($to_exploded as $address) {
            $mail->addTo($address);
        }
        $mail->send();

        echo json_encode(
            [
                'success' => true,
                'message' => $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/controller_messages')
                    ->get('listShared', 'The wishlist has been successfully shared'),
            ]
        );
    }

    /**
     * changes saved cart name
     */
    public function changeNameAction()
    {
        $post = $this->Request()->getPost();
        $basketId = $post['basketId'];
        $newName = strip_tags($post['newName']);
        $userId = (int) $this->get('session')->get('sUserId');
        $shopId = $this->get('shop')->getId();

        if (!$this->authService->authenticateById($basketId)) {
            throw new \RuntimeException('User not authorized');
        }

        $repo = $this->getModelManager()->getRepository(Cart::class);
        /** @var Cart $cartModel */
        $cartModel = $repo->find($basketId);
        $customerId = (int) $cartModel->getCustomer()->getId();
        if ($customerId !== $userId) {
            $this->View()->assign(
                ['success' => false, 'message' => "Access denied for user with id: {$userId}"]
            );

            return;
        }

        $sql = 'SELECT `name`
                FROM `s_order_basket_saved`
                WHERE `name` = :name
                  AND `user_id` = :userId
                  AND `shop_id` = :shopId';

        $existingBasket = $this->get('db')->fetchOne($sql, [
            'name' => $newName,
            'userId' => $userId,
            'shopId' => $shopId,
        ]);
        // Check if basket already named like $newName
        if ($newName === $existingBasket) {
            $this->View()->assign(
                ['success' => false, 'message' => "Basket already exists with name: {$newName}"]
            );

            return;
        }

        $cartModel->setName($newName);
        $this->getModelManager()->flush();

        $this->View()->assign(
            ['success' => true, 'message' => "Basket name successfully changed to: {$newName}"]
        );
    }

    /**
     * changes the published flag
     */
    public function changePublishedAction()
    {
        $post = $this->Request()->getPost();
        $basketId = $post['basketId'];
        $newState = $post['newState'];
        $userId = (int) $this->get('session')->get('sUserId');

        $repo = $this->getModelManager()->getRepository(Cart::class);
        /** @var Cart $cartModel */
        $cartModel = $repo->find($basketId);
        $customerId = (int) $cartModel->getCustomer()->getId();
        if ($customerId !== $userId) {
            echo json_encode(['success' => false, 'message' => "Access denied for user with id: {$userId}"]);

            return;
        }

        $cartModel->setPublished($newState);
        $this->getModelManager()->flush();

        echo json_encode(['success' => true, 'message' => "Publish flag set to: {$newState}"]);
    }

    /**
     * adds product to saved cart
     *
     * @throws \RuntimeException
     */
    public function getArticleAction()
    {
        // Get items
        $post = $this->Request()->getPost();
        $basketId = $post['basketId'];
        $productName = trim($post['articleName']);

        if (!$this->authService->authenticateById($basketId)) {
            throw new \RuntimeException('User not authorized');
        }

        $response = $this->addProductToWishList($basketId, $productName);

        $this->View()->assign('theme', $this->getThemeConfig());
        $this->View()->assign('item', $response['data']);
        $this->View()->assign('sBasketItem', $response['data']['article']);
        $itemTemplate = $this->View()->fetch('frontend/wishlist/item_form.tpl');

        echo json_encode(
            [
                'success' => $response['success'],
                'type' => $response['type'],
                'message' => $response['message'],
                'data' => $response['data'],
                'template' => $itemTemplate,
            ]
        );
    }

    /**
     * searches products in saved cart controller live
     */
    public function searchAction()
    {
        $results = $this->searchArticles();

        echo json_encode($results);
    }

    /**
     * adds a new item to the wist list
     */
    public function addToListAction()
    {
        $cartHandler = $this->container->get('swag_advanced_cart.cart_handler');

        $lists = $cartHandler->addToList($this->Request()->getPost());

        if (!$lists) {
            return;
        }

        echo json_encode($lists);
    }

    /**
     * loads the template for the modal-window on the detail-page
     */
    public function detailModalAction()
    {
        $userId = $this->get('session')->get('sUserId');
        if (!$userId) {
            $this->get('session')->offsetSet('keepWishList', $this->Request()->getParam('orderNumber'));
        }

        /** @var QueryBuilder $builder */
        $builder = $this->getModelManager()->getConnection()->createQueryBuilder();
        $result = $builder->select('cart.*, GROUP_CONCAT(items.article_ordernumber) as orderNumbers')
            ->from('s_order_basket_saved', 'cart')
            ->leftJoin('cart', 's_user', 'customer', 'cart.user_id = customer.id')
            ->leftJoin('cart', 's_order_basket_saved_items', 'items', 'items.basket_id = cart.id')
            ->where('customer.id = :customerId')
            ->andWhere('cart.shop_id = :shopId')
            ->andWhere('LENGTH(cart.name) > 0')
            ->groupBy('cart.id')
            ->setParameters(['customerId' => $userId, 'shopId' => $this->get('shop')->getId()])
            ->execute()
            ->fetchAll();

        $orderNumber = $this->Request()->getParam('orderNumber');

        $cartHandler = $this->container->get('swag_advanced_cart.cart_handler');
        $result = $cartHandler->prepareCartForModal($result);

        $this->View()->assign([
            'allCartsByUser' => $result,
            'sArticle' => $this->getProductByNumber($orderNumber),
            'userId' => $userId,
            'quantity' => (int) $this->Request()->get('quantity'),
            'customizable' => $this->Request()->get('customized'),
        ]);
    }

    /**
     * loads the template for the modal-window on the detail-page of a customizing-article
     */
    public function detailCustomizingModalAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');
        $this->View()->assign('sArticle', $this->getProductByNumber($orderNumber));
    }

    /**
     * loads the confirm-template for the modal-window on the wishlist-page
     */
    public function wishlistConfirmModalAction()
    {
        $this->View()->assign('wishListName', $this->Request()->getParam('name'));
        $this->View()->assign('deleteUrl', $this->Request()->getParam('deleteUrl'));
    }

    /**
     * loads the share-template for the modal-window
     */
    public function shareModalAction()
    {
        // Assign User Data for sharing
        $userData = $this->get('modules')->Admin()->sGetUserData();

        $data = [
            'eMail' => $userData['additional']['user']['email'],
            'name' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'],
            'hash' => $this->Request()->getParam('hash'),
        ];

        $this->View()->assign($data);
    }

    /**
     * redirects to the login-form with sTarget to the cart.
     */
    public function loginCartAction()
    {
        $userID = $this->get('session')->get('sUserId');

        // Redirect user to login form
        if (!$userID) {
            $this->redirect(['controller' => 'account', 'sTarget' => 'checkout', 'sTargetAction' => 'cart']);
        }
    }

    /**
     * This method is for the button(link) addToWishlist in the ArticleDetail page.
     * It returns the needed data.
     */
    public function getCurrentWishListsAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender();

        $userID = $this->get('session')->get('sUserId');
        if ($userID === null) {
            $this->get('session')->offsetSet('keepWishList', $this->Request()->getParam('orderNumber'));
            echo json_encode(['success' => false, 'message' => 'noUser']);

            return;
        }

        $sql = 'SELECT basket.id, basket.name, COUNT(item.id) AS score
                FROM `s_order_basket_saved` AS basket
                  LEFT JOIN `s_order_basket_saved_items` AS item
                    ON item.basket_id = basket.id
                WHERE basket.user_id = :userId
                AND shop_id = :shopId
                AND basket.name IS NOT NULL
                GROUP BY basket.id';

        try {
            $shopId = $this->get('shop')->getId();
            $result = $this->get('db')->fetchAssoc($sql, ['userId' => $userID, 'shopId' => $shopId]);
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'noData']);

                return;
            }
        } catch (\Exception $ex) {
            echo json_encode(['success' => false, 'message' => $ex->getMessage()]);

            return;
        }

        $result = $this->prepareResult($result);
        echo json_encode(['success' => true, 'data' => $result]);
    }

    /**
     * Returns minpurchase, maxpurchase, purchasesteps and pckunit.
     * Which are needed for the quantity selection in the template.
     *
     * @param string $orderNumber
     *
     * @return array
     */
    protected function getPurchaseAdditionalData($orderNumber)
    {
        $sql = 'SELECT maxpurchase, minpurchase, purchasesteps, packunit
                FROM s_articles_details
                WHERE ordernumber = ?';

        $purchaseInfo = $this->get('db')->fetchRow($sql, [$orderNumber]);

        //set default values
        if (!$purchaseInfo['minpurchase']) {
            $purchaseInfo['minpurchase'] = 1;
        }
        if (!$purchaseInfo['purchasesteps']) {
            $purchaseInfo['purchasesteps'] = 1;
        }
        if (!$purchaseInfo['maxpurchase']) {
            $purchaseInfo['maxpurchase'] = $this->get('config')->get('sMAXPURCHASE');
        }

        $purchaseInfo['sVariants'] = [];

        return $purchaseInfo;
    }

    /**
     * Get the media repository
     *
     * @return Repository
     */
    private function getMediaRepository()
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = $this->getModelManager()->getRepository(Media::class);
        }

        return $this->mediaRepository;
    }

    /**
     * @param string      $name
     * @param CartHandler $cartHandler
     *
     * @return array
     */
    private function validateWishlistName($name, CartHandler $cartHandler)
    {
        $errors = [
            'success' => true,
            'error' => [],
        ];

        if (!$name) {
            $errors['success'] = false;
            $errors['error'][] = self::ERROR_NO_NAME;
        }

        try {
            $cartHandler->checkIfBasketNameExists(
                $name,
                $userID = $this->get('session')->get('sUserId')
            );
        } catch (\RuntimeException $exception) {
            $errors['success'] = false;
            $errors['error'][] = self::ERROR_NAME_ALREADY_EXISTS;
        }

        return $errors;
    }

    /**
     * Helper function for price formatting
     *
     * @param      $price
     * @param bool $currencySymbol
     *
     * @return string
     */
    private function formatPrice($price, $currencySymbol = false)
    {
        $price = number_format($price, 2, ',', '.');

        if ($currencySymbol) {
            $price .= ' € *';
        }

        return $price;
    }

    /**
     * check for product exists in active category
     *
     * @param $productId
     *
     * @return mixed
     */
    private function existsInMainCategory($productId)
    {
        $categoryId = $this->get('shop')->getCategory()->getId();

        $exist = $this->get('db')->fetchRow(
            'SELECT * FROM s_articles_categories_ro WHERE categoryID = ? AND articleID = ?',
            [$categoryId, $productId]
        );

        return $exist;
    }

    /**
     * Get product and cover by order number
     *
     * @param string $orderNumber
     *
     * @return array
     */
    private function getProductByNumber($orderNumber = null)
    {
        $product = $this->get('modules')->Articles()->sGetProductByOrdernumber($orderNumber);

        if (!$product) {
            $product = $this->get('modules')->Articles()->sGetPromotionById('fix', null, $orderNumber);
        }

        if (!$product) {
            return false;
        }

        // If product has variants, we need to append the additional text to the name
        if ($product['sConfigurator']) {
            $product = $this->getProductAdditionalText($product, $orderNumber);
        }

        // Fetching variant image
        $productAlbum = $this->getMediaRepository()->getAlbumWithSettingsQuery(-1)->getOneOrNullResult();
        $cover = $this->get('modules')->Articles()->getArticleCover($product['articleID'], $orderNumber, $productAlbum);
        $product['cover'] = $cover['src'];

        return $product;
    }

    /**
     * @param array  $product
     * @param string $orderNumber
     *
     * @return array|ListProduct
     */
    private function getProductAdditionalText(array $product, $orderNumber)
    {
        $listProduct = new ListProduct(
            (int) $product['articleID'],
            (int) $product['articleDetailsID'],
            $orderNumber
        );

        $listProduct->setAdditional($product['additionaltext']);

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();
        $productAdd = $this->container->get('shopware_storefront.additional_text_service')->buildAdditionalText($listProduct, $context);

        $product['additionaltext'] = $productAdd->getAdditional();

        return $product;
    }

    /**
     * restores shared basket
     *
     * @param $sharedBasketToken
     *
     * @throws Enlight_Exception
     */
    private function receiveBasket($sharedBasketToken)
    {
        $builder = $this->getModelManager()->createQueryBuilder();
        $builder->select(['items'])
            ->from(SharedCart::class, 'items')
            ->where('items.token = :token')
            ->setParameter('token', $sharedBasketToken);

        $items = $builder->getQuery()->getResult();

        /** @var SharedCart $item */
        foreach ($items as $item) {
            $this->get('modules')->Basket()->sAddArticle($item->getOrderNumber(), $item->getQuantity());
        }

        $url = $this->get('router')->assemble(['controller' => 'checkout', 'action' => 'cart']);
        $this->redirect($url);
    }

    /**
     * Helper method to add a product to a wish-list.
     *
     * @param $basketId
     * @param $productName
     *
     * @return array
     */
    private function addProductToWishList($basketId, $productName)
    {
        // filter product order number [Syntax: product name ( order number ) ]
        preg_match('#\((.*?)\)#', $productName, $orderNumberArray);
        $orderNumber = null;

        if (!empty($orderNumberArray)) {
            $orderNumber = $orderNumberArray[1];
        }

        // Try to fetch order number directly if product name isn't set by ajax
        if (!$orderNumber) {
            $orderNumber = $productName;
        }

        // We could not control what the customer fills into the search field, so we select the orderNumber again from the DB
        // As MySQL is case insensitive on where condition we will get the correct written orderNumber
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select('ordernumber')
            ->from('s_articles_details')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber);

        $orderNumber = $query->execute()->fetchColumn();

        if ($orderNumber == $this->get('session')->get('keepWishList')) {
            $this->get('session')->offsetUnset('keepWishList');
        }

        // Check if product exists
        $productVariantRepo = $this->getModelManager()->getRepository(ProductVariant::class);
        $variantModel = $productVariantRepo->findOneBy(['number' => $orderNumber]);

        // Check if product exists
        if (!$variantModel) {
            return [
                'success' => false,
                'type' => 'notfound',
                'message' => $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/controller_messages')
                    ->get('articleNotFound', 'The product could not be found.'),
            ];
        }

        // Check if product is inside main category
        $exist = $this->existsInMainCategory($variantModel->getArticleId());

        // Check if product is active
        $active = $variantModel->getActive();
        if (!$active || !$exist) {
            return [
                'success' => false,
                'type' => 'notfound',
                'message' => $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/controller_messages')
                    ->get('articleNotFound', 'The product could not be found.'),
            ];
        }

        // Update last modified Date
        /** @var Cart $cart */
        $cartRepo = $this->getModelManager()->getRepository(Cart::class);
        $cart = $cartRepo->findOneBy(['id' => $basketId]);
        $cart->setModified(date('Y-m-d H:i:s'));
        $this->getModelManager()->persist($cart);
        $this->getModelManager()->flush($cart);

        // Check if product is already stored in wish list
        $cartItemRepo = $this->getModelManager()->getRepository(CartItem::class);
        /** @var CartItem $cartItemModel */
        $cartItemModel = $cartItemRepo->findOneBy(['productOrderNumber' => $orderNumber, 'cart' => $basketId]);

        if ($cartItemModel) {
            $oldQuantity = $cartItemModel->getQuantity();
            $cartItemModel->setQuantity(++$oldQuantity);

            $this->getModelManager()->persist($cartItemModel);
            $this->getModelManager()->flush($cartItemModel);

            $data = [
                'ordernumber' => $orderNumber,
                'basketId' => $basketId,
            ];

            return [
                'success' => true,
                'type' => 'readded',
                'message' => $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/controller_messages')
                    ->get('articleReAdded', 'The product already existed in the cart and was added another time.'),
                'data' => $data,
            ];
        }

        // Adding product to saved basket
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $queryBuilder->insert('s_order_basket_saved_items')
            ->values([
                'basket_id' => ':cartId',
                'article_ordernumber' => ':productNumber',
                'quantity' => 1,
            ])
            ->setParameter('cartId', $cart->getId())
            ->setParameter('productNumber', $orderNumber)
            ->execute();

        $insertId = $this->get('dbal_connection')->lastInsertId();

        $product = $this->get('modules')->Articles()->sGetArticleById($variantModel->getArticleId(), null, $orderNumber);

        //For compatibility issues
        $product['articlename'] = $product['articleName'];

        $data = [
            'id' => $insertId,
            'articleID' => $variantModel->getArticleId(),
            'ordernumber' => $orderNumber,
            'name' => $product['articleName'],
            'price' => $product['price'],
            'quantity' => 1,
            'article' => $product,
        ];

        //For compatibility issues
        $data['data'] = $data;

        return [
            'success' => true,
            'type' => 'added',
            'message' => null,
            'data' => $data,
        ];
    }

    /**
     * removes expired carts. Configure the expire date in Shopware Backend Plugin Configuration
     */
    private function removeExpiredCarts()
    {
        $dateTime = new \DateTime('now');
        $expiredDate = $dateTime->format('Y-m-d');

        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
        $expiredBaskets = $queryBuilder->select('*')
            ->from('s_order_basket_saved')
            ->where('expire < :expiredDate')
            ->andWhere('`name` IS NULL')
            ->setParameter('expiredDate', $expiredDate)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        if (!$expiredBaskets) {
            return;
        }

        foreach ($expiredBaskets as $basket) {
            $repo = $this->getModelManager()->getRepository(Cart::class);
            $cart = $repo->findOneBy(['id' => $basket['id']]);
            $this->getModelManager()->remove($cart);
        }
        $this->getModelManager()->flush();
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function prepareResult(array $result)
    {
        $returnValue = [];
        foreach ($result as $key => $value) {
            $returnValue[] = $value;
        }

        return $returnValue;
    }

    /**
     * Helper function for generating url to wish-list
     *
     * @param Enlight_Controller_Request_Request $request
     * @param                                    $hash
     *
     * @return string
     */
    private function getWishListUrl(Enlight_Controller_Request_Request $request, $hash)
    {
        $url = $request->getScheme()
            . '://'
            . $request->getHttpHost()
            . $request->getBasePath()
            . '/wishlist/public/id/'
            . $hash;

        return $url;
    }

    /**
     * Helper method to search for the products depending on the shopware-version
     *
     * @return bool|array
     */
    private function searchArticles()
    {
        return $this->searchByCriteria();
    }

    /**
     * Helper method to search for products using the new search-components
     *
     * @return array
     */
    private function searchByCriteria()
    {
        /** @var $context ProductContextInterface */
        $context = $this->get('shopware_storefront.context_service')->getProductContext();

        /** @var Criteria $criteria */
        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createAjaxSearchCriteria($this->Request(), $context);

        /** @var $result ProductSearchResult */
        $result = $this->get('shopware_search.product_search')->search($criteria, $context);

        $results = array_map(function ($item) {
            return $this->convertToCartItem($item);
        }, $result->getProducts());

        if (!$results) {
            return false;
        }

        return $results;
    }

    /**
     * Helper method to convert the product-array to a valid item for the advanced-cart search
     *
     * @param $item
     *
     * @return array
     */
    private function convertToCartItem($item)
    {
        /** @var LegacyStructConverter $converter */
        $converter = $this->get('legacy_struct_converter');
        $product = $converter->convertListProductStruct($item);
        // Replace square brackets if exists
        $name = str_replace(['(', ')'], ['[', ']'], $product['articleName']);

        return [$product['ordernumber'], $name . ' ' . $product['additionaltext']];
    }

    /**
     * Helper method to read all given carts.
     *
     * @param $userID
     *
     * @return array
     */
    private function getCarts($userID)
    {
        $builder = $this->getModelManager()->createQueryBuilder();
        $builder->select(['cart', 'items', 'details'])
            ->from(Cart::class, 'cart')
            ->leftJoin('cart.customer', 'customer')
            ->leftJoin('cart.cartItems', 'items')
            ->leftJoin('items.details', 'details')
            ->where('customer.id = :customerId')
            ->andWhere('cart.shopId = :shopId')
            ->andWhere('LENGTH(cart.name) > 0')
            ->orderBy('items.id', 'DESC')
            ->setParameter('customerId', $userID)
            ->setParameter('shopId', $this->get('shop')->getId());

        return $builder->getQuery()->getResult();
    }

    /**
     * Helper method to prepare all the carts for the frontend-template.
     *
     * @param $userID
     *
     * @return array
     */
    private function prepareCarts($userID)
    {
        $keepWishList = $this->get('session')->get('keepWishList');

        $carts = $this->getCarts($userID);

        $baskets = [];

        /** @var Cart $cart */
        foreach ($carts as $cart) {
            $items = $this->prepareCartItems($cart);

            $baskets[] = [
                'basketID' => $cart->getId(),
                'hash' => $cart->getHash(),
                'name' => $cart->getName(),
                'published' => $cart->getPublished(),
                'items' => $items,
            ];
        }

        // Assign User Data for sharing
        $userData = $this->get('modules')->Admin()->sGetUserData();

        $carts = [
            'savedBaskets' => $baskets,
            'eMail' => $userData['additional']['user']['email'],
            'name' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'],
        ];

        if ($keepWishList) {
            $carts['storedWishlist'] = true;
        }

        $carts['sUserLoggedIn'] = $this->get('modules')->Admin()->sCheckUser();

        return $carts;
    }

    /**
     * A helper function that prepares a single cart
     *
     * @param Cart $cart
     *
     * @return null|array
     */
    private function prepareCartItems($cart)
    {
        $items = [];

        /** @var CartItem $cartItem */
        foreach ($cart->getCartItems() as $cartItem) {
            if (!$cartItem->getDetail() || !$cartItem->getDetail()->getActive()) {
                continue;
            }
            $orderNumber = $cartItem->getProductOrderNumber();
            $product = $this->prepareArticle($orderNumber, $cartItem);

            if ($product) {
                $items[] = [
                    'id' => $cartItem->getId(),
                    'ordernumber' => $cartItem->getProductOrderNumber(),
                    'quantity' => $cartItem->getQuantity(),
                    'name' => $product['articleName'],
                    'article' => $product,
                ];
            }
        }

        return $items;
    }

    /**
     * A helper function that returns a prepared product object ready to be displayed in the frontend
     *
     * @param int      $orderNumber
     * @param CartItem $cartItem
     *
     * @return null|Shopware\Models\Article\Article
     */
    private function prepareArticle($orderNumber, $cartItem)
    {
        if (!$orderNumber) {
            return null;
        }

        //Gets the product by the order number
        $productId = $this->get('modules')->Articles()->sGetArticleIdByOrderNumber($orderNumber);

        if (!$productId) {
            return null;
        }

        try {
            $product = $this->get('modules')->Articles()->sGetArticleById($productId, null, $orderNumber);
        } catch (\RuntimeException $e) {
            // if product is not found the ProductNumberService will throw an exception
            return null;
        }

        if (!$product || !$this->existsInMainCategory($productId)) {
            return null;
        }

        $sql = 'SELECT active FROM s_articles_details WHERE ordernumber = :orderNumber;';
        $isVariantActive = $this->get('db')->fetchOne($sql, ['orderNumber' => $orderNumber]);

        if (!$isVariantActive) {
            $message = $this->get('snippets')->getNamespace('frontend/plugins/swag_advanced_cart/plugin')->get('ArticleNotAvailable');
            $items[] = [
                'id' => $cartItem->getId(),
                'ordernumber' => $cartItem->getProductOrderNumber(),
                'quantity' => $cartItem->getQuantity(),
                'name' => $message,
                'article' => [
                    'articleName' => $message,
                    'articlename' => $message,
                ],
            ];

            return null;
        }

        $product['price'] = str_replace(',', '.', $product['price']) * $cartItem->getQuantity();

        //For compatibility issues
        $product['articlename'] = $product['articleName'];

        if ($product['sConfigurator']) {
            if ($product['additionaltext']) {
                $product['articlename'] .= ' ' . $product['additionaltext'];
                $product['articleName'] .= ' ' . $product['additionaltext'];
            } else {
                $productName = $this->get('modules')->Articles()->sGetArticleNameByOrderNumber($orderNumber);
                $product['articlename'] = $productName;
                $product['articleName'] = $productName;
            }
        }

        $product['img'] = $product['image']['src'][0];

        return $product;
    }

    /**
     * Helper method to retrieve a cart by its hash.
     *
     * @param $hash
     *
     * @return mixed
     */
    private function getCartByHash($hash)
    {
        $builder = $this->getModelManager()->createQueryBuilder();
        $builder->select(['cart', 'items', 'details'])
            ->from(Cart::class, 'cart')
            ->leftJoin(
                'cart.customer',
                'customer'
            )
            ->leftJoin('cart.cartItems', 'items')
            ->leftJoin('items.details', 'details')
            ->where('cart.hash = :hash')
            ->andWhere('cart.published = 1')
            ->setParameter('hash', $hash);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array
     */
    private function getThemeConfig()
    {
        /** @var ContextService $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $shopStruct = $contextService->getShopContext()->getShop();
        $shopId = $shopStruct->getId();
        $themeId = $shopStruct->getTemplate()->getId();

        $theme = $this->get('models')->getRepository(Template::class)->find($themeId);
        $shop = $this->get('models')->getRepository(Shop::class)->find($shopId);

        /** @var Inheritance $themeInheritanceService */
        $themeInheritanceService = $this->get('theme_inheritance');

        return $themeInheritanceService->buildConfig($theme, $shop);
    }
}
