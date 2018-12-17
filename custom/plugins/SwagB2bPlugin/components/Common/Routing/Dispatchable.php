<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

use Shopware\B2B\Common\MvcExtension\Request;

interface Dispatchable
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function dispatch(Request $request);
}
