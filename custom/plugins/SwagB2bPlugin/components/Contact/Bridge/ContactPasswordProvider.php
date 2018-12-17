<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactPasswordProviderInterface;
use Shopware\Components\Password\Manager;

class ContactPasswordProvider implements ContactPasswordProviderInterface
{
    /**
     * @var Manager
     */
    private $passwordEncoder;

    /**
     * {@inheritdoc}
     */
    public function __construct(Manager $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword(ContactEntity $contact, $newPassword)
    {
        $contact->encoder = $this->passwordEncoder->getDefaultPasswordEncoderName();
        $contact->password = $this->passwordEncoder
            ->encodePassword($newPassword, $contact->encoder);
    }
}
