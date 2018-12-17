<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class UserLoginCredentials
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $encoder;

    /**
     * @var bool
     */
    public $active;

    /**
     * @param string $email
     * @param string $password
     * @param string $encoder
     * @param bool $active
     */
    public function __construct(
        string $email,
        string $password,
        string $encoder,
        bool $active
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->encoder = $encoder;
        $this->active = $active;
    }
}
