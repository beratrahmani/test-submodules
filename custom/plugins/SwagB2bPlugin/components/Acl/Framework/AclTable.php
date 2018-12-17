<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

/**
 * Main value object - representing the definition of a ACL relationship
 */
abstract class AclTable
{
    /**
     * @var string
     */
    private $contextTableName;

    /**
     * @var string
     */
    private $contextPrimaryKeyField;

    /**
     * @var string
     */
    private $subjectTableName;

    /**
     * @var string
     */
    private $subjectPrimaryKeyField;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string $contextTableName
     * @param string $contextPrimaryKeyField
     * @param string $subjectTableName
     * @param string $subjectPrimaryKeyField
     */
    public function __construct(
        string $name,
        string $contextTableName,
        string $contextPrimaryKeyField,
        string $subjectTableName,
        string $subjectPrimaryKeyField
    ) {
        $this->name = $name;
        $this->contextTableName = $contextTableName;
        $this->contextPrimaryKeyField = $contextPrimaryKeyField;
        $this->subjectTableName = $subjectTableName;
        $this->subjectPrimaryKeyField = $subjectPrimaryKeyField;
    }

    /**
     * @return string
     */
    public function getContextTableName(): string
    {
        return $this->contextTableName;
    }

    /**
     * @return string
     */
    public function getContextPrimaryKeyField(): string
    {
        return $this->contextPrimaryKeyField;
    }

    /**
     * @return string
     */
    public function getSubjectTableName(): string
    {
        return $this->subjectTableName;
    }

    /**
     * @return string
     */
    public function getSubjectPrimaryKeyField(): string
    {
        return $this->subjectPrimaryKeyField;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'b2b_acl_' . $this->name;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return sha1("{$this->contextTableName}_{$this->subjectTableName}");
    }

    /**
     * Extract an array of foreign keys from the selector, if the foreign table applies.
     *
     * @param object $context can be anything
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclTableResolverFacade
     */
    public function getResolver($context): AclTableResolverFacade
    {
        if (!is_object($context)) {
            throw new AclUnsupportedContextException('Context must be an object.');
        }

        $contextChain = $this->getContextResolvers();

        foreach ($contextChain as $contextQueryProducer) {
            try {
                $contextQueryProducer->extractId($context);

                return new AclTableResolverFacade($contextQueryProducer, $this->getName(), $context);
            } catch (AclUnsupportedContextException $e) {
                //nth;
            }
        }

        throw new AclUnsupportedContextException('No query resolver found for context "' . get_class($context) . '"');
    }

    /**
     * @param $context
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclTableResolverFacade
     */
    public function getMainResolver($context): AclTableResolverFacade
    {
        if (!is_object($context)) {
            throw new AclUnsupportedContextException('Context must be an object.');
        }

        $contextChain = $this->getContextResolvers();

        foreach ($contextChain as $contextQueryProducer) {
            if (!$contextQueryProducer->isMainContext()) {
                continue;
            }

            try {
                $contextQueryProducer->extractId($context);
            } catch (AclUnsupportedContextException $e) {
                continue;
            }

            return new AclTableResolverFacade($contextQueryProducer, $this->getName(), $context);
        }

        throw new AclUnsupportedContextException('Main resolver not compatible with context "' . get_class($context) . '"');
    }

    /**
     * @return AclContextResolver[]
     */
    abstract protected function getContextResolvers(): array;
}
