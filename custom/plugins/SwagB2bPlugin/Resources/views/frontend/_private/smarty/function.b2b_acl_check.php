<?php declare(strict_types=1);

/**
 * Renders subrequests for the B2B suite. As this component is used for uncached parts of shopware,
 * the {action} widget is not suitable, as it will render ESI subrequests when caches are in place
 *
 * @param $params
 * @return bool
 */
function smarty_function_b2b_acl_check($params)
{
    if (!isset($params['controller'])) {
        throw new \InvalidArgumentException('Missing required parameter "controller"');
    }
    $controller = $params['controller'];

    if (isset($params['actions'])) {
        $params['action'] = explode(',', trim($params['actions']));
    }

    if (!isset($params['action'])) {
        throw new \InvalidArgumentException('Missing required parameter "action"');
    }
    $action = $params['action'];

    $checkIsAllowed = function (string $controller, array $actions) {
        $routeService = Shopware()->Container()->get('b2b_acl_route.service');

        foreach ($actions as $action) {
            if (!$routeService->isRouteAllowed($controller, $action)) {
                return false;
            }
        }

        return true;
    };

    if (is_array($action)) {
        $isAllowed = $checkIsAllowed($controller, $action);
    } else {
        $isAllowed = $checkIsAllowed($controller, [$action]);
    }

    return $isAllowed;
}
