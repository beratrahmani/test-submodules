<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Updater class used during installation and updates, therefore very light on dependencies
 */
class AclRoutingUpdateService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Necessary for plugin install
     *
     * @return AclRoutingUpdateService
     */
    public static function create(): self
    {
        return new self(Shopware()->Container()->get('dbal_connection'));
    }

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array $config
     */
    public function addConfig(array $config)
    {
        foreach ($config as $resourceName => $controllers) {
            foreach ($controllers as $controllerName => $actions) {
                foreach ($actions as $actionName => $privilegeName) {
                    if ($privilegeName !== 'free') {
                        $this->setAclRoute($controllerName, $actionName, $resourceName, $privilegeName);
                    }
                }
            }
        }
    }

    /**
     * @internal
     * @param string $controller
     * @param string $action
     * @param string $resourceName
     * @param string $privilegeName
     */
    protected function setAclRoute(string $controller, string $action, string $resourceName, string $privilegeName)
    {
        $controller = strtolower($controller);
        $action = strtolower($action);

        try {
            $this->connection->insert(
                'b2b_acl_route_privilege',
                [
                    'resource_name' => $resourceName,
                    'privilege_type' => $privilegeName,
                ]
            );
        } catch (DBALException $e) {
            //nth
        }

        $privilegeId = $this->connection->fetchColumn(
            'SELECT id FROM b2b_acl_route_privilege 
             WHERE resource_name = :resourceName AND privilege_type = :privilegeType',
            [
                'resourceName' => $resourceName,
                'privilegeType' => $privilegeName,
            ]
        );

        try {
            $this->connection->insert(
                'b2b_acl_route',
                [
                    'privilege_id' => $privilegeId,
                    'controller' => $controller,
                    'action' => $action,
                ]
            );
        } catch (DBALException $e) {
            //nth
        }

        $this->connection->update(
            'b2b_acl_route',
            [
                'privilege_id' => $privilegeId,
            ],
            [
                'controller' => $controller,
                'action' => $action,
            ]
        );

        $this->connection->exec(
            'DELETE p FROM b2b_acl_route_privilege p
             LEFT JOIN b2b_acl_route r ON r.privilege_id = p.id
             WHERE r.id IS NULL'
        );
    }
}
