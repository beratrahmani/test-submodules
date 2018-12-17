<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Shopware\B2B\Cart\Framework\CartStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CartState implements CartStateInterface
{
    const CART_STATE_OFFSET = 'CartState';

    const CART_STATE_OLD_OFFSET = 'OldCartState';

    const CART_STATE_ID_OFFSET = 'CartStateId';

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if ($container->has('shop')) {
            $this->session = $container->get('session');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setState(string $state)
    {
        $this->session->offsetSet(self::CART_STATE_OLD_OFFSET, $this->session->offsetGet(self::CART_STATE_OFFSET));
        $this->session->offsetSet(self::CART_STATE_OFFSET, $state);
    }

    /**
     * @return bool
     */
    public function hasState(): bool
    {
        return $this->session->offsetExists(self::CART_STATE_OFFSET);
    }

    /**
     * @return bool
     */
    public function hasOldState(): bool
    {
        return $this->session->offsetExists(self::CART_STATE_OLD_OFFSET);
    }

    /**
     * @return string
     */
    public function getOldState(): string
    {
        return $this->session->offsetGet(self::CART_STATE_OLD_OFFSET, false);
    }

    public function resetState()
    {
        $this->session->offsetUnset(self::CART_STATE_OFFSET);
    }

    public function resetOldState()
    {
        $this->session->offsetUnset(self::CART_STATE_OLD_OFFSET);
    }

    /**
     * {@inheritdoc}
     */
    public function isState(string $state): bool
    {
        return $this->session->offsetGet(self::CART_STATE_OFFSET, false) === $state;
    }

    /**
     * {@inheritdoc}
     */
    public function setStateId(int $id)
    {
        $this->session->offsetSet(self::CART_STATE_ID_OFFSET, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId(): int
    {
        return $this->session->offsetGet(self::CART_STATE_ID_OFFSET);
    }

    public function resetStateId()
    {
        $this->session->offsetUnset(self::CART_STATE_ID_OFFSET);
    }

    /**
     * {@inheritdoc}
     */
    public function hasStateId(): bool
    {
        return $this->session->offsetExists(self::CART_STATE_ID_OFFSET);
    }
}
