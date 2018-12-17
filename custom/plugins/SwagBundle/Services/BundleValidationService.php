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

use Shopware\Models\Customer\Group;
use SwagBundle\Models\Bundle;

class BundleValidationService implements BundleValidationServiceInterface
{
    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @param CustomerGroupServiceInterface $customerGroupService
     */
    public function __construct(CustomerGroupServiceInterface $customerGroupService)
    {
        $this->customerGroupService = $customerGroupService;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBundle(Bundle $bundle)
    {
        $customerGroup = $this->customerGroupService->getCurrentCustomerGroup();

        //check if the bundle is allowed for the current customer group
        if ($bundle->getCustomerGroups()->count() <= 0 || !$this->isCustomerGroupAllowed($bundle, $customerGroup)) {
            return [
                'success' => false,
                'bundle' => $bundle->getName(),
                'notForCustomerGroup' => true,
            ];
        }

        //check if the bundle is limited and the bundle has enough stock
        if ($bundle->getLimited() && $bundle->getQuantity() <= 0) {
            return [
                'success' => false,
                'bundle' => $bundle->getName(),
                'noStock' => true,
            ];
        }

        return true;
    }

    /**
     * Helper function to check if the passed customer group is allowed for the passed bundle.
     *
     * @param Bundle $bundle
     * @param Group  $customerGroup
     *
     * @return bool
     */
    private function isCustomerGroupAllowed($bundle, Group $customerGroup)
    {
        /** @var Group $customerGroupBundle */
        foreach ($bundle->getCustomerGroups() as $customerGroupBundle) {
            if ($customerGroup->getKey() === $customerGroupBundle->getKey()) {
                return true;
            }
        }

        return false;
    }
}
