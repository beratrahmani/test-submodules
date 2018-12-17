<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware_Components_Snippet_Manager;

class BasketOfferRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TaxProvider
     */
    private $taxProvider;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param Connection $connection
     * @param TaxProvider $taxProvider
     * @param Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        Connection $connection,
        TaxProvider $taxProvider,
        Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->connection = $connection;
        $this->taxProvider = $taxProvider;
        $this->snippetManager = $snippetManager;
    }

    /**
     * @param OfferEntity $offerEntity
     * @param OwnershipContext $ownershipContext
     */
    public function addDiscountToBasket(OfferEntity $offerEntity, OwnershipContext $ownershipContext)
    {
        if (!$offerEntity->discountValueNet) {
            return;
        }

        $discountTax = $this->taxProvider->getDiscountTax($offerEntity->listId, $ownershipContext);

        $this->connection->delete(
            's_order_basket',
            [
                'sessionID' => Shopware()->Container()->get('session')->get('sessionId'),
                'ordernumber' => OfferEntity::DISCOUNT_REFERENCE,
            ]
        );

        $this->connection->insert('s_order_basket', [
            'sessionID' => Shopware()->Container()->get('session')->get('sessionId'),
            'userID' => $offerEntity->authId,
            'articlename' => $this->snippetManager
                ->getNamespace('frontend/plugins/b2b_debtor_plugin')
                ->get('DiscountBasket'),
            'articleID' => 0,
            'ordernumber' => OfferEntity::DISCOUNT_REFERENCE,
            'shippingfree' => 0,
            'quantity' => 1,
            'price' => -($offerEntity->discountValueNet * $discountTax),
            'netprice' => -$offerEntity->discountValueNet,
            'tax_rate' => $discountTax,
            'datum' => (new \DateTime())->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
            'modus' => 4,
            'esdarticle' => 0,
            'partnerID' => 0,
            'lastviewport' => 'checkout',
            'useragent' => '',
            'currencyFactor' => $offerEntity->getCurrencyFactor(),
        ]);
    }

    /**
     * @param int $id
     * @return array
     */
    public function fetchArticleById(int $id): array
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_order_basket')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch();
    }
}
