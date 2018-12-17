<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContactAccessWriterCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $crudService = $container->findDefinition('b2b_contact.crud_service');
        $tags = $container->findTaggedServiceIds('b2b_contact_create.acl_access_writer');
        $serviceIds = array_keys($tags);

        $accessWriters = [];
        foreach ($serviceIds as $serviceId) {
            $accessWriters[] = new Reference($serviceId);
        }

        $crudService->replaceArgument(6, $accessWriters);
    }
}
