<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class UserPostalSettings
{
    /**
     * @var string
     */
    public $salutation;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var int
     */
    public $language;

    /**
     * @var string
     */
    public $email;

    /**
     * @param string $salutation
     * @param string|null $title
     * @param string $firstName
     * @param string $lastName
     * @param int $language
     * @param string $email
     */
    public function __construct(
        string $salutation,
        string $title = null,
        string $firstName,
        string $lastName,
        int $language,
        string $email
    ) {
        $this->salutation = $salutation;
        $this->title = $title;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->language = $language;
        $this->email = $email;
    }
}
