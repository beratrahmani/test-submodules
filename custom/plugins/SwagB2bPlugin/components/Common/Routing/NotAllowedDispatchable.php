<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\Components\Api\Exception\NotFoundException;

class NotAllowedDispatchable implements Dispatchable
{
    /**
     * @var string
     */
    private $query;

    /**
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    /**
     * @param Request $request
     * @throws NotFoundException
     */
    public function dispatch(Request $request)
    {
        throw new NotFoundException($this->query);
    }
}
