<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Framework;

/**
 * Assigns contingents to contacts M:N
 */
class ContingentGroupContactAssignmentService
{
    /**
     * @var ContingentContactRepository
     */
    private $contingentContactRepository;

    /**
     * @param ContingentContactRepository $contingentContactRepository
     */
    public function __construct(ContingentContactRepository $contingentContactRepository)
    {
        $this->contingentContactRepository = $contingentContactRepository;
    }

    /**
     * @param int $contingentGroupId
     * @param int $contactId
     * @throws MismatchingDataException
     */
    public function assign(int $contingentGroupId, int $contactId)
    {
        if ($this->contingentContactRepository->isContingentGroupContactDebtor($contingentGroupId, $contactId)) {
            throw new MismatchingDataException();
        }

        $this->contingentContactRepository->assignContingentContact($contingentGroupId, $contactId);
    }

    /**
     * @param int $contingentGroupId
     * @param int $contactId
     * @throws \Shopware\B2B\ContingentGroupContact\Framework\MismatchingDataException
     */
    public function removeAssignment(int $contingentGroupId, int $contactId)
    {
        $this->contingentContactRepository
            ->removeContingentContactAssignment($contingentGroupId, $contactId);
    }
}
