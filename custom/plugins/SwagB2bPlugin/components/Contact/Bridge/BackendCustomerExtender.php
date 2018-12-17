<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use Doctrine\ORM\Query\Expr\Join;
use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;
use Shopware\Components\Model\QueryBuilder;

class BackendCustomerExtender implements SubscriberInterface
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authRepo;

    /**
     * @param ContactRepository $contactRepository
     * @param StoreFrontAuthenticationRepository $authRepo
     */
    public function __construct(ContactRepository $contactRepository, StoreFrontAuthenticationRepository $authRepo)
    {
        $this->contactRepository = $contactRepository;
        $this->authRepo = $authRepo;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_Controllers_Backend_CustomerQuickView::getListQuery::after' => 'extendCustomerQuickView',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return QueryBuilder
     */
    public function extendCustomerQuickView(\Enlight_Hook_HookArgs $args): QueryBuilder
    {
        /** @var QueryBuilder $query */
        $query = $args->getReturn();

        $query->leftJoin(
            ContactModel::class,
            'b2bContact',
            Join::WITH,
            'customer.email = b2bContact.email'
        )->andWhere('b2bContact.id IS NULL');

        return $args->getReturn();
    }
}
