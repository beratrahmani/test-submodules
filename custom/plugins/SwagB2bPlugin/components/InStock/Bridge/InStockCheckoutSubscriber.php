<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as ShopSession;
use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\InStock\Framework\InStockEntity;
use Shopware\B2B\InStock\Framework\InStockHelper;
use Shopware\B2B\InStock\Framework\InStockRepository;
use Shopware\B2B\InStock\Framework\InStockSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Components\Model\ModelManager;

class InStockCheckoutSubscriber implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var InStockRepository
     */
    private $inStockRepository;

    /**
     * @var InStockHelper
     */
    private $inStockHelper;

    /**
     * @var InStockBridgeRepository
     */
    private $bridgeRepository;

    /**
     * @var ShopSession
     */
    private $shopSession;

    /**
     * @param ModelManager $modelManager
     * @param AuthenticationService $authenticationService
     * @param InStockRepository $inStockRepository
     * @param InStockHelper $inStockHelper
     * @param InStockBridgeRepository $bridgeRepository
     * @param ShopSession $shopSession
     */
    public function __construct(
        ModelManager $modelManager,
        AuthenticationService $authenticationService,
        InStockRepository $inStockRepository,
        InStockHelper $inStockHelper,
        InStockBridgeRepository $bridgeRepository,
        ShopSession $shopSession
    ) {
        $this->authenticationService = $authenticationService;
        $this->modelManager = $modelManager;
        $this->inStockRepository = $inStockRepository;
        $this->inStockHelper = $inStockHelper;
        $this->bridgeRepository = $bridgeRepository;
        $this->shopSession = $shopSession;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Checkout::getAvailableStock::after' => 'afterGetAvailableStock',
            'Shopware_Modules_Basket_getArticleForAddArticle_FilterArticle' => 'onFilterForAddArticle',
            'Shopware_Modules_Basket_GetBasket_FilterItemStart' => 'onFilterItemStart',
            'sBasket::sCheckBasketQuantities::after' => 'afterCheckBasketQuantities',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return array
     */
    public function afterCheckBasketQuantities(\Enlight_Hook_HookArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return $args->getReturn();
        }

        $result = $this->bridgeRepository->getCheckBasketQuantitiesData((string) $this->shopSession->get('sessionId'));
        $hideBasket = false;
        $articles = [];
        foreach ($result as $article) {
            $inStocks = $this->getInStocks((int) $article['detailId']);

            $article['diffStock'] = ($article['instock'] - $article['quantity']);
            if (count($inStocks) !== 0) {
                $article['diffStock'] = ($inStocks[$article['detailId']]->inStock - $article['quantity']);
            }

            if (empty($article['active'])
                || (!empty($article['laststock']) && $article['diffStock'] < 0)
            ) {
                $hideBasket = true;
                $articles[$article['ordernumber']]['OutOfStock'] = true;
            } else {
                $articles[$article['ordernumber']]['OutOfStock'] = false;
            }
        }

        $return = ['hideBasket' => $hideBasket, 'articles' => $articles];
        $args->setReturn($return);

        return $return;
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return array
     */
    public function onFilterItemStart(\Enlight_Event_EventArgs $args)
    {
        $return = $args->getReturn();

        if (!$this->authenticationService->isB2b()) {
            return $return;
        }

        $detailId = (int) $return['articleDetailId'];

        $inStocks = $this->getInStocks($detailId);

        if (count($inStocks) === 0) {
            return $return;
        }

        $return['instock'] = $inStocks[$detailId]->inStock;

        $args->setReturn($return);

        return $return;
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return array
     */
    public function onFilterForAddArticle(\Enlight_Event_EventArgs $args)
    {
        $return = $args->getReturn();

        if (!$this->authenticationService->isB2b()) {
            return $return;
        }

        $detailId = (int) $return['articledetailsID'];

        $inStocks = $this->getInStocks($detailId);

        if (count($inStocks) === 0) {
            return $return;
        }

        $return['instock'] = $inStocks[$detailId]->inStock;

        $args->setReturn($return);

        return $return;
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return mixed
     */
    public function afterGetAvailableStock(\Enlight_Hook_HookArgs $args)
    {
        $return = $args->getReturn();

        if (!$this->authenticationService->isB2b()) {
            return $return;
        }

        $orderNumber = $return['ordernumber'];

        $detailRepo = $this->modelManager->getRepository('Shopware\Models\Article\Detail');

        $detail = $detailRepo->findOneBy(['number' => $orderNumber]);

        if (!$detail) {
            return $return;
        }

        $searchStruct = new InStockSearchStruct();

        $searchStruct->filters = [new EqualsFilter(
            $this->inStockRepository::TABLE_ALIAS,
            'articles_details_id',
            $detail->getId()
        )];

        $inStocks = $this->inStockHelper->getCascadedInStocksForAuthId(
            $this->authenticationService->getIdentity(),
            $searchStruct
        );

        if (count($inStocks) === 0) {
            return $return;
        }

        $inStock = $inStocks[$detail->getId()]->inStock;

        $return['instock'] = $inStock <= 0 ? 0 : $inStock;

        $args->setReturn($return);

        return $return;
    }

    /**
     * @internal
     * @param int $detailId
     * @return InStockEntity[]
     */
    protected function getInStocks(int $detailId)
    {
        $searchStruct = new InStockSearchStruct();

        $searchStruct->filters = [new EqualsFilter(
            $this->inStockRepository::TABLE_ALIAS,
            'articles_details_id',
            $detailId
        )];

        return $this->inStockHelper->getCascadedInStocksForAuthId(
            $this->authenticationService->getIdentity(),
            $searchStruct
        );
    }
}
