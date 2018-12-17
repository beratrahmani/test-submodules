<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Bridge;

use Shopware\B2B\Address\Framework\AddressEntity;
use Shopware\B2B\Address\Framework\ConfigServiceInterface;
use Shopware_Components_Config;

class ConfigService implements ConfigServiceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigService constructor.
     * @param Shopware_Components_Config $config
     */
    public function __construct(Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        $requiredFields = [
            'phone' => $this->config->get('requirePhoneField'),
            'addressAdditional1' => $this->config->get('requireAdditionAddressLine1'),
            'addressAdditional2' => $this->config->get('requireAdditionAddressLine2'),
        ];

        return $requiredFields;
    }

    /**
     * @param AddressEntity $address
     * @return array
     */
    public function getRequiredFieldsByAddress(AddressEntity $address): array
    {
        $requiredFields = $this->getRequiredFields();
        $fields = [];

        if ($requiredFields['phone']) {
            $fields['phone'] = $address->phone;
        }
        if ($requiredFields['addressAdditional1']) {
            $fields['addressAdditional1'] = $address->additional_address_line1;
        }
        if ($requiredFields['addressAdditional2']) {
            $fields['addressAdditional2'] = $address->additional_address_line2;
        }

        return $fields;
    }
}
