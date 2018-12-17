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

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Repository;
use SwagBundle\Services\Calculation\Validation\BundleLastStockValidatorInterface;

class BundleAvailableService implements BundleAvailableServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var BundleLastStockValidatorInterface
     */
    private $bundleLastStockValidator;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Repository
     */
    private $bundleRepository;

    /**
     * @param Connection                        $connection
     * @param BundleLastStockValidatorInterface $bundleLastStockValidator
     * @param ModelManager                      $modelManager
     */
    public function __construct(
        Connection $connection,
        BundleLastStockValidatorInterface $bundleLastStockValidator,
        ModelManager $modelManager
    ) {
        $this->connection = $connection;
        $this->bundleLastStockValidator = $bundleLastStockValidator;
        $this->modelManager = $modelManager;
        $this->bundleRepository = $modelManager->getRepository(Bundle::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isBundleAvailable($mainProductId, $bundleId, $orderNumber)
    {
        if ($this->isBundleMainProduct($mainProductId, $orderNumber)) {
            $limitedVariants = $this->bundleRepository->getLimitedDetails($bundleId);
            if ($this->bundleIsNotLimitedToVariant($orderNumber, $limitedVariants)) {
                return false;
            }
        }

        $detailRepository = $this->modelManager->getRepository(Detail::class);
        $detail = $detailRepository->findOneBy(['number' => $orderNumber]);

        $bundle = $this->modelManager->find(Bundle::class, $bundleId);

        if ($detail === null || $bundle === null) {
            return false;
        }

        if (!$this->bundleLastStockValidator->validate($detail, $bundle)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the product is the main product in this bundle
     *
     * @param int $mainProductId
     * @param int $orderNumber
     *
     * @return bool
     */
    private function isBundleMainProduct($mainProductId, $orderNumber)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $productId = (int) $queryBuilder->select('articleID')
            ->from('s_articles_details')
            ->where('ordernumber = :ordernumber')
            ->setParameter('ordernumber', $orderNumber)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return $productId === $mainProductId;
    }

    /**
     * @param string $orderNumber
     * @param array  $limitedVariants
     *
     * @return bool
     */
    private function bundleIsNotLimitedToVariant($orderNumber, array $limitedVariants)
    {
        if (count($limitedVariants) > 0) {
            if (!in_array($orderNumber, array_column($limitedVariants, 'ordernumber'), false)) {
                return true;
            }
        }

        return false;
    }
}
