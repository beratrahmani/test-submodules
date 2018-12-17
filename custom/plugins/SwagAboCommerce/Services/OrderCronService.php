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

namespace SwagAboCommerce\Services;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session;
use Shopware\Components\Logger;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Router;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;

class OrderCronService implements OrderCronServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /** @var Logger */
    private $pluginLogger;

    /**
     * @param AboCommerceServiceInterface $aboCommerceService
     * @param DependencyProviderInterface $dependencyProvider
     * @param Connection                  $connection
     * @param ModelManager                $modelManager
     * @param Shopware_Components_Config  $config
     * @param Router                      $router
     * @param Logger                      $pluginLogger
     */
    public function __construct(
        AboCommerceServiceInterface $aboCommerceService,
        DependencyProviderInterface $dependencyProvider,
        Connection $connection,
        ModelManager $modelManager,
        Shopware_Components_Config $config,
        Router $router,
        Logger $pluginLogger
    ) {
        $this->aboCommerceService = $aboCommerceService;
        $this->dependencyProvider = $dependencyProvider;
        $this->connection = $connection;
        $this->modelManager = $modelManager;
        $this->config = $config;
        $this->router = $router;
        $this->pluginLogger = $pluginLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function getOverdueOrders()
    {
        $now = $this->getDateNow();

        return $this->connection->createQueryBuilder()
            ->select('aboOrders.id as aboId, orders.language as shopId')
            ->from('s_plugin_swag_abo_commerce_orders', 'aboOrders')
            ->innerJoin('aboOrders', 's_order', 'orders', 'aboOrders.order_id = orders.id')
            ->andWhere('aboOrders.due_date <= :dateNow')
            ->andWhere('aboOrders.due_date <= aboOrders.last_run OR (aboOrders.endless_subscription = 1 AND aboOrders.last_run IS NULL)')
            ->setParameter('dateNow', $now)
            ->setMaxResults(5)
            ->execute()
            ->fetchAll();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function setRouterContext($shopId)
    {
        $shopRepository = $this->modelManager->getRepository(Shop::class);
        $shop = false;

        if (!$shopId) {
            $shop = $shopRepository->getActiveDefault();
        }

        if (!$shop) {
            $shop = $shopRepository->getActiveById($shopId);
        }

        if (!$shop) {
            throw new \RuntimeException(
                sprintf(
                    'No active shop with id %s found for this order.',
                    $shopId
                )
            );
        }

        $context = Context::createFromShop($shop, $this->config);

        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     *
     * @throws AboOrderException
     */
    public function createOrder($aboId)
    {
        $abo = $this->getAboById($aboId);
        if (!$abo) {
            throw new AboOrderException(
                sprintf('No abo for ID %s found', $aboId)
            );
        }

        $order = $this->getOrderById((int) $abo['last_order_id'], $abo['payment_id']);
        if (!$order) {
            throw new AboOrderException(
                sprintf('No order for abo ID %s found', $aboId)
            );
        }

        $shopId = (int) $order['language'];

        $this->aboCommerceService->registerShop($shopId, $order['currency']);
        $this->aboCommerceService->registerOrder($abo, $order);

        $this->prepareSession($aboId);

        if (empty($order['payment_action'])) {
            $action = 'aboFinish';
            $controller = 'checkout';
        } else {
            $controller = $order['payment_action'];
            $action = 'recurring';
        }

        $url = $this->getUrl($action, $controller, $abo['last_order_id']);

        return $this->handleCurl($url, $shopId);
    }

    /**
     * @return string
     */
    private function getDateNow()
    {
        $dateNow = new \DateTime();

        return $dateNow->format('Y-m-d H:i:s');
    }

    /**
     * @param string $url
     * @param int    $shopId
     *
     * @throws AboOrderException
     *
     * @return array
     */
    private function handleCurl($url, $shopId)
    {
        Enlight_Components_Session::writeClose();

        $curlHandle = curl_init();

        curl_setopt_array($curlHandle, [
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Shopware/' . $this->config->offsetGet('version'),
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
            CURLOPT_COOKIE => 'shop=' . $shopId,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
        ]);

        $result = curl_exec($curlHandle);
        $httpStatusCode = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        if (!empty($error = curl_error($curlHandle))) {
            $this->pluginLogger->addError($error);
        }

        curl_close($curlHandle);

        if ($result !== false && $httpStatusCode === 200) {
            return json_decode($result, true);
        }

        throw new AboOrderException(sprintf('Could not create order due to a HTTP request exception. URL: %s (Code: %s)', $url, $httpStatusCode));
    }

    /**
     * @param string $action
     * @param string $controller
     * @param int    $lastOrderId
     *
     * @return false|string
     */
    private function getUrl($action, $controller, $lastOrderId)
    {
        return $this->router->assemble(
            [
                'action' => $action,
                'controller' => $controller,
                'module' => 'frontend',
                'orderId' => $lastOrderId,
                'appendSession' => true,
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    private function getAboById($id)
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_plugin_swag_abo_commerce_orders')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $orderId
     * @param int $aboPaymentId
     *
     * @return mixed
     */
    private function getOrderById($orderId, $aboPaymentId)
    {
        return $this->connection->createQueryBuilder()
            ->select(['*', 'ord.id AS orderId', 'payment.id as paymentId', 'payment.action as payment_action', 'shop.id AS shopId'])
            ->from('s_order', 'ord')
            ->innerJoin('ord', 's_core_shops', 'shop', 'ord.language = shop.id AND shop.active = 1')
            ->leftJoin('ord', 's_core_paymentmeans', 'payment', 'payment.id = :aboPaymentId')
            ->where('ord.id = :id')
            ->setParameter('id', $orderId)
            ->setParameter('aboPaymentId', $aboPaymentId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $aboId
     */
    private function prepareSession($aboId)
    {
        $session = $this->dependencyProvider->getSession();
        $session->offsetSet('isRecurringAboOrder', true);
        $session->offsetSet('aboId', $aboId);
    }
}
