<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationEntity;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationRepositoryInterface;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationServiceInterface;
use Shopware\Components\Random;
use Shopware\Components\Routing\Router;
use Shopware_Components_TemplateMail;

class ContactPasswordActivationService implements ContactPasswordActivationServiceInterface
{
    /**
     * @var Shopware_Components_TemplateMail
     */
    private $templateMail;

    /**
     * @var ContactPasswordActivationRepositoryInterface
     */
    private $contactPasswordActivationRepository;

    /**
     * @var Router
     */
    private $shopwareRouter;

    /**
     * @param Shopware_Components_TemplateMail $templateMail
     * @param ContactPasswordActivationRepositoryInterface $contactPasswordActivationRepository
     * @param Router $shopwareRouter
     */
    public function __construct(
        Shopware_Components_TemplateMail $templateMail,
        ContactPasswordActivationRepositoryInterface $contactPasswordActivationRepository,
        Router $shopwareRouter
    ) {
        $this->templateMail = $templateMail;
        $this->contactPasswordActivationRepository = $contactPasswordActivationRepository;
        $this->shopwareRouter = $shopwareRouter;
    }

    /**
     * @param ContactEntity $contact
     */
    public function sendPasswordActivationEmail(ContactEntity $contact)
    {
        $params = [];
        $passwordActivationEntity = $this->createPasswordActivationEntity($contact);

        $params['passwordActivation'] = $passwordActivationEntity->toArray();
        $params['passwordActivation']['email'] = $contact->email;
        $params['passwordActivation']['url'] = $this->shopwareRouter
            ->assemble([
                    'module' => 'frontend',
                    'controller' => 'b2bcontactpasswordactivation',
                    'hash' => $passwordActivationEntity->hash,
                ]);

        // @TODO remove @ after ticket SW-19218 resolved
        @$email = $this->templateMail
            ->createMail('b2bPasswordActivation', $params);

        $email->addTo($contact->email);
        $email->send();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidActivationByHash(string $hash)
    {
        try {
            $activation = $this->contactPasswordActivationRepository->fetchOneByHash($hash);
        } catch (NotFoundException $e) {
            return;
        }

        if (!$activation->isValid()) {
            return;
        }

        return $activation;
    }

    /**
     * @param ContactPasswordActivationEntity $activation
     * @return ContactPasswordActivationEntity
     */
    public function removeActivation(ContactPasswordActivationEntity $activation): ContactPasswordActivationEntity
    {
        return $this->contactPasswordActivationRepository->removeActivation($activation);
    }

    /**
     * @param ContactEntity $contact
     * @return ContactPasswordActivationEntity
     */
    protected function createPasswordActivationEntity(ContactEntity $contact): ContactPasswordActivationEntity
    {
        $activationEntity = new ContactPasswordActivationEntity();
        $activationEntity->type = ContactPasswordActivationEntity::class;
        $activationEntity->hash = Random::getAlphanumericString(32);
        $activationEntity->date = new \DateTime();
        $activationEntity->data = $contact;

        return $this->contactPasswordActivationRepository
            ->addContactActivation($activationEntity);
    }
}
