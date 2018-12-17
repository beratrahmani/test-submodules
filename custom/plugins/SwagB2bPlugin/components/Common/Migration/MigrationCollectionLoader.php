<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Migration;

class MigrationCollectionLoader
{
    /**
     * @var array
     */
    private $directories = [];

    /**
     * @return MigrationCollectionLoader
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param string $directory
     * @param string $namespace
     * @return MigrationCollectionLoader
     */
    public function addDirectory(string $directory, string $namespace): self
    {
        $this->directories[$directory] = $namespace;

        return $this;
    }

    /**
     * @return MigrationStepInterface[]
     */
    public function getMigrationCollection(): array
    {
        $migrations = [];

        foreach ($this->directories as $directory => $namespace) {
            foreach (scandir($directory) as $classFileName) {
                $path = $directory . '/' . $classFileName;
                $className = $namespace . '\\' . pathinfo($classFileName, PATHINFO_FILENAME);

                if ('php' !== pathinfo($path, PATHINFO_EXTENSION)) {
                    continue;
                }

                if (!class_exists($className)) {
                    throw new \RuntimeException('Unable to load "' . $className . '"" at "' . $path . '"');
                }

                if (!is_a($className, MigrationStepInterface::class, true)) {
                    continue;
                }

                /** @var MigrationStepInterface $migration */
                $migration = new $className;

                if (isset($migrations[$migration->getCreationTimeStamp()])) {
                    throw new \DomainException('Can not handle two migrations with identical timestamps');
                }

                $migrations[$migration->getCreationTimeStamp()] = $migration;
            }
        }

        usort($migrations, function (MigrationStepInterface $a, MigrationStepInterface $b) {
            return ($a->getCreationTimeStamp() < $b->getCreationTimeStamp()) ? -1 : 1;
        });

        return $migrations;
    }
}
