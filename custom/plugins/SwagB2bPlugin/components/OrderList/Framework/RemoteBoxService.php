<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RemoteBoxService
{
    /**
     * @todo produces statefull service - refactor to stack
     * @var ValidationException[]
     */
    private $errors = [];

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OrderListService
     */
    private $orderListService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param CurrencyService $currencyService
     * @param OrderListService $orderListService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        CurrencyService $currencyService,
        OrderListService $orderListService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->currencyService = $currencyService;
        $this->orderListService = $orderListService;
        $this->orderNumberRepository = $orderNumberRepository;
    }

    /**
     * @param array $errors
     * @param ValidationException $exception
     */
    public function addError(ValidationException $exception)
    {
        $this->errors[] = $exception;
    }

    /**
     * @param array $products
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function createLineItemListFromProductsRequest(array $products, OwnershipContext $ownershipContext): LineItemList
    {
        $lineItemList = new LineItemList();

        $references = [];
        foreach ($products as $product) {
            try {
                $reference = $this->orderListService
                    ->createReferenceFromProductRequest($product);

                if ($reference->referenceNumber) {
                    $reference->referenceNumber = $this->orderNumberRepository->fetchOriginalOrderNumber($reference->referenceNumber, $ownershipContext);
                }

                $references[] = $reference;
            } catch (ValidationException $e) {
                $this->errors[] = $e;
            }
        }

        $lineItemList->references = $references;

        $lineItemList->currencyFactor = $this->currencyService
            ->createCurrencyContext()->currentCurrencyFactor;

        if (!count($lineItemList->references)) {
            throw new \InvalidArgumentException('no valid products found');
        }

        return $lineItemList;
    }

    /**
     * @return array $errors
     */
    public function getValidationResponse(): array
    {
        $errors = [];
        foreach ($this->errors as $error) {
            foreach ($error->getViolations() as $violation) {
                $errors[] = [
                    'property' => ucfirst($violation->getPropertyPath()),
                    'snippetKey' => str_replace(['}', '{', '%', '.', ' '], '', $violation->getMessageTemplate()),
                    'messageTemplate' => $violation->getMessageTemplate(),
                    'parameters' => $violation->getParameters(),
                ];
            };
        }

        return $errors;
    }
}
