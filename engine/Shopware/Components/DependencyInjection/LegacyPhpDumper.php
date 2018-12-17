<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\Dumper;
use Symfony\Component\DependencyInjection\Exception\EnvParameterException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface as ProxyDumper;
use Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\TypedReference;
use Symfony\Component\DependencyInjection\Variable;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PhpDumper dumps a service container as a PHP class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class LegacyPhpDumper extends Dumper
{
    /**
     * Characters that might appear in the generated variable name as first character.
     */
    const FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Characters that might appear in the generated variable name as any but the first character.
     */
    const NON_FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789_';

    private $definitionVariables;
    private $referenceVariables;
    private $variableCount;
    private $inlinedDefinitions;
    private $serviceCalls;
    private $reservedVariables = ['instance', 'class', 'this'];
    private $expressionLanguage;
    private $targetDirRegex;
    private $targetDirMaxMatches;
    private $docStar;
    private $serviceIdToMethodNameMap;
    private $usedMethodNames;
    private $namespace;
    private $asFiles;
    private $hotPathTag;
    private $inlineRequires;
    private $inlinedRequires = [];
    private $circularReferences = [];

    /**
     * @var ProxyDumper
     */
    private $proxyDumper;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerBuilder $container)
    {
        if (!$container->isCompiled()) {
            @trigger_error('Dumping an uncompiled ContainerBuilder is deprecated since Symfony 3.3 and will not be supported anymore in 4.0. Compile the container beforehand.', E_USER_DEPRECATED);
        }

        parent::__construct($container);
    }

    /**
     * Sets the dumper to be used when dumping proxies in the generated container.
     */
    public function setProxyDumper(ProxyDumper $proxyDumper)
    {
        $this->proxyDumper = $proxyDumper;
    }

    /**
     * Dumps the service container as a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *  * namespace:  The class namespace
     *  * as_files:   To split the container in several files
     *
     * @throws EnvParameterException When an env var exists but has not been dumped
     *
     * @return string|array A PHP class representing the service container or an array of PHP files if the "as_files" option is set
     */
    public function dump(array $options = [])
    {
        $this->targetDirRegex = null;
        $this->inlinedRequires = [];
        $options = array_merge([
            'class' => 'ProjectServiceContainer',
            'base_class' => 'Container',
            'namespace' => '',
            'as_files' => false,
            'debug' => true,
            'hot_path_tag' => 'container.hot_path',
            'inline_class_loader_parameter' => 'container.dumper.inline_class_loader',
            'build_time' => time(),
        ], $options);

        $this->namespace = $options['namespace'];
        $this->asFiles = $options['as_files'];
        $this->hotPathTag = $options['hot_path_tag'];
        $this->inlineRequires = $options['inline_class_loader_parameter'] && $this->container->hasParameter($options['inline_class_loader_parameter']) && $this->container->getParameter($options['inline_class_loader_parameter']);

        if (strpos($baseClass = $options['base_class'], '\\') !== 0 && $baseClass !== 'Container') {
            $baseClass = sprintf('%s\%s', $options['namespace'] ? '\\' . $options['namespace'] : '', $baseClass);
            $baseClassWithNamespace = $baseClass;
        } elseif ($baseClass === 'Container') {
            $baseClassWithNamespace = Container::class;
        } else {
            $baseClassWithNamespace = $baseClass;
        }

        $this->initializeMethodNamesMap($baseClass === 'Container' ? Container::class : $baseClass);

        if ($this->getProxyDumper() instanceof NullDumper) {
            (new AnalyzeServiceReferencesPass(true, false))->process($this->container);
            try {
                (new CheckCircularReferencesPass())->process($this->container);
            } catch (ServiceCircularReferenceException $e) {
                $path = $e->getPath();
                end($path);
                $path[key($path)] .= '". Try running "composer require symfony/proxy-manager-bridge';

                throw new ServiceCircularReferenceException($e->getServiceId(), $path);
            }
        }

        (new AnalyzeServiceReferencesPass(false))->process($this->container);
        $this->circularReferences = [];
        $checkedNodes = [];
        foreach ($this->container->getCompiler()->getServiceReferenceGraph()->getNodes() as $id => $node) {
            $currentPath = [$id => $id];
            $this->analyzeCircularReferences($node->getOutEdges(), $checkedNodes, $currentPath);
        }
        $this->container->getCompiler()->getServiceReferenceGraph()->clear();

        $this->docStar = $options['debug'] ? '*' : '';

        if (!empty($options['file']) && is_dir($dir = \dirname($options['file']))) {
            // Build a regexp where the first root dirs are mandatory,
            // but every other sub-dir is optional up to the full path in $dir
            // Mandate at least 2 root dirs and not more that 5 optional dirs.

            $dir = explode(\DIRECTORY_SEPARATOR, realpath($dir));
            $i = \count($dir);

            if ($i >= 3) {
                $regex = '';
                $lastOptionalDir = $i > 8 ? $i - 5 : 3;
                $this->targetDirMaxMatches = $i - $lastOptionalDir;

                while (--$i >= $lastOptionalDir) {
                    $regex = sprintf('(%s%s)?', preg_quote(\DIRECTORY_SEPARATOR . $dir[$i], '#'), $regex);
                }

                do {
                    $regex = preg_quote(\DIRECTORY_SEPARATOR . $dir[$i], '#') . $regex;
                } while (--$i > 0);

                $this->targetDirRegex = '#' . preg_quote($dir[0], '#') . $regex . '#';
            }
        }

        $code =
            $this->startClass($options['class'], $baseClass, $baseClassWithNamespace) .
            $this->addServices() .
            $this->addDefaultParametersMethod() .
            $this->endClass()
        ;

        if ($this->asFiles) {
            $fileStart = <<<EOF
<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

EOF;
            $files = [];

            if ($ids = array_keys($this->container->getRemovedIds())) {
                sort($ids);
                $c = "<?php\n\nreturn array(\n";
                foreach ($ids as $id) {
                    $c .= '    ' . $this->doExport($id) . " => true,\n";
                }
                $files['removed-ids.php'] = $c .= ");\n";
            }

            foreach ($this->generateServiceFiles() as $file => $c) {
                $files[$file] = $fileStart . $c;
            }
            foreach ($this->generateProxyClasses() as $file => $c) {
                $files[$file] = "<?php\n" . $c;
            }
            $files[$options['class'] . '.php'] = $code;
            $hash = ucfirst(strtr(ContainerBuilder::hash($files), '._', 'xx'));
            $code = [];

            foreach ($files as $file => $c) {
                $code["Container{$hash}/{$file}"] = $c;
            }
            array_pop($code);
            $code["Container{$hash}/{$options['class']}.php"] = substr_replace($files[$options['class'] . '.php'], "<?php\n\nnamespace Container{$hash};\n", 0, 6);
            $namespaceLine = $this->namespace ? "\nnamespace {$this->namespace};\n" : '';
            $time = $options['build_time'];
            $id = hash('crc32', $hash . $time);

            $code[$options['class'] . '.php'] = <<<EOF
<?php
{$namespaceLine}
// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\\class_exists(\\Container{$hash}\\{$options['class']}::class, false)) {
    // no-op
} elseif (!include __DIR__.'/Container{$hash}/{$options['class']}.php') {
    touch(__DIR__.'/Container{$hash}.legacy');

    return;
}

if (!\\class_exists({$options['class']}::class, false)) {
    \\class_alias(\\Container{$hash}\\{$options['class']}::class, {$options['class']}::class, false);
}

return new \\Container{$hash}\\{$options['class']}(array(
    'container.build_hash' => '$hash',
    'container.build_id' => '$id',
    'container.build_time' => $time,
), __DIR__.\\DIRECTORY_SEPARATOR.'Container{$hash}');

EOF;
        } else {
            foreach ($this->generateProxyClasses() as $c) {
                $code .= $c;
            }
        }

        $this->targetDirRegex = null;
        $this->inlinedRequires = [];
        $this->circularReferences = [];

        $unusedEnvs = [];
        foreach ($this->container->getEnvCounters() as $env => $use) {
            if (!$use) {
                $unusedEnvs[] = $env;
            }
        }
        if ($unusedEnvs) {
            throw new EnvParameterException($unusedEnvs, null, 'Environment variables "%s" are never used. Please, check your container\'s configuration.');
        }

        return $code;
    }

    /**
     * Retrieves the currently set proxy dumper or instantiates one.
     *
     * @return ProxyDumper
     */
    private function getProxyDumper()
    {
        if (!$this->proxyDumper) {
            $this->proxyDumper = new NullDumper();
        }

        return $this->proxyDumper;
    }

    private function analyzeCircularReferences(array $edges, &$checkedNodes, &$currentPath)
    {
        foreach ($edges as $edge) {
            $node = $edge->getDestNode();
            $id = $node->getId();

            if ($node->getValue() && ($edge->isLazy() || $edge->isWeak())) {
                // no-op
            } elseif (isset($currentPath[$id])) {
                foreach (array_reverse($currentPath) as $parentId) {
                    $this->circularReferences[$parentId][$id] = $id;
                    $id = $parentId;
                }
            } elseif (!isset($checkedNodes[$id])) {
                $checkedNodes[$id] = true;
                $currentPath[$id] = $id;
                $this->analyzeCircularReferences($node->getOutEdges(), $checkedNodes, $currentPath);
                unset($currentPath[$id]);
            }
        }
    }

    private function collectLineage($class, array &$lineage)
    {
        if (isset($lineage[$class])) {
            return;
        }
        if (!$r = $this->container->getReflectionClass($class, false)) {
            return;
        }
        if ($this->container instanceof $class) {
            return;
        }
        $file = $r->getFileName();
        if (!$file || $this->doExport($file) === $exportedFile = $this->export($file)) {
            return;
        }

        if ($parent = $r->getParentClass()) {
            $this->collectLineage($parent->name, $lineage);
        }

        foreach ($r->getInterfaces() as $parent) {
            $this->collectLineage($parent->name, $lineage);
        }

        foreach ($r->getTraits() as $parent) {
            $this->collectLineage($parent->name, $lineage);
        }

        $lineage[$class] = substr($exportedFile, 1, -1);
    }

    private function generateProxyClasses()
    {
        $alreadyGenerated = [];
        $definitions = $this->container->getDefinitions();
        $strip = $this->docStar === '' && method_exists('Symfony\Component\HttpKernel\Kernel', 'stripComments');
        $proxyDumper = $this->getProxyDumper();
        ksort($definitions);
        foreach ($definitions as $definition) {
            if (!$proxyDumper->isProxyCandidate($definition)) {
                continue;
            }
            if (isset($alreadyGenerated[$class = $definition->getClass()])) {
                continue;
            }
            $alreadyGenerated[$class] = true;
            // register class' reflector for resource tracking
            $this->container->getReflectionClass($class);
            $proxyCode = "\n" . $proxyDumper->getProxyCode($definition);
            if ($strip) {
                $proxyCode = "<?php\n" . $proxyCode;
                $proxyCode = substr(Kernel::stripComments($proxyCode), 5);
            }
            yield sprintf('%s.php', explode(' ', $proxyCode, 3)[1]) => $proxyCode;
        }
    }

    /**
     * Generates the require_once statement for service includes.
     *
     * @return string
     */
    private function addServiceInclude($cId, Definition $definition)
    {
        $code = '';

        if ($this->inlineRequires && !$this->isHotPath($definition)) {
            $lineage = [];
            foreach ($this->inlinedDefinitions as $def) {
                if (!$def->isDeprecated() && \is_string($class = \is_array($factory = $def->getFactory()) && \is_string($factory[0]) ? $factory[0] : $def->getClass())) {
                    $this->collectLineage($class, $lineage);
                }
            }

            foreach ($this->serviceCalls as $id => list($callCount, $behavior)) {
                if ($id !== 'service_container' && $id !== $cId
                    && $behavior !== ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE
                    && $this->container->has($id)
                    && $this->isTrivialInstance($def = $this->container->findDefinition($id))
                    && \is_string($class = \is_array($factory = $def->getFactory()) && \is_string($factory[0]) ? $factory[0] : $def->getClass())
                ) {
                    $this->collectLineage($class, $lineage);
                }
            }

            foreach (array_diff_key(array_flip($lineage), $this->inlinedRequires) as $file => $class) {
                $code .= sprintf("        include_once %s;\n", $file);
            }
        }

        foreach ($this->inlinedDefinitions as $def) {
            if ($file = $def->getFile()) {
                $code .= sprintf("        include_once %s;\n", $this->dumpValue($file));
            }
        }

        if ($code !== '') {
            $code .= "\n";
        }

        return $code;
    }

    /**
     * Generates the service instance.
     *
     * @param string     $id
     * @param Definition $definition
     * @param bool       $isSimpleInstance
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return string
     */
    private function addServiceInstance($id, Definition $definition, $isSimpleInstance)
    {
        $class = $this->dumpValue($definition->getClass());

        if (strpos($class, "'") === 0 && strpos($class, '$') === false && !preg_match('/^\'(?:\\\{2})?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\{2}[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\'$/', $class)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid class name for the "%s" service.', $class, $id));
        }

        $isProxyCandidate = $this->getProxyDumper()->isProxyCandidate($definition);
        $instantiation = '';

        if (!$isProxyCandidate && $definition->isShared()) {
            $instantiation = "\$this->services['$id'] = " . ($isSimpleInstance ? '' : '$instance');
        } elseif (!$isSimpleInstance) {
            $instantiation = '$instance';
        }

        $return = '';
        if ($isSimpleInstance) {
            $return = 'return ';
        } else {
            $instantiation .= ' = ';
        }

        return $this->addNewInstance($definition, $return, $instantiation, $id);
    }

    /**
     * Checks if the definition is a trivial instance.
     *
     * @param Definition $definition
     *
     * @return bool
     */
    private function isTrivialInstance(Definition $definition)
    {
        if ($definition->isSynthetic() || $definition->getFile() || $definition->getMethodCalls() || $definition->getProperties() || $definition->getConfigurator()) {
            return false;
        }
        if ($definition->isDeprecated() || $definition->isLazy() || $definition->getFactory() || \count($definition->getArguments()) > 3) {
            return false;
        }

        foreach ($definition->getArguments() as $arg) {
            if (!$arg || $arg instanceof Parameter) {
                continue;
            }
            if (\is_array($arg) && \count($arg) <= 3) {
                foreach ($arg as $k => $v) {
                    if ($this->dumpValue($k) !== $this->dumpValue($k, false)) {
                        return false;
                    }
                    if (!$v || $v instanceof Parameter) {
                        continue;
                    }
                    if ($v instanceof Reference && $this->container->has($id = (string) $v) && $this->container->findDefinition($id)->isSynthetic()) {
                        continue;
                    }
                    if (!is_scalar($v) || $this->dumpValue($v) !== $this->dumpValue($v, false)) {
                        return false;
                    }
                }
            } elseif ($arg instanceof Reference && $this->container->has($id = (string) $arg) && $this->container->findDefinition($id)->isSynthetic()) {
                continue;
            } elseif (!is_scalar($arg) || $this->dumpValue($arg) !== $this->dumpValue($arg, false)) {
                return false;
            }
        }

        if (strpos($this->dumpLiteralClass($this->dumpValue($definition->getClass())), '$') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Adds method calls to a service definition.
     *
     * @param Definition $definition
     * @param string     $variableName
     *
     * @return string
     */
    private function addServiceMethodCalls(Definition $definition, $variableName = 'instance')
    {
        $calls = '';
        foreach ($definition->getMethodCalls() as $call) {
            $arguments = [];
            foreach ($call[1] as $value) {
                $arguments[] = $this->dumpValue($value);
            }

            $calls .= $this->wrapServiceConditionals($call[1], sprintf("        \$%s->%s(%s);\n", $variableName, $call[0], implode(', ', $arguments)));
        }

        return $calls;
    }

    private function addServiceProperties(Definition $definition, $variableName = 'instance')
    {
        $code = '';
        foreach ($definition->getProperties() as $name => $value) {
            $code .= sprintf("        \$%s->%s = %s;\n", $variableName, $name, $this->dumpValue($value));
        }

        return $code;
    }

    /**
     * Adds configurator definition.
     *
     * @param Definition $definition
     * @param string     $variableName
     *
     * @return string
     */
    private function addServiceConfigurator(Definition $definition, $variableName = 'instance')
    {
        if (!$callable = $definition->getConfigurator()) {
            return '';
        }

        if (\is_array($callable)) {
            if ($callable[0] instanceof Reference
                || ($callable[0] instanceof Definition && $this->definitionVariables->contains($callable[0]))) {
                return sprintf("        %s->%s(\$%s);\n", $this->dumpValue($callable[0]), $callable[1], $variableName);
            }

            $class = $this->dumpValue($callable[0]);
            // If the class is a string we can optimize call_user_func away
            if (strpos($class, "'") === 0 && strpos($class, '$') === false) {
                return sprintf("        %s::%s(\$%s);\n", $this->dumpLiteralClass($class), $callable[1], $variableName);
            }

            if (strpos($class, 'new ') === 0) {
                return sprintf("        (%s)->%s(\$%s);\n", $this->dumpValue($callable[0]), $callable[1], $variableName);
            }

            return sprintf("        \\call_user_func(array(%s, '%s'), \$%s);\n", $this->dumpValue($callable[0]), $callable[1], $variableName);
        }

        return sprintf("        %s(\$%s);\n", $callable, $variableName);
    }

    /**
     * Adds a service.
     *
     * @param string     $id
     * @param Definition $definition
     * @param string     &$file
     *
     * @return string
     */
    private function addService($id, Definition $definition, &$file = null)
    {
        $this->definitionVariables = new \SplObjectStorage();
        $this->referenceVariables = [];
        $this->variableCount = 0;
        $this->definitionVariables[$definition] = $this->referenceVariables[$id] = new Variable('instance');

        $return = [];

        if ($class = $definition->getClass()) {
            $class = $this->container->resolveEnvPlaceholders($class);
            $return[] = sprintf(strpos($class, '%') === 0 ? '@return object A %1$s instance' : '@return \%s', ltrim($class, '\\'));
        } elseif ($definition->getFactory()) {
            $factory = $definition->getFactory();
            if (\is_string($factory)) {
                $return[] = sprintf('@return object An instance returned by %s()', $factory);
            } elseif (\is_array($factory) && (\is_string($factory[0]) || $factory[0] instanceof Definition || $factory[0] instanceof Reference)) {
                if (\is_string($factory[0]) || $factory[0] instanceof Reference) {
                    $return[] = sprintf('@return object An instance returned by %s::%s()', (string) $factory[0], $factory[1]);
                } elseif ($factory[0] instanceof Definition) {
                    $return[] = sprintf('@return object An instance returned by %s::%s()', $factory[0]->getClass(), $factory[1]);
                }
            }
        }

        if ($definition->isDeprecated()) {
            if ($return && strpos($return[\count($return) - 1], '@return') === 0) {
                $return[] = '';
            }

            $return[] = sprintf('@deprecated %s', $definition->getDeprecationMessage($id));
        }

        $return = str_replace("\n     * \n", "\n     *\n", implode("\n     * ", $return));
        $return = $this->container->resolveEnvPlaceholders($return);

        $shared = $definition->isShared() ? ' shared' : '';
        $public = $definition->isPublic() ? 'public' : 'private';
        $autowired = $definition->isAutowired() ? ' autowired' : '';

        if ($definition->isLazy()) {
            $lazyInitialization = '$lazyLoad = true';
        } else {
            $lazyInitialization = '';
        }

        $asFile = $this->asFiles && $definition->isShared() && !$this->isHotPath($definition);
        $methodName = $this->generateMethodName($id);
        if ($asFile) {
            $file = $methodName . '.php';
            $code = "        // Returns the $public '$id'$shared$autowired service.\n\n";
        } else {
            $code = <<<EOF

    /*{$this->docStar}
     * Gets the $public '$id'$shared$autowired service.
     *
     * $return
     */
    protected function {$methodName}($lazyInitialization)
    {

EOF;
        }

        $this->serviceCalls = [];
        $this->inlinedDefinitions = $this->getDefinitionsFromArguments([$definition], null, $this->serviceCalls);

        $code .= $this->addServiceInclude($id, $definition);

        if ($this->getProxyDumper()->isProxyCandidate($definition)) {
            $factoryCode = $asFile ? "\$this->load('%s.php', false)" : '$this->%s(false)';
            $code .= $this->getProxyDumper()->getProxyFactoryCode($definition, $id, sprintf($factoryCode, $methodName));
        }

        if ($definition->isDeprecated()) {
            $code .= sprintf("        @trigger_error(%s, E_USER_DEPRECATED);\n\n", $this->export($definition->getDeprecationMessage($id)));
        }

        $head = $tail = '';
        $arguments = [$definition->getArguments(), $definition->getFactory()];
        $this->addInlineVariables($head, $tail, $id, $arguments, true);
        $code .= $head !== '' ? $head . "\n" : '';

        if ($arguments = array_filter([$definition->getProperties(), $definition->getMethodCalls(), $definition->getConfigurator()])) {
            $this->addInlineVariables($tail, $tail, $id, $arguments, false);

            $tail .= $tail !== '' ? "\n" : '';
            $tail .= $this->addServiceProperties($definition);
            $tail .= $this->addServiceMethodCalls($definition);
            $tail .= $this->addServiceConfigurator($definition);
        }

        $code .= $this->addServiceInstance($id, $definition, $tail === '')
            . ($tail !== '' ? "\n" . $tail . "\n        return \$instance;\n" : '');

        if ($asFile) {
            $code = implode("\n", array_map(function ($line) { return $line ? substr($line, 8) : $line; }, explode("\n", $code)));
        } else {
            $code .= "    }\n";
        }

        $this->definitionVariables = $this->inlinedDefinitions = null;
        $this->referenceVariables = $this->serviceCalls = null;

        return $code;
    }

    private function addInlineVariables(&$head, &$tail, $id, array $arguments, $forConstructor)
    {
        $hasSelfRef = false;

        foreach ($arguments as $argument) {
            if (\is_array($argument)) {
                $hasSelfRef = $this->addInlineVariables($head, $tail, $id, $argument, $forConstructor) || $hasSelfRef;
            } elseif ($argument instanceof Reference) {
                $hasSelfRef = $this->addInlineReference($head, $tail, $id, $this->container->normalizeId($argument), $forConstructor) || $hasSelfRef;
            } elseif ($argument instanceof Definition) {
                $hasSelfRef = $this->addInlineService($head, $tail, $id, $argument, $forConstructor) || $hasSelfRef;
            }
        }

        return $hasSelfRef;
    }

    private function addInlineReference(&$head, &$tail, $id, $targetId, $forConstructor)
    {
        if ($targetId === 'service_container' || isset($this->referenceVariables[$targetId])) {
            return isset($this->circularReferences[$id][$targetId]);
        }

        list($callCount, $behavior) = $this->serviceCalls[$targetId];

        if ($callCount < 2 && (!$forConstructor || !isset($this->circularReferences[$id][$targetId]))) {
            return isset($this->circularReferences[$id][$targetId]);
        }

        $name = $this->getNextVariableName();
        $this->referenceVariables[$targetId] = new Variable($name);

        $reference = $behavior <= ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE ? new Reference($targetId, $behavior) : null;
        $code = sprintf("        \$%s = %s;\n", $name, $this->getServiceCall($targetId, $reference));

        if (!isset($this->circularReferences[$id][$targetId])) {
            $head .= $code;

            return false;
        }

        if (!$forConstructor) {
            $tail .= $code;

            return true;
        }

        $head .= $code . sprintf(<<<'EOTXT'

        if (isset($this->%s['%s'])) {
            return $this->%1$s['%2$s'];
        }

EOTXT
                ,
                'services',
                $id
            );

        return false;
    }

    private function addInlineService(&$head, &$tail, $id, Definition $definition, $forConstructor)
    {
        if (isset($this->definitionVariables[$definition])) {
            return false;
        }

        $arguments = [$definition->getArguments(), $definition->getFactory()];

        if ($this->inlinedDefinitions[$definition] < 2 && !$definition->getMethodCalls() && !$definition->getProperties() && !$definition->getConfigurator() && strpos($this->dumpValue($definition->getClass()), '$') === false) {
            return $this->addInlineVariables($head, $tail, $id, $arguments, $forConstructor);
        }

        $name = $this->getNextVariableName();
        $this->definitionVariables[$definition] = new Variable($name);

        $code = '';
        $hasSelfRef = $this->addInlineVariables($code, $tail, $id, $arguments, $forConstructor);
        $code .= $this->addNewInstance($definition, '$' . $name, ' = ', $id);
        $hasSelfRef ? $tail .= ($tail !== '' ? "\n" : '') . $code : $head .= ($head !== '' ? "\n" : '') . $code;

        $code = '';
        $arguments = [$definition->getProperties(), $definition->getMethodCalls(), $definition->getConfigurator()];
        $hasSelfRef = $this->addInlineVariables($code, $code, $id, $arguments, false) || $hasSelfRef;

        $code .= $code !== '' ? "\n" : '';
        $code .= $this->addServiceProperties($definition, $name);
        $code .= $this->addServiceMethodCalls($definition, $name);
        $code .= $this->addServiceConfigurator($definition, $name);
        if ($code !== '') {
            $hasSelfRef ? $tail .= ($tail !== '' ? "\n" : '') . $code : $head .= $code;
        }

        return $hasSelfRef;
    }

    /**
     * Adds multiple services.
     *
     * @return string
     */
    private function addServices()
    {
        $publicServices = $privateServices = '';
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if ($definition->isSynthetic() || ($this->asFiles && $definition->isShared() && !$this->isHotPath($definition))) {
                continue;
            }
            if ($definition->isPublic()) {
                $publicServices .= $this->addService($id, $definition);
            } else {
                $privateServices .= $this->addService($id, $definition);
            }
        }

        return $publicServices . $privateServices;
    }

    private function generateServiceFiles()
    {
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if (!$definition->isSynthetic() && $definition->isShared() && !$this->isHotPath($definition)) {
                $code = $this->addService($id, $definition, $file);
                yield $file => $code;
            }
        }
    }

    private function addNewInstance(Definition $definition, $return, $instantiation, $id)
    {
        $class = $this->dumpValue($definition->getClass());
        $return = '        ' . $return . $instantiation;

        $arguments = [];
        foreach ($definition->getArguments() as $value) {
            $arguments[] = $this->dumpValue($value);
        }

        if ($definition->getFactory() !== null) {
            $callable = $definition->getFactory();
            if (\is_array($callable)) {
                if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $callable[1])) {
                    throw new RuntimeException(sprintf('Cannot dump definition because of invalid factory method (%s)', $callable[1] ?: 'n/a'));
                }

                if ($callable[0] instanceof Reference
                    || ($callable[0] instanceof Definition && $this->definitionVariables->contains($callable[0]))) {
                    return $return . sprintf("%s->%s(%s);\n", $this->dumpValue($callable[0]), $callable[1], $arguments ? implode(', ', $arguments) : '');
                }

                $class = $this->dumpValue($callable[0]);
                // If the class is a string we can optimize call_user_func away
                if (strpos($class, "'") === 0 && strpos($class, '$') === false) {
                    if ($class === "''") {
                        throw new RuntimeException(sprintf('Cannot dump definition: The "%s" service is defined to be created by a factory but is missing the service reference, did you forget to define the factory service id or class?', $id));
                    }

                    return $return . sprintf("%s::%s(%s);\n", $this->dumpLiteralClass($class), $callable[1], $arguments ? implode(', ', $arguments) : '');
                }

                if (strpos($class, 'new ') === 0) {
                    return $return . sprintf("(%s)->%s(%s);\n", $class, $callable[1], $arguments ? implode(', ', $arguments) : '');
                }

                return $return . sprintf("\\call_user_func(array(%s, '%s')%s);\n", $class, $callable[1], $arguments ? ', ' . implode(', ', $arguments) : '');
            }

            return $return . sprintf("%s(%s);\n", $this->dumpLiteralClass($this->dumpValue($callable)), $arguments ? implode(', ', $arguments) : '');
        }

        if (strpos($class, '$') !== false) {
            return sprintf("        \$class = %s;\n\n%snew \$class(%s);\n", $class, $return, implode(', ', $arguments));
        }

        return $return . sprintf("new %s(%s);\n", $this->dumpLiteralClass($class), implode(', ', $arguments));
    }

    /**
     * Adds the class headers.
     *
     * @param string $class                  Class name
     * @param string $baseClass              The name of the base class
     * @param string $baseClassWithNamespace Fully qualified base class name
     *
     * @return string
     */
    private function startClass($class, $baseClass, $baseClassWithNamespace)
    {
        $bagClass = $this->container->isCompiled() ? 'use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;' : 'use Symfony\Component\DependencyInjection\ParameterBag\\ParameterBag;';
        $namespaceLine = !$this->asFiles && $this->namespace ? "\nnamespace {$this->namespace};\n" : '';

        $code = <<<EOF
<?php
$namespaceLine
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
$bagClass

/*{$this->docStar}
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class $class extends $baseClass
{
    private \$parameters;
    private \$targetDirs = array();

    public function __construct()
    {

EOF;
        if ($this->targetDirRegex !== null) {
            $dir = $this->asFiles ? '$this->targetDirs[0] = \\dirname($containerDir)' : '__DIR__';
            $code .= <<<EOF
        \$dir = {$dir};
        for (\$i = 1; \$i <= {$this->targetDirMaxMatches}; ++\$i) {
            \$this->targetDirs[\$i] = \$dir = \\dirname(\$dir);
        }

EOF;
        }
        if ($this->asFiles) {
            $code = str_replace('$parameters', "\$buildParameters;\n    private \$containerDir;\n    private \$parameters", $code);
            $code = str_replace('__construct()', '__construct(array $buildParameters = array(), $containerDir = __DIR__)', $code);
            $code .= "        \$this->buildParameters = \$buildParameters;\n";
            $code .= "        \$this->containerDir = \$containerDir;\n";
        }

        if ($this->container->isCompiled()) {
            if ($baseClassWithNamespace !== Container::class) {
                $r = $this->container->getReflectionClass($baseClassWithNamespace, false);
                if ($r !== null
                    && (null !== $constructor = $r->getConstructor())
                    && $constructor->getNumberOfRequiredParameters() === 0
                    && $constructor->getDeclaringClass()->name !== Container::class
                ) {
                    $code .= "        parent::__construct();\n";
                    $code .= "        \$this->parameterBag = null;\n\n";
                }
            }

            if ($this->container->getParameterBag()->all()) {
                $code .= "        \$this->parameters = \$this->getDefaultParameters();\n\n";
            }

            $code .= "        \$this->services = array();\n";
        } else {
            $arguments = $this->container->getParameterBag()->all() ? 'new ParameterBag($this->getDefaultParameters())' : null;
            $code .= "        parent::__construct($arguments);\n";
        }

        $code .= $this->addNormalizedIds();
        $code .= $this->addSyntheticIds();
        $code .= $this->addMethodMap();
        $code .= $this->asFiles ? $this->addFileMap() : '';
        $code .= $this->addPrivateServices();
        $code .= $this->addAliases();
        $code .= $this->addInlineRequires();
        $code .= <<<'EOF'
    }

EOF;
        $code .= $this->addRemovedIds();

        if ($this->container->isCompiled()) {
            $code .= <<<EOF

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

EOF;
        }

        if ($this->asFiles) {
            $code .= <<<EOF

    protected function load(\$file, \$lazyLoad = true)
    {
        return require \$this->containerDir.\\DIRECTORY_SEPARATOR.\$file;
    }

EOF;
        }

        $proxyDumper = $this->getProxyDumper();
        foreach ($this->container->getDefinitions() as $definition) {
            if (!$proxyDumper->isProxyCandidate($definition)) {
                continue;
            }
            if ($this->asFiles) {
                $proxyLoader = '$this->load("{$class}.php")';
            } elseif ($this->namespace) {
                $proxyLoader = 'class_alias("' . $this->namespace . '\\\\{$class}", $class, false)';
            } else {
                $proxyLoader = '';
            }
            if ($proxyLoader) {
                $proxyLoader = "class_exists(\$class, false) || {$proxyLoader};\n\n        ";
            }
            $code .= <<<EOF

    protected function createProxy(\$class, \Closure \$factory)
    {
        {$proxyLoader}return \$factory();
    }

EOF;
            break;
        }

        return $code;
    }

    /**
     * Adds the normalizedIds property definition.
     *
     * @return string
     */
    private function addNormalizedIds()
    {
        $code = '';
        $normalizedIds = $this->container->getNormalizedIds();
        ksort($normalizedIds);
        foreach ($normalizedIds as $id => $normalizedId) {
            if ($this->container->has($normalizedId)) {
                $code .= '            ' . $this->doExport($id) . ' => ' . $this->doExport($normalizedId) . ",\n";
            }
        }

        return $code ? "        \$this->normalizedIds = array(\n" . $code . "        );\n" : '';
    }

    /**
     * Adds the syntheticIds definition.
     *
     * @return string
     */
    private function addSyntheticIds()
    {
        $code = '';
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if ($definition->isSynthetic() && $id !== 'service_container') {
                $code .= '            ' . $this->doExport($id) . " => true,\n";
            }
        }

        return $code ? "        \$this->syntheticIds = array(\n{$code}        );\n" : '';
    }

    /**
     * Adds the removedIds definition.
     *
     * @return string
     */
    private function addRemovedIds()
    {
        if (!$ids = $this->container->getRemovedIds()) {
            return '';
        }
        if ($this->asFiles) {
            $code = "require \$this->containerDir.\\DIRECTORY_SEPARATOR.'removed-ids.php'";
        } else {
            $code = '';
            $ids = array_keys($ids);
            sort($ids);
            foreach ($ids as $id) {
                if (preg_match('/^\d+_[^~]++~[._a-zA-Z\d]{7}$/', $id)) {
                    continue;
                }
                $code .= '            ' . $this->doExport($id) . " => true,\n";
            }

            $code = "array(\n{$code}        )";
        }

        return <<<EOF

    public function getRemovedIds()
    {
        return {$code};
    }

EOF;
    }

    /**
     * Adds the methodMap property definition.
     *
     * @return string
     */
    private function addMethodMap()
    {
        $code = '';
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if (!$definition->isSynthetic() && (!$this->asFiles || !$definition->isShared() || $this->isHotPath($definition))) {
                $code .= '            ' . $this->doExport($id) . ' => ' . $this->doExport($this->generateMethodName($id)) . ",\n";
            }
        }

        return $code ? "        \$this->methodMap = array(\n{$code}        );\n" : '';
    }

    /**
     * Adds the fileMap property definition.
     *
     * @return string
     */
    private function addFileMap()
    {
        $code = '';
        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if (!$definition->isSynthetic() && $definition->isShared() && !$this->isHotPath($definition)) {
                $code .= sprintf("            %s => '%s.php',\n", $this->doExport($id), $this->generateMethodName($id));
            }
        }

        return $code ? "        \$this->fileMap = array(\n{$code}        );\n" : '';
    }

    /**
     * Adds the privates property definition.
     *
     * @return string
     */
    private function addPrivateServices()
    {
        $code = '';

        $aliases = $this->container->getAliases();
        ksort($aliases);
        foreach ($aliases as $id => $alias) {
            if ($alias->isPrivate()) {
                $code .= '            ' . $this->doExport($id) . " => true,\n";
            }
        }

        $definitions = $this->container->getDefinitions();
        ksort($definitions);
        foreach ($definitions as $id => $definition) {
            if (!$definition->isPublic()) {
                $code .= '            ' . $this->doExport($id) . " => true,\n";
            }
        }

        if (empty($code)) {
            return '';
        }

        $out = "        \$this->privates = array(\n";
        $out .= $code;
        $out .= "        );\n";

        return $out;
    }

    /**
     * Adds the aliases property definition.
     *
     * @return string
     */
    private function addAliases()
    {
        if (!$aliases = $this->container->getAliases()) {
            return $this->container->isCompiled() ? "\n        \$this->aliases = array();\n" : '';
        }

        $code = "        \$this->aliases = array(\n";
        ksort($aliases);
        foreach ($aliases as $alias => $id) {
            $id = $this->container->normalizeId($id);
            while (isset($aliases[$id])) {
                $id = $this->container->normalizeId($aliases[$id]);
            }
            $code .= '            ' . $this->doExport($alias) . ' => ' . $this->doExport($id) . ",\n";
        }

        return $code . "        );\n";
    }

    private function addInlineRequires()
    {
        if (!$this->hotPathTag || !$this->inlineRequires) {
            return '';
        }

        $lineage = [];

        foreach ($this->container->findTaggedServiceIds($this->hotPathTag) as $id => $tags) {
            $definition = $this->container->getDefinition($id);
            $inlinedDefinitions = $this->getDefinitionsFromArguments([$definition]);

            foreach ($inlinedDefinitions as $def) {
                if (\is_string($class = \is_array($factory = $def->getFactory()) && \is_string($factory[0]) ? $factory[0] : $def->getClass())) {
                    $this->collectLineage($class, $lineage);
                }
            }
        }

        $code = '';

        foreach ($lineage as $file) {
            if (!isset($this->inlinedRequires[$file])) {
                $this->inlinedRequires[$file] = true;
                $code .= sprintf("\n            include_once %s;", $file);
            }
        }

        return $code ? sprintf("\n        \$this->privates['service_container'] = function () {%s\n        };\n", $code) : '';
    }

    /**
     * Adds default parameters method.
     *
     * @return string
     */
    private function addDefaultParametersMethod()
    {
        if (!$this->container->getParameterBag()->all()) {
            return '';
        }

        $php = [];
        $dynamicPhp = [];
        $normalizedParams = [];

        foreach ($this->container->getParameterBag()->all() as $key => $value) {
            if ($key !== $resolvedKey = $this->container->resolveEnvPlaceholders($key)) {
                throw new InvalidArgumentException(sprintf('Parameter name cannot use env parameters: %s.', $resolvedKey));
            }
            if ($key !== $lcKey = strtolower($key)) {
                $normalizedParams[] = sprintf('        %s => %s,', $this->export($lcKey), $this->export($key));
            }
            $export = $this->exportParameters([$value]);
            $export = explode('0 => ', substr(rtrim($export, " )\n"), 7, -1), 2);

            if (preg_match("/\\\$this->(?:getEnv\('(?:\w++:)*+\w++'\)|targetDirs\[\d++\])/", $export[1])) {
                $dynamicPhp[$key] = sprintf('%scase %s: $value = %s; break;', $export[0], $this->export($key), $export[1]);
            } else {
                $php[] = sprintf('%s%s => %s,', $export[0], $this->export($key), $export[1]);
            }
        }
        $parameters = sprintf("array(\n%s\n%s)", implode("\n", $php), str_repeat(' ', 8));

        $code = '';
        if ($this->container->isCompiled()) {
            $code .= <<<'EOF'

    public function getParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
            $name = $this->normalizeParameterName($name);

            if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
                throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
            }
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    public function hasParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return true;
        }
        $name = $this->normalizeParameterName($name);

        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters);
    }

    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

EOF;
            if (!$this->asFiles) {
                $code = preg_replace('/^.*buildParameters.*\n.*\n.*\n/m', '', $code);
            }

            if ($dynamicPhp) {
                $loadedDynamicParameters = $this->exportParameters(array_combine(array_keys($dynamicPhp), array_fill(0, \count($dynamicPhp), false)), '', 8);
                $getDynamicParameter = <<<'EOF'
        switch ($name) {
%s
            default: throw new InvalidArgumentException(sprintf('The dynamic parameter "%%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
EOF;
                $getDynamicParameter = sprintf($getDynamicParameter, implode("\n", $dynamicPhp));
            } else {
                $loadedDynamicParameters = 'array()';
                $getDynamicParameter = str_repeat(' ', 8) . 'throw new InvalidArgumentException(sprintf(\'The dynamic parameter "%s" must be defined.\', $name));';
            }

            $code .= <<<EOF

    private \$loadedDynamicParameters = {$loadedDynamicParameters};
    private \$dynamicParameters = array();

    /*{$this->docStar}
     * Computes a dynamic parameter.
     *
     * @param string The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter(\$name)
    {
{$getDynamicParameter}
    }


EOF;

            $code .= '    private $normalizedParameterNames = ' . ($normalizedParams ? sprintf("array(\n%s\n    );", implode("\n", $normalizedParams)) : 'array();') . "\n";
            $code .= <<<'EOF'

    private function normalizeParameterName($name)
    {
        if (isset($this->normalizedParameterNames[$normalizedName = strtolower($name)]) || isset($this->parameters[$normalizedName]) || array_key_exists($normalizedName, $this->parameters)) {
            $normalizedName = isset($this->normalizedParameterNames[$normalizedName]) ? $this->normalizedParameterNames[$normalizedName] : $normalizedName;
            if ((string) $name !== $normalizedName) {
                @trigger_error(sprintf('Parameter names will be made case sensitive in Symfony 4.0. Using "%s" instead of "%s" is deprecated since Symfony 3.4.', $name, $normalizedName), E_USER_DEPRECATED);
            }
        } else {
            $normalizedName = $this->normalizedParameterNames[$normalizedName] = (string) $name;
        }

        return $normalizedName;
    }

EOF;
        } elseif ($dynamicPhp) {
            throw new RuntimeException('You cannot dump a not-frozen container with dynamic parameters.');
        }

        $code .= <<<EOF

    /*{$this->docStar}
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return $parameters;
    }

EOF;

        return $code;
    }

    /**
     * Exports parameters.
     *
     * @param array  $parameters
     * @param string $path
     * @param int    $indent
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    private function exportParameters(array $parameters, $path = '', $indent = 12)
    {
        $php = [];
        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                $value = $this->exportParameters($value, $path . '/' . $key, $indent + 4);
            } elseif ($value instanceof ArgumentInterface) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain special arguments. "%s" found in "%s".', \get_class($value), $path . '/' . $key));
            } elseif ($value instanceof Variable) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain variable references. Variable "%s" found in "%s".', $value, $path . '/' . $key));
            } elseif ($value instanceof Definition) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain service definitions. Definition for "%s" found in "%s".', $value->getClass(), $path . '/' . $key));
            } elseif ($value instanceof Reference) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain references to other services (reference to service "%s" found in "%s").', $value, $path . '/' . $key));
            } elseif ($value instanceof Expression) {
                throw new InvalidArgumentException(sprintf('You cannot dump a container with parameters that contain expressions. Expression "%s" found in "%s".', $value, $path . '/' . $key));
            } else {
                $value = $this->export($value);
            }

            $php[] = sprintf('%s%s => %s,', str_repeat(' ', $indent), $this->export($key), $value);
        }

        return sprintf("array(\n%s\n%s)", implode("\n", $php), str_repeat(' ', $indent - 4));
    }

    /**
     * Ends the class definition.
     *
     * @return string
     */
    private function endClass()
    {
        return <<<'EOF'
}

EOF;
    }

    /**
     * Wraps the service conditionals.
     *
     * @param string $value
     * @param string $code
     *
     * @return string
     */
    private function wrapServiceConditionals($value, $code)
    {
        if (!$condition = $this->getServiceConditionals($value)) {
            return $code;
        }

        // re-indent the wrapped code
        $code = implode("\n", array_map(function ($line) { return $line ? '    ' . $line : $line; }, explode("\n", $code)));

        return sprintf("        if (%s) {\n%s        }\n", $condition, $code);
    }

    /**
     * Get the conditions to execute for conditional services.
     *
     * @param string $value
     *
     * @return string|null
     */
    private function getServiceConditionals($value)
    {
        $conditions = [];
        foreach (ContainerBuilder::getInitializedConditionals($value) as $service) {
            if (!$this->container->hasDefinition($service)) {
                return 'false';
            }
            $conditions[] = sprintf("isset(\$this->services['%s'])", $service);
        }
        foreach (ContainerBuilder::getServiceConditionals($value) as $service) {
            if ($this->container->hasDefinition($service) && !$this->container->getDefinition($service)->isPublic()) {
                continue;
            }

            $conditions[] = sprintf("\$this->has('%s')", $service);
        }

        if (!$conditions) {
            return '';
        }

        return implode(' && ', $conditions);
    }

    private function getDefinitionsFromArguments(array $arguments, \SplObjectStorage $definitions = null, array &$calls = [])
    {
        if ($definitions === null) {
            $definitions = new \SplObjectStorage();
        }

        foreach ($arguments as $argument) {
            if (\is_array($argument)) {
                $this->getDefinitionsFromArguments($argument, $definitions, $calls);
            } elseif ($argument instanceof Reference) {
                $id = $this->container->normalizeId($argument);

                if (!isset($calls[$id])) {
                    $calls[$id] = [0, $argument->getInvalidBehavior()];
                } else {
                    $calls[$id][1] = min($calls[$id][1], $argument->getInvalidBehavior());
                }

                ++$calls[$id][0];
            } elseif (!$argument instanceof Definition) {
                // no-op
            } elseif (isset($definitions[$argument])) {
                $definitions[$argument] = 1 + $definitions[$argument];
            } else {
                $definitions[$argument] = 1;
                $arguments = [$argument->getArguments(), $argument->getFactory(), $argument->getProperties(), $argument->getMethodCalls(), $argument->getConfigurator()];
                $this->getDefinitionsFromArguments($arguments, $definitions, $calls);
            }
        }

        return $definitions;
    }

    /**
     * Dumps values.
     *
     * @param mixed $value
     * @param bool  $interpolate
     *
     * @throws RuntimeException
     *
     * @return string
     */
    private function dumpValue($value, $interpolate = true)
    {
        if (\is_array($value)) {
            if ($value && $interpolate && false !== $param = array_search($value, $this->container->getParameterBag()->all(), true)) {
                return $this->dumpValue("%$param%");
            }
            $code = [];
            foreach ($value as $k => $v) {
                $code[] = sprintf('%s => %s', $this->dumpValue($k, $interpolate), $this->dumpValue($v, $interpolate));
            }

            return sprintf('array(%s)', implode(', ', $code));
        } elseif ($value instanceof ArgumentInterface) {
            $scope = [$this->definitionVariables, $this->referenceVariables];
            $this->definitionVariables = $this->referenceVariables = null;

            try {
                if ($value instanceof ServiceClosureArgument) {
                    $value = $value->getValues()[0];
                    $code = $this->dumpValue($value, $interpolate);

                    if ($value instanceof TypedReference) {
                        $code = sprintf('$f = function (\\%s $v%s) { return $v; }; return $f(%s);', $value->getType(), $value->getInvalidBehavior() !== ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE ? ' = null' : '', $code);
                    } else {
                        $code = sprintf('return %s;', $code);
                    }

                    return sprintf("function () {\n            %s\n        }", $code);
                }

                if ($value instanceof IteratorArgument) {
                    $operands = [0];
                    $code = [];
                    $code[] = 'new RewindableGenerator(function () {';

                    if (!$values = $value->getValues()) {
                        $code[] = '            return new \EmptyIterator();';
                    } else {
                        $countCode = [];
                        $countCode[] = 'function () {';

                        foreach ($values as $k => $v) {
                            ($c = $this->getServiceConditionals($v)) ? $operands[] = "(int) ($c)" : ++$operands[0];
                            $v = $this->wrapServiceConditionals($v, sprintf("        yield %s => %s;\n", $this->dumpValue($k, $interpolate), $this->dumpValue($v, $interpolate)));
                            foreach (explode("\n", $v) as $v) {
                                if ($v) {
                                    $code[] = '    ' . $v;
                                }
                            }
                        }

                        $countCode[] = sprintf('            return %s;', implode(' + ', $operands));
                        $countCode[] = '        }';
                    }

                    $code[] = sprintf('        }, %s)', \count($operands) > 1 ? implode("\n", $countCode) : $operands[0]);

                    return implode("\n", $code);
                }
            } finally {
                list($this->definitionVariables, $this->referenceVariables) = $scope;
            }
        } elseif ($value instanceof Definition) {
            if ($this->definitionVariables !== null && $this->definitionVariables->contains($value)) {
                return $this->dumpValue($this->definitionVariables[$value], $interpolate);
            }
            if ($value->getMethodCalls()) {
                throw new RuntimeException('Cannot dump definitions which have method calls.');
            }
            if ($value->getProperties()) {
                throw new RuntimeException('Cannot dump definitions which have properties.');
            }
            if ($value->getConfigurator() !== null) {
                throw new RuntimeException('Cannot dump definitions which have a configurator.');
            }

            $arguments = [];
            foreach ($value->getArguments() as $argument) {
                $arguments[] = $this->dumpValue($argument);
            }

            if ($value->getFactory() !== null) {
                $factory = $value->getFactory();

                if (\is_string($factory)) {
                    return sprintf('%s(%s)', $this->dumpLiteralClass($this->dumpValue($factory)), implode(', ', $arguments));
                }

                if (\is_array($factory)) {
                    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $factory[1])) {
                        throw new RuntimeException(sprintf('Cannot dump definition because of invalid factory method (%s)', $factory[1] ?: 'n/a'));
                    }

                    $class = $this->dumpValue($factory[0]);
                    if (\is_string($factory[0])) {
                        return sprintf('%s::%s(%s)', $this->dumpLiteralClass($class), $factory[1], implode(', ', $arguments));
                    }

                    if ($factory[0] instanceof Definition) {
                        if (strpos($class, 'new ') === 0) {
                            return sprintf('(%s)->%s(%s)', $class, $factory[1], implode(', ', $arguments));
                        }

                        return sprintf("\\call_user_func(array(%s, '%s')%s)", $class, $factory[1], \count($arguments) > 0 ? ', ' . implode(', ', $arguments) : '');
                    }

                    if ($factory[0] instanceof Reference) {
                        return sprintf('%s->%s(%s)', $class, $factory[1], implode(', ', $arguments));
                    }
                }

                throw new RuntimeException('Cannot dump definition because of invalid factory');
            }

            $class = $value->getClass();
            if ($class === null) {
                throw new RuntimeException('Cannot dump definitions which have no class nor factory.');
            }

            return sprintf('new %s(%s)', $this->dumpLiteralClass($this->dumpValue($class)), implode(', ', $arguments));
        } elseif ($value instanceof Variable) {
            return '$' . $value;
        } elseif ($value instanceof Reference) {
            $id = $this->container->normalizeId($value);
            if ($this->referenceVariables !== null && isset($this->referenceVariables[$id])) {
                return $this->dumpValue($this->referenceVariables[$id], $interpolate);
            }

            return $this->getServiceCall($id, $value);
        } elseif ($value instanceof Expression) {
            return $this->getExpressionLanguage()->compile((string) $value, ['this' => 'container']);
        } elseif ($value instanceof Parameter) {
            return $this->dumpParameter($value);
        } elseif ($interpolate === true && \is_string($value)) {
            if (preg_match('/^%([^%]+)%$/', $value, $match)) {
                // we do this to deal with non string values (Boolean, integer, ...)
                // the preg_replace_callback converts them to strings
                return $this->dumpParameter($match[1]);
            }
            $replaceParameters = function ($match) {
                return "'." . $this->dumpParameter($match[2]) . ".'";
            };

            $code = str_replace('%%', '%', preg_replace_callback('/(?<!%)(%)([^%]+)\1/', $replaceParameters, $this->export($value)));

            return $code;
        } elseif (\is_object($value) || \is_resource($value)) {
            throw new RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
        }

        return $this->export($value);
    }

    /**
     * Dumps a string to a literal (aka PHP Code) class value.
     *
     * @param string $class
     *
     * @throws RuntimeException
     *
     * @return string
     */
    private function dumpLiteralClass($class)
    {
        if (strpos($class, '$') !== false) {
            return sprintf('${($_ = %s) && false ?: "_"}', $class);
        }
        if (strpos($class, "'") !== 0 || !preg_match('/^\'(?:\\\{2})?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\{2}[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*\'$/', $class)) {
            throw new RuntimeException(sprintf('Cannot dump definition because of invalid class name (%s)', $class ?: 'n/a'));
        }

        $class = substr(str_replace('\\\\', '\\', $class), 1, -1);

        return strpos($class, '\\') === 0 ? $class : '\\' . $class;
    }

    /**
     * Dumps a parameter.
     *
     * @param string $name
     *
     * @return string
     */
    private function dumpParameter($name)
    {
        if ($this->container->isCompiled() && $this->container->hasParameter($name)) {
            $value = $this->container->getParameter($name);
            $dumpedValue = $this->dumpValue($value, false);

            if (!$value || !\is_array($value)) {
                return $dumpedValue;
            }

            if (!preg_match("/\\\$this->(?:getEnv\('(?:\w++:)*+\w++'\)|targetDirs\[\d++\])/", $dumpedValue)) {
                return sprintf("\$this->parameters['%s']", $name);
            }
        }

        return sprintf("\$this->getParameter('%s')", $name);
    }

    /**
     * Gets a service call.
     *
     * @param string    $id
     * @param Reference $reference
     *
     * @return string
     */
    private function getServiceCall($id, Reference $reference = null)
    {
        while ($this->container->hasAlias($id)) {
            $id = (string) $this->container->getAlias($id);
        }
        $id = $this->container->normalizeId($id);

        if ($id === 'service_container') {
            return '$this';
        }

        // SW-21828: Symfony removed in 3.1 $this->get calls. We need for the events InitResource and AfterInitResource the get method. This adds the ->get calls back in the dumped Container.
        if ($reference !== null && $reference->getInvalidBehavior() !== ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE) {
            $code = sprintf('$this->get(\'%s\', /* ContainerInterface::NULL_ON_INVALID_REFERENCE */ %d)', $id, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        } else {
            $code = sprintf('$this->get(\'%s\')', $id);
        }

        // The following is PHP 5.5 syntax for what could be written as "(\$this->services['$id'] ?? $code)" on PHP>=7.0

        return "\${(\$_ = isset(\$this->services['$id']) ? \$this->services['$id'] : $code) && false ?: '_'}";
    }

    /**
     * Initializes the method names map to avoid conflicts with the Container methods.
     *
     * @param string $class the container base class
     */
    private function initializeMethodNamesMap($class)
    {
        $this->serviceIdToMethodNameMap = [];
        $this->usedMethodNames = [];

        if ($reflectionClass = $this->container->getReflectionClass($class)) {
            foreach ($reflectionClass->getMethods() as $method) {
                $this->usedMethodNames[strtolower($method->getName())] = true;
            }
        }
    }

    /**
     * Convert a service id to a valid PHP method name.
     *
     * @param string $id
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    private function generateMethodName($id)
    {
        if (isset($this->serviceIdToMethodNameMap[$id])) {
            return $this->serviceIdToMethodNameMap[$id];
        }

        $i = strrpos($id, '\\');
        $name = Container::camelize($i !== false && isset($id[1 + $i]) ? substr($id, 1 + $i) : $id);
        $name = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '', $name);
        $methodName = 'get' . $name . 'Service';
        $suffix = 1;

        while (isset($this->usedMethodNames[strtolower($methodName)])) {
            ++$suffix;
            $methodName = 'get' . $name . $suffix . 'Service';
        }

        $this->serviceIdToMethodNameMap[$id] = $methodName;
        $this->usedMethodNames[strtolower($methodName)] = true;

        return $methodName;
    }

    /**
     * Returns the next name to use.
     *
     * @return string
     */
    private function getNextVariableName()
    {
        $firstChars = self::FIRST_CHARS;
        $firstCharsLength = \strlen($firstChars);
        $nonFirstChars = self::NON_FIRST_CHARS;
        $nonFirstCharsLength = \strlen($nonFirstChars);

        while (true) {
            $name = '';
            $i = $this->variableCount;

            if ($name === '') {
                $name .= $firstChars[$i % $firstCharsLength];
                $i = (int) ($i / $firstCharsLength);
            }

            while ($i > 0) {
                --$i;
                $name .= $nonFirstChars[$i % $nonFirstCharsLength];
                $i = (int) ($i / $nonFirstCharsLength);
            }

            ++$this->variableCount;

            // check that the name is not reserved
            if (\in_array($name, $this->reservedVariables, true)) {
                continue;
            }

            return $name;
        }
    }

    private function getExpressionLanguage()
    {
        if ($this->expressionLanguage === null) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $providers = $this->container->getExpressionLanguageProviders();
            $this->expressionLanguage = new ExpressionLanguage(null, $providers, function ($arg) {
                $id = substr_replace($arg, '', 1, -1) === '""' ? stripcslashes(substr($arg, 1, -1)) : null;

                if ($id !== null && ($this->container->hasAlias($id) || $this->container->hasDefinition($id))) {
                    return $this->getServiceCall($id);
                }

                return sprintf('$this->get(%s)', $arg);
            });

            if ($this->container->isTrackingResources()) {
                foreach ($providers as $provider) {
                    $this->container->addObjectResource($provider);
                }
            }
        }

        return $this->expressionLanguage;
    }

    private function isHotPath(Definition $definition)
    {
        return $this->hotPathTag && $definition->hasTag($this->hotPathTag) && !$definition->isDeprecated();
    }

    private function export($value)
    {
        if ($this->targetDirRegex !== null && \is_string($value) && preg_match($this->targetDirRegex, $value, $matches, PREG_OFFSET_CAPTURE)) {
            $prefix = $matches[0][1] ? $this->doExport(substr($value, 0, $matches[0][1]), true) . '.' : '';
            $suffix = $matches[0][1] + \strlen($matches[0][0]);
            $suffix = isset($value[$suffix]) ? '.' . $this->doExport(substr($value, $suffix), true) : '';
            $dirname = $this->asFiles ? '$this->containerDir' : '__DIR__';
            $offset = 1 + $this->targetDirMaxMatches - \count($matches);

            if ($this->asFiles || $offset > 0) {
                $dirname = sprintf('$this->targetDirs[%d]', $offset);
            }

            if ($prefix || $suffix) {
                return sprintf('(%s%s%s)', $prefix, $dirname, $suffix);
            }

            return $dirname;
        }

        return $this->doExport($value, true);
    }

    private function doExport($value, $resolveEnv = false)
    {
        if (\is_string($value) && strpos($value, "\n") !== false) {
            $cleanParts = explode("\n", $value);
            $cleanParts = array_map(function ($part) { return var_export($part, true); }, $cleanParts);
            $export = implode('."\n".', $cleanParts);
        } else {
            $export = var_export($value, true);
        }

        if ($resolveEnv && $export[0] === "'" && $export !== $resolvedExport = $this->container->resolveEnvPlaceholders($export, "'.\$this->getEnv('string:%s').'")) {
            $export = $resolvedExport;
            if (substr($export, -3) === ".''") {
                $export = substr($export, 0, -3);
                if ($export[1] === "'") {
                    $export = substr_replace($export, '', 18, 7);
                }
            }
            if ($export[1] === "'") {
                $export = substr($export, 3);
            }
        }

        return $export;
    }
}