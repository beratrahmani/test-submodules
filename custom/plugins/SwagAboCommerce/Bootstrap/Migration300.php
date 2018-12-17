<?php

namespace SwagAboCommerce\Bootstrap;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Components\Logger;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class Migration300
{
    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param AddressServiceInterface $addressService
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        AddressServiceInterface $addressService,
        EntityManagerInterface $manager,
        Logger $logger
    ) {
        $this->addressService = $addressService;
        $this->manager = $manager;
        $this->logger = $logger;

        $this->connection = $manager->getConnection();
    }

    public function updateTo230()
    {
        $sql = <<<SQL
ALTER TABLE `s_plugin_swag_abo_commerce_orders`
ADD `payment_id` INT(11) UNSIGNED NULL,
ADD `billing_address_id` INT(11) UNSIGNED NULL,
ADD `shipping_address_id` INT(11) UNSIGNED NULL
SQL;

        $this->connection->exec($sql);

        $this->updateOpenSubscriptions();
    }

    private function updateOpenSubscriptions()
    {
        /** @var array $openSubscriptions */
        $openSubscriptions = $this->getOpenSubscriptions();

        foreach ($openSubscriptions as $subscriptionOrderId => $subscriptionOrderData) {
            try {
                $customer = $this->manager->find(Customer::class, $subscriptionOrderData['customerId']);

                if (!$customer) {
                    continue;
                }
                $billingAddressId = $this->findAddress($customer->getId(), $subscriptionOrderData, 'ba');
                $shippingAddressId = $this->findAddress($customer->getId(), $subscriptionOrderData, 'sa');

                if(!$billingAddressId) {
                    $billingAddressId = $this->createAddress($customer, $subscriptionOrderData, 'ba');
                }

                if (!$shippingAddressId) {
                    $shippingAddressId = $this->createAddress($customer, $subscriptionOrderData, 'sa');
                }

                $this->updateSubscriptionOrder(
                    (int) $subscriptionOrderId,
                    (int) $subscriptionOrderData['paymentId'],
                    $billingAddressId,
                    $shippingAddressId
                );
            } catch (\Exception $e) {
                $this->logger->addError($e->getMessage());
                // never break the plugin update
                continue;
            }
        }
    }

    /**
     * @return array
     */
    private function getOpenSubscriptions()
    {
        $dateNow = new \DateTime();
        $dateNow = $dateNow->format('Y-m-d H:i:s');

        return $this->connection->createQueryBuilder()
            ->select('aboOrder.id as aboOrderId, aboOrder.customer_id as customerId, o.paymentID as paymentId')
            ->addSelect('ba.company as baCompany, ba.department as baDepartment, ba.salutation as baSalutation, ba.title as baTitle')
            ->addSelect('ba.firstname as baFirstName, ba.lastname as baLastName, ba.street as baStreet, ba.zipcode as baZipCode')
            ->addSelect('ba.city as baCity, ba.countryID as baCountry, ba.stateID as baState, ba.ustid as baUstid, ba.phone as baPhone')
            ->addSelect('ba.additional_address_line1 as baAddOne, ba.additional_address_line2 as baAddTwo')
            ->addSelect('sa.company as saCompany, sa.department as saDepartment, sa.salutation as saSalutation, sa.title as saTitle')
            ->addSelect('sa.firstname as saFirstName, sa.lastname as saLastName, sa.street as saStreet, sa.zipcode as saZipCode')
            ->addSelect('sa.city as saCity, sa.countryID as saCountry, sa.stateID as saState, sa.phone as saPhone')
            ->addSelect('sa.additional_address_line1 as saAddOne, sa.additional_address_line2 as saAddTwo')
            ->from('s_plugin_swag_abo_commerce_orders', 'aboOrder')
            ->innerJoin('aboOrder', 's_order', 'o', 'o.id = aboOrder.order_id')
            ->leftJoin('o', 's_order_billingaddress', 'ba', 'o.id = ba.orderID')
            ->leftJoin('o', 's_order_shippingaddress', 'sa', 'o.id = sa.orderID')
            ->andWhere('aboOrder.due_date <= aboOrder.last_run')
            ->orWhere('aboOrder.endless_subscription = 1 AND aboOrder.last_run IS NULL')
            ->setParameter('dateNow', $dateNow)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param Customer $customer
     * @param array $subscriptionData
     * @param $alias
     *
     * @return int
     */
    private function createAddress(Customer $customer, array $subscriptionData, $alias)
    {
        $address = new Address();
        $idx = $alias . 'Country';

        if (isset($subscriptionData[$idx])) {
            $country = $this->manager->find(Country::class, $subscriptionData[$idx]);
        }
        $idx = $alias . 'State';
        if (isset($subscriptionData[$idx])) {
            $state = $this->manager->find(State::class, $subscriptionData[$idx]);
        }

        $data = [
            'company' => $subscriptionData[$alias . 'Company'],
            'department' => $subscriptionData[$alias . 'Department'],
            'salutation' => $subscriptionData[$alias . 'Salutation'],
            'title' => $subscriptionData[$alias . 'Title'],
            'firstname' => $subscriptionData[$alias . 'FirstName'],
            'lastname' => $subscriptionData[$alias . 'LastName'],
            'street' => $subscriptionData[$alias . 'Street'],
            'zipCode' => $subscriptionData[$alias . 'ZipCode'],
            'city' => $subscriptionData[$alias . 'City'],
            'phone' => $subscriptionData[$alias . 'Phone'],
            'country' => isset($country) ? $country : null,
            'state' => isset($state) ? $state : null,
            'ustd' => isset($subscriptionData[$alias . 'Ustid']) ? $subscriptionData[$alias . 'Ustid'] : null,
            'additionalAddressLine1' => $subscriptionData[$alias . 'AddOne'],
            'additionalAddressLine2' => $subscriptionData[$alias . 'AddTwo'],
        ];

        $address->fromArray($data);

        $this->addressService->create($address, $customer);

        return $address->getId();
    }

    /**
     * @param int $getId
     * @param array $subscriptionOrderData
     * @param string $alias
     *
     * @return int
     */
    private function findAddress($customerId, array $subscriptionOrderData, $alias)
    {
        return $this->connection->createQueryBuilder()
            ->select('address.id')
            ->from('s_user_addresses', 'address')
            ->where('address.user_id = :id')
            ->andWhere('address.firstname = :firstname')
            ->andWhere('address.lastname = :lastname')
            ->andWhere('address.street = :street')
            ->andWhere('address.zipcode = :zipcode')
            ->setParameter('id', $customerId)
            ->setParameter('firstname', $subscriptionOrderData[$alias . 'FirstName'])
            ->setParameter('lastname', $subscriptionOrderData[$alias . 'LastName'])
            ->setParameter('street', $subscriptionOrderData[$alias . 'Street'])
            ->setParameter('zipcode', $subscriptionOrderData[$alias . 'ZipCode'])
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param int   $subscriptionId
     * @param int   $paymentId
     * @param int   $billingAddressId
     * @param int   $shippingAddressId
     */
    private function updateSubscriptionOrder($subscriptionOrderId, $paymentId, $billingAddressId, $shippingAddressId)
    {
        $this->connection->createQueryBuilder()
            ->update('s_plugin_swag_abo_commerce_orders', 'subscriptionOrder')
            ->set('subscriptionOrder.payment_id', ':paymentId')
            ->set('subscriptionOrder.billing_address_id', ':billingAddressId')
            ->set('subscriptionOrder.shipping_address_id', ':shippingAddressId')
            ->where('subscriptionOrder.id = :id')
            ->setParameter('paymentId', $paymentId)
            ->setParameter('billingAddressId', $billingAddressId)
            ->setParameter('shippingAddressId', $shippingAddressId)
            ->setParameter('id', $subscriptionOrderId)
            ->execute();
    }
}