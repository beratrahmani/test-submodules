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

namespace SwagLiveShopping\Components;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;

class PriceService implements PriceServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @param Connection              $connection
     * @param ContextServiceInterface $contextService
     */
    public function __construct(
        Connection $connection,
        ContextServiceInterface $contextService
    ) {
        $this->connection = $connection;
        $this->contextService = $contextService;
    }

    /**
     * @param int       $liveShoppingId
     * @param int       $liveShoppingType
     * @param \DateTime $buyTime
     * @param \DateTime $liveShoppingValidFrom
     * @param \DateTime $liveShoppingValidTo
     *
     * @throws NoLiveShoppingPriceException
     *
     * @return float
     */
    public function getLiveShoppingPrice(
        $liveShoppingId,
        $liveShoppingType,
        \DateTime $buyTime,
        \DateTime $liveShoppingValidFrom,
        \DateTime $liveShoppingValidTo
    ) {
        $customerGroup = $this->contextService->getShopContext()->getCurrentCustomerGroup();
        $priceData = $this->getPriceData($liveShoppingId, $customerGroup->getId());

        /* @var Group $customerGroup */
        if (!$priceData) {
            throw new NoLiveShoppingPriceException(
                sprintf(
                    'There is no Price for customer group %s with id %s and LiveShoppingId %s',
                    $customerGroup->getKey(),
                    $customerGroup->getId(),
                    $liveShoppingId
                )
            );
        }

        $price = $this->calculatePriceByLiveShoppingType(
            $priceData,
            $liveShoppingType,
            $buyTime,
            $liveShoppingValidFrom,
            $liveShoppingValidTo
        );

        return $this->getGrossPrice($price, $priceData['tax_rate'], $customerGroup);
    }

    /**
     * @param array     $priceData
     * @param int       $liveShoppingType
     * @param \DateTime $liveShoppingValidFrom
     * @param \DateTime $liveShoppingValidTo
     * @param \DateTime $buyTime
     *
     * @return mixed
     */
    private function calculatePriceByLiveShoppingType(
        array $priceData,
        $liveShoppingType,
        \DateTime $buyTime,
        \DateTime $liveShoppingValidFrom,
        \DateTime $liveShoppingValidTo
    ) {
        if ($liveShoppingType === LiveShoppingInterface::NORMAL_TYPE) {
            return $priceData['endprice'];
        }

        $expiredAmount = $this->getExpiredAmount($priceData, $buyTime, $liveShoppingValidFrom, $liveShoppingValidTo);

        if ($liveShoppingType === LiveShoppingInterface::DISCOUNT_TYPE) {
            return $priceData['price'] - abs($expiredAmount);
        }

        if ($liveShoppingType === LiveShoppingInterface::SURCHARGE_TYPE) {
            return $priceData['price'] + abs($expiredAmount);
        }

        throw new LiveShoppingTypeNotSupportedException(
            sprintf(
                'The LiveShopping type with the key "%s" is not Supported',
                $liveShoppingType
            )
        );
    }

    /**
     * @param int $liveShoppingId
     * @param int $customerGroupId
     *
     * @return array
     */
    private function getPriceData($liveShoppingId, $customerGroupId)
    {
        $price = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_articles_live_prices', 'livePrices')
            ->where('live_shopping_id = :liveShoppingId')
            ->andWhere('customer_group_id = :customerGroupId')
            ->setParameters([
                'liveShoppingId' => $liveShoppingId,
                'customerGroupId' => $customerGroupId,
            ])
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        $price['tax_rate'] = $this->getTaxRate($liveShoppingId);

        $isTaxInput = $this->getIsTaxInput($customerGroupId);

        if ($isTaxInput) {
            $price['price'] = $price['price'] / 100 * (100 + $price['tax_rate']);
            $price['endprice'] = $price['endprice'] / 100 * (100 + $price['tax_rate']);
        }

        return $price;
    }

    /**
     * @param int $liveShoppingId
     *
     * @return string
     */
    private function getTaxRate($liveShoppingId)
    {
        $taxRate = (float) $this->connection->createQueryBuilder()
            ->select('tax.tax')
            ->from('s_articles_lives', 'live')
            ->join('live', 's_articles', 'articles', 'live.article_id = articles.id')
            ->join('articles', 's_core_tax', 'tax', 'articles.taxID = tax.id')
            ->where('live.id = :liveShoppingId')
            ->setParameter('liveShoppingId', $liveShoppingId)
            ->execute()
            ->fetchColumn();

        if (!$taxRate) {
            throw new NoAssociatedTaxRate(
                sprintf('There is no associated tax rate. LiveShoppingId: %s',
                    $liveShoppingId
                )
            );
        }

        return $taxRate;
    }

    /**
     * @param int $customerGroupId
     *
     * @return int
     */
    private function getIsTaxInput($customerGroupId)
    {
        return (int) $this->connection->createQueryBuilder()
            ->select('taxinput')
            ->from('s_core_customergroups')
            ->where('id = :customerGroupId')
            ->setParameter('customerGroupId', $customerGroupId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param array $priceData
     * @param int   $minutes
     *
     * @return float
     */
    private function getPricePerMinute(array $priceData, $minutes)
    {
        return (float) ($priceData['endprice'] - $priceData['price']) / $minutes;
    }

    /**
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     *
     * @return bool|\DateInterval
     */
    private function getTimeDifference(\DateTime $startTime, \DateTime $endTime)
    {
        return $startTime->diff($endTime);
    }

    /**
     * @param \DateInterval $timeDifference
     *
     * @return int
     */
    private function getExpiredMinutes(\DateInterval $timeDifference)
    {
        $daysInMinutes = $timeDifference->d * PriceServiceInterface::MINUTES_A_DAY;
        $hoursInMinutes = $timeDifference->h * PriceServiceInterface::MINUTES_IN_HOUR;

        return $daysInMinutes + $hoursInMinutes + $timeDifference->i;
    }

    /**
     * @param array     $priceData
     * @param \DateTime $liveShoppingValidFrom
     * @param \DateTime $liveShoppingValidTo
     * @param \DateTime $buyTime
     *
     * @return float|int
     */
    private function getExpiredAmount(
        array $priceData,
        \DateTime $buyTime,
        \DateTime $liveShoppingValidFrom,
        \DateTime $liveShoppingValidTo
    ) {
        $liveShoppingValidDifference = $this->getTimeDifference($liveShoppingValidFrom, $liveShoppingValidTo);
        $validLiveShoppingPeriodInMinutes = $this->getExpiredMinutes($liveShoppingValidDifference);
        $pricePerMinute = $this->getPricePerMinute($priceData, $validLiveShoppingPeriodInMinutes);

        $buyTimeDifference = $this->getTimeDifference($liveShoppingValidFrom, $buyTime);
        $expiredMinutes = $this->getExpiredMinutes($buyTimeDifference);

        return $pricePerMinute * $expiredMinutes;
    }

    /**
     * @param float $price
     * @param float $taxRate
     * @param Group $customerGroup
     *
     * @return float
     */
    private function getGrossPrice($price, $taxRate, Group $customerGroup)
    {
        if ($customerGroup->insertedGrossPrices()) {
            if ($customerGroup->displayGrossPrices()) {
                return $price;
            }

            return $price / (100 + $taxRate) * 100;
        }

        if ($customerGroup->displayGrossPrices()) {
            return $price / 100 * (100 + $taxRate);
        }

        return $price;
    }
}
