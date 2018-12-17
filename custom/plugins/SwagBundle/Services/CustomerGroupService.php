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

namespace SwagBundle\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Group;
use Shopware\Models\Customer\Repository;
use SwagBundle\Services\Dependencies\ProviderInterface;

class CustomerGroupService implements CustomerGroupServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var Repository
     */
    private $customerGroupRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ProviderInterface $dependenciesProvider
     * @param ModelManager      $modelManager
     */
    public function __construct(ProviderInterface $dependenciesProvider, ModelManager $modelManager)
    {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->modelManager = $modelManager;

        $this->customerGroupRepository = $modelManager->getRepository(Group::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroup = null;
        if ($this->dependenciesProvider->hasShop()) {
            $session = $this->dependenciesProvider->getSession();
            $customerGroupData = $session->get('sUserGroupData');

            /* @var Group $customerGroup */
            //check if the customer logged in and get the customer group model for the logged in customer
            if (!empty($customerGroupData['groupkey'])) {
                $customerGroup = $this->customerGroupRepository->findOneBy([
                    'key' => $customerGroupData['groupkey'],
                ]);
            }
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Group) {
            $customerGroup = $this->dependenciesProvider->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function useNetPriceInBasket()
    {
        $customerGroup = $this->getCurrentCustomerGroup();
        if ($customerGroup->getTax() && !$this->isShippingCountryNet()) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to check if the selected country would be delivered with net prices.
     *
     * @return bool|int
     */
    private function isShippingCountryNet()
    {
        $session = $this->dependenciesProvider->getSession();
        $country = $session->get('sCountry');
        if (empty($country)) {
            return false;
        }

        /** @var Country $country */
        $country = $this->modelManager->find(Country::class, $country);

        if (!$country) {
            return false;
        }

        if ((bool) $country->getTaxFree()) {
            return true;
        }

        if ((bool) $country->getTaxFreeUstId()) {
            // Check if the logged in customer has a UStId
            $customerId = $session->get('sUserId');
            if (empty($customerId)) {
                return false;
            }
            $vatId = $this->modelManager->getDBALQueryBuilder()
                ->select('ustid')
                ->from('s_user_addresses', 'billingAddress')
                ->innerJoin(
                    'billingAddress',
                    's_user',
                    'customer',
                    'billingAddress.id = customer.default_billing_address_id'
                )
                ->where('customer.id = :customerId')
                ->setParameter('customerId', $customerId)
                ->execute()->fetchColumn();

            return !empty($vatId);
        }

        return false;
    }
}
