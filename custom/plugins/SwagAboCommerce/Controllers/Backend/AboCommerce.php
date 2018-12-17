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
use Shopware\Models\Article\Article as CoreProduct;
use Shopware\Models\Customer\Group;
use Shopware\Models\Payment\Payment;
use SwagAboCommerce\Models\Order;
use SwagAboCommerce\Models\Product;
use SwagAboCommerce\Models\Settings;
use SwagAboCommerce\Services\AboOrderException;
use SwagAboCommerce\Services\OrderCronServiceInterface;

class Shopware_Controllers_Backend_AboCommerce extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Save settings action
     *
     * @throws RuntimeException
     */
    public function saveSettingsAction()
    {
        $data = $this->Request()->getPost();
        $payments = $this->Request()->getPost('payments');
        unset($data['payments']);

        // Search for paypal
        $isPayPal = false;
        foreach ($payments as $payment) {
            if ('paypal' === $payment['name']) {
                $isPayPal = true;
                break;
            }
        }

        if ($isPayPal) {
            try {
                $config = $this->container->get('plugins')->Frontend()->SwagPaymentPaypal()->Config();
            } catch (Enlight_Exception $e) {
                $config = false;
            }

            if (!$config) {
                return;
            }

            $billingAgreement = $config->paypalBillingAgreement;
            if (!$billingAgreement) {
                throw new RuntimeException('In order to use PayPal with AboCommerce, you need to enable PayPal BillingAgreement in the configuration of PayPal.');
            }
        }

        $settings = $this->getAboCommerceRepository()
            ->getAboCommerceSettingsQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        /** @var $settings Settings */
        if (!$settings) {
            $settings = new Settings();
            $this->getEntityManager()->persist($settings);
        }

        $settings->fromArray($data);

        $settings->getPayments()->clear();
        if (!empty($payments)) {
            foreach ($payments as $paymentMethod) {
                if (empty($paymentMethod['id'])) {
                    continue;
                }
                $paymentModel = $this->getPaymentRepository()->find($paymentMethod['id']);
                if ($paymentModel instanceof Payment) {
                    $settings->addPayment($paymentModel);
                }
            }
        }

        $this->getEntityManager()->flush();

        $this->View()->assign($this->getSettings());
    }

    /**
     * Get settings action
     */
    public function getSettingsAction()
    {
        $this->View()->assign($this->getSettings());
    }

    /**
     * Global interface to create a new AboCommerce.
     */
    public function createAboCommerceAction()
    {
        $this->View()->assign(
            $this->saveAboCommerce(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Global interface to update an existing AboCommerce record.
     */
    public function updateAboCommerceAction()
    {
        $this->View()->assign(
            $this->saveAboCommerce(
                $this->Request()->getParam('articleId'),
                $this->Request()->getPost()
            )
        );
    }

    /**
     * Global interface to remove an existing AboCommerce record.
     */
    public function removeAboCommerceAction()
    {
        $aboId = $this->Request()->getPost('id');

        $aboProduct = $this->getAboCommerceOrderRepository()->find($aboId);

        $this->getEntityManager()->remove($aboProduct);
        $this->getEntityManager()->flush();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Global interface to get the whole data for a single AboCommerce record.
     */
    public function getDetailAction()
    {
        $productId = $this->Request()->getParam('articleId');
        $this->View()->assign($this->getDetail($productId));
    }

    /**
     * Get order action
     */
    public function getOrdersAction()
    {
        $filterDue = (bool) $this->Request()->getParam('filterDue', false);
        $excludeExpired = (bool) $this->Request()->getParam('exludeExpired', false);

        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);

        $search = $this->Request()->getParam('search');

        $filter = $this->prefixProperties($this->Request()->getParam('filter', []));
        $order = $this->prefixProperties($this->Request()->getParam('sort', []));

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['aboOrders', 'articleOrderDetail', 'orders', 'customer', 'customerGroup'])
            ->from(Order::class, 'aboOrders')
            ->innerJoin('aboOrders.order', 'orders')
            ->innerJoin('aboOrders.articleOrderDetail', 'articleOrderDetail')
            ->leftJoin('aboOrders.customer', 'customer')
            ->innerJoin('customer.group', 'customerGroup')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!empty($search)) {
            $builder->andWhere(
                'orders.number LIKE :search OR ' .
                'articleOrderDetail.articleNumber LIKE :search OR ' .
                'customer.email LIKE :search OR ' .
                'articleOrderDetail.articleName LIKE :search OR ' .
                'customerGroup.name LIKE :search'
            )
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if ($excludeExpired) {
            $builder->andWhere('aboOrders.dueDate <= aboOrders.lastRun');
        }

        if (empty($order)) {
            $builder->orderBy('aboOrders.dueDate');
        } else {
            $builder->addOrderBy($order);
        }

        if ($filterDue) {
            $now = new \DateTime();
            $builder->andWhere('aboOrders.dueDate <= :dateNow');
            $builder->setParameter('dateNow', $now);
        }

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        $paginator = $this->container->get('models')->createPaginator($query);
        //returns the total count of the query
        $total = $paginator->count();

        $results = [];
        /** @var $record Order */
        foreach ($paginator as $record) {
            $result = $record->toArray();

            $detailOrder = $record->getArticleOrderDetail();

            $result['articleName'] = $detailOrder->getArticleName();
            $result['articleId'] = $detailOrder->getArticleId();

            $customer = $record->getCustomer();
            $result['customerMail'] = $customer->getEmail();
            $result['customerGroup'] = $customer->getGroup()->getName();

            $results[] = $result;
        }

        $this->view->assign([
            'success' => true,
            'total' => $total,
            'data' => $results,
        ]);
    }

    /**
     * create order action
     */
    public function createOrderAction()
    {
        $aboId = (int) $this->Request()->getParam('aboId');

        $this->createOrder($aboId);
    }

    public function getArticlesAction()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'aboProduct.id as id',
            'aboProduct.active as isActive',
            'aboProduct.exclusive as isExclusive',
            'product.id as articleId',
            'product.name as articleName',
            'detail.number as articleNumber',
        ]);

        $builder->from(Product::class, 'aboProduct')
            ->innerJoin('aboProduct.article', 'product')
            ->innerJoin('product.mainDetail', 'detail');

        $query = $builder->getQuery();

        $paginator = $this->container->get('models')->createPaginator($query);
        $products = $paginator->getIterator()->getArrayCopy();
        $total = $paginator->count();

        $this->view->assign([
            'success' => true,
            'total' => $total,
            'data' => $products,
        ]);
    }

    public function terminateSubscriptionAction()
    {
        if ($this->Request()->getMethod() !== 'POST') {
            $this->View()->assign(['success' => false]);

            return;
        }

        $orderId = (int) $this->request->get('orderId');

        if ($orderId === 0) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $manager = $this->container->get('models');
        /** @var Order $aboOrder */
        $aboOrder = $this->getModelManager()->find(Order::class, $orderId);

        if (!$aboOrder) {
            $this->View()->assign(['success' => false]);

            return;
        }
        $aboData = $manager->getRepository(Order::class)->getAboWithUserData($orderId);

        if ($aboOrder->getDirectTermination() === true) {
            $endDate = new \DateTime();
            $aboOrder->setLastRun($endDate);
        } else {
            $endDate = $aboOrder->incrementDate(new \DateTime(), $aboOrder->getPeriodOfNoticeInterval(), $aboOrder->getPeriodOfNoticeUnit());
            $aboOrder->setLastRun($endDate);
        }
        $now = new \DateTime();
        $aboOrder->setTerminationDate($now);

        $manager->flush($aboOrder);

        $context = [
            'aboData' => $aboData,
            'dueDate' => $endDate,
        ];

        $mailSent = true;
        try {
            $mail = $this->container->get('templatemail')->createMail('sABOCOMMERCETERMINATION', $context);
            $mail->addTo($aboOrder->getCustomer()->getEmail());
            $mail->send();
        } catch (\Exception $e) {
            $mailSent = false;
        }

        $this->View()->assign(['success' => true, 'mailSent' => $mailSent]);
    }

    /**
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getEntityManager()
    {
        return $this->getModelManager();
    }

    /**
     * @return mixed|\SwagAboCommerce\Services\AboCommerceServiceInterface
     */
    protected function getAboCommerceComponent()
    {
        return $this->container->get('swag_abo_commerce.abo_commerce_service');
    }

    /**
     * Internal function to get the whole data for a single AboCommerce record.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getDetail($id)
    {
        /** @var $product CoreProduct */
        $product = $this->getProductRepository()->find($id);

        $aboProduct = $this->getAboCommerceRepository()
            ->getDetailQuery($id)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (empty($aboProduct)) {
            return [
                'success' => true,
            ];
        }

        $tax = $product->getTax();
        foreach ($aboProduct['prices'] as &$price) {
            $price['discountAbsoluteNet'] = $price['discountAbsolute'];

            if (empty($price['discountAbsolute'])) {
                continue;
            }

            /* @var $customerGroup Group */
            $customerGroup = $this->getCustomerGroupRepository()->find($price['customerGroupId']);

            if (!$customerGroup->getTaxInput()) {
                continue;
            }

            $price['discountAbsolute'] = $price['discountAbsolute'] / 100 * (100 + $tax->getTax());
        }

        return [
            'success' => true,
            'data' => $aboProduct,
        ];
    }

    /**
     * Helper method to prefix properties
     *
     * @param array $properties
     *
     * @return array
     */
    protected function prefixProperties(array $properties = [])
    {
        $mappingArray = [
            'articleName' => [
                'aliasName' => 'articleOrderDetail',
                'fieldName' => 'articleName',
            ],
            'customerMail' => [
                'aliasName' => 'customer',
                'fieldName' => 'email',
            ],
            'customerGroup' => [
                'aliasName' => 'customerGroup',
                'fieldName' => 'name',
            ],
        ];

        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                if (array_key_exists($property['property'], $mappingArray)) {
                    $properties[$key]['property'] = $mappingArray[$property['property']]['aliasName'] . '.' . $mappingArray[$property['property']]['fieldName'];
                } else {
                    $properties[$key]['property'] = 'aboOrders.' . $property['property'];
                }
            }
        }

        return $properties;
    }

    /**
     * @return array
     */
    private function getSettings()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['settings', 'payments'])
            ->from(Settings::class, 'settings')
            ->leftJoin('settings.payments', 'payments');

        $data = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!$data) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'data' => $data,
            'count' => 1,
        ];
    }

    /**
     * @param int   $productId
     * @param array $params
     */
    private function saveAboCommerce($productId, array $params)
    {
        $manager = $this->getEntityManager();
        $aboProduct = $this->getAboCommerceRepository()
            ->getDetailQuery($productId)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        /* @var $product CoreProduct */
        $product = $this->getProductRepository()->find($productId);

        if (empty($aboProduct)) {
            $aboProduct = new Product();
            $aboProduct->setArticle($product);
            $manager->persist($aboProduct);
        }

        $tax = $product->getTax();

        foreach ($params['prices'] as &$price) {
            if (empty($price['discountAbsolute'])) {
                continue;
            }

            /* @var $customerGroup Group */
            $customerGroup = $this->getCustomerGroupRepository()->find($price['customerGroupId']);

            if (!$customerGroup->getTaxInput()) {
                continue;
            }

            $price['discountAbsolute'] = $price['discountAbsolute'] / (100 + $tax->getTax()) * 100;
        }
        unset($price);

        if (!$params['deliveryIntervalUnit']) {
            $params['deliveryIntervalUnit'] = $params['durationUnit'];
        }

        $aboProduct->fromArray($params);
        $manager->flush();

        $this->View()->assign($this->getDetail($productId));
    }

    /**
     * @param int $aboId
     *
     * @throws RuntimeException
     */
    private function createOrder($aboId)
    {
        /** @var OrderCronServiceInterface $aboOrderService */
        $aboOrderService = $this->container->get('swag_abo_commerce.order_cron_job_service');
        $result = $aboOrderService->createOrder($aboId);
        $this->View()->assign($result);
    }

    /**
     * @return \SwagAboCommerce\Models\Repository
     */
    private function getAboCommerceRepository()
    {
        return $this->getEntityManager()->getRepository(Product::class);
    }

    /**
     * @return \Shopware\Components\Model\ModelRepository
     */
    private function getAboCommerceOrderRepository()
    {
        return $this->getEntityManager()->getRepository(Order::class);
    }

    /**
     * @return \Shopware\Models\Article\Repository
     */
    private function getProductRepository()
    {
        return $this->getEntityManager()->getRepository(CoreProduct::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getCustomerGroupRepository()
    {
        return $this->getEntityManager()->getRepository(Group::class);
    }

    /**
     * @return \Shopware\Components\Model\ModelRepository
     */
    private function getPaymentRepository()
    {
        return $this->getEntityManager()->getRepository(Payment::class);
    }
}
