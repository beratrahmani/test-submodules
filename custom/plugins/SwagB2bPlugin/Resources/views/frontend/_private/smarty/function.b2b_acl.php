<?php declare(strict_types=1);
interface SwagB2BSmartyConstants
{
    const ACL_CLASS_NAME = 'is--b2b-acl';
    const ACL_CLASS_FORBIDDEN = 'is--b2b-acl-forbidden';
    const ACL_CLASS_ALLOWED = 'is--b2b-acl-allowed';
    const ACL_CLASS_CONTROLLER_PREFIX = 'is--b2b-acl-controller-';
    const ACL_CLASS_ACTION_PREFIX = 'is--b2b-acl-action-';
}

/**
 * Renders subrequests for the B2B suite. As this component is used for uncached parts of shopware,
 * the {action} widget is not suitable, as it will render ESI subrequests when caches are in place
 *
 * @param $params
 * @return array|null|string
 */
function smarty_function_b2b_acl($params)
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
        $aclAction = implode(' ', array_map(function (string $value) {
            return SwagB2BSmartyConstants::ACL_CLASS_ACTION_PREFIX . $value;
        }, $action));
    } else {
        $isAllowed = $checkIsAllowed($controller, [$action]);
        $aclAction = SwagB2BSmartyConstants::ACL_CLASS_ACTION_PREFIX . $action;
    }

    $aclCssClass = SwagB2BSmartyConstants::ACL_CLASS_FORBIDDEN;

    if ($isAllowed) {
        $aclCssClass = SwagB2BSmartyConstants::ACL_CLASS_ALLOWED;
    }

    return implode(' ', [
        SwagB2BSmartyConstants::ACL_CLASS_NAME,
        SwagB2BSmartyConstants::ACL_CLASS_CONTROLLER_PREFIX . $controller,
        $aclAction,
        $aclCssClass,
    ]);
}
