<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Address\Framework\CountryRepositoryInterface;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Translation;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CountryRepository implements CountryRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Shopware_Components_Translation
     */
    private $translator;

    /**
     * @param Connection $connection
     * @param Shopware_Components_Translation $translator
     * @param ContainerInterface $container
     */
    public function __construct(
        Connection $connection,
        Shopware_Components_Translation $translator,
        ContainerInterface $container
    ) {
        $this->connection = $connection;
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getCountryList(): array
    {
        $rawCountries = $this
            ->connection
            ->fetchAll(
                'SELECT id, countryname FROM s_core_countries WHERE active = :active ORDER BY position, countryname ASC',
                ['active' => 1]
            );

        $countries = [];
        foreach ($rawCountries as $country) {
            $countries[$country['id']] = $this->getTranslatedCountryName($country, $this->getCurrentShop());
        }

        return $countries;
    }

    /**
     * Important: Not part of the interface, and therefore internal for bridge usage
     *
     * @param int $countryId
     * @return int
     */
    public function fetchAreaIdForCountryId(int $countryId): int
    {
        return (int) $this
            ->connection
            ->fetchColumn(
                'SELECT areaID FROM s_core_countries WHERE id=:countryId',
                ['countryId' => $countryId]
            );
    }

    /**
     * @param array $country
     * @param Shop $shop
     * @return string
     */
    private function getTranslatedCountryName(array $country, Shop $shop): string
    {
        $countryTranslations = $this->translator
            ->readWithFallback(
                $shop->getId(),
                $this->extractFallbackId($shop),
                'config_countries'
            );

        $countryId = $country['id'];

        if (isset($countryTranslations[$countryId]['countryname'])) {
            $country['countryname'] = $countryTranslations[$countryId]['countryname'];
        }

        return $country['countryname'];
    }

    /**
     * @param Shop $shop
     * @return int
     */
    private function extractFallbackId(Shop $shop): int
    {
        $fallbackId = 0;

        if ($shop->getFallback()) {
            return $shop
                ->getFallback()
                ->getId();
        }

        return $fallbackId;
    }

    /**
     * @return Shop
     */
    private function getCurrentShop(): Shop
    {
        /** @var Shop $shop */
        return $this
            ->container
            ->get('shop');
    }
}
