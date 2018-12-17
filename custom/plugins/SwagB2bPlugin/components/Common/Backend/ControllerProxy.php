<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Backend;

use Shopware\B2B\Common\B2BException;
use Shopware\B2B\Common\Controller\ControllerProxyInterface;
use Shopware\B2B\Common\MvcExtension\EnlightRequest;
use Shopware\B2B\Common\MvcExtension\RoutingInterceptor;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware_Controllers_Backend_ExtJs;
use Symfony\Component\Validator\ConstraintViolation;

abstract class ControllerProxy extends Shopware_Controllers_Backend_ExtJs implements ControllerProxyInterface
{
    /**
     * @return string
     */
    abstract protected function getControllerDiKey(): string;

    /**
     * @return object
     */
    protected function getController()
    {
        return $this->get($this->getControllerDiKey());
    }

    /**
     * @param string $action
     */
    public function dispatch($action)
    {
        (new RoutingInterceptor())->interceptException(
            $this,
            function () use ($action) {
                parent::dispatch($action);
            }
        );
    }

    /**
     * @param string $name
     * @param null $value
     */
    public function __call($name, $value = null)
    {
        $controller = $this->getController();
        $isAction = substr($name, -6) === 'Action';
        $controllerHasAction = method_exists($controller, $name);

        if ($isAction && $controllerHasAction) {
            try {
                $viewAssigns = $controller->{$name}(new EnlightRequest($this->Request()));
                $viewAssigns['success'] = true;
            } catch (B2BException $e) {
                $viewAssigns['success'] = false;
                $viewAssigns['error'] = $e->getMessage();

                if ($e instanceof ValidationException) {
                    $viewAssigns['error'] = $this->getMessageFromValidationMessage($e);
                }
            }

            $this->View()->assign($viewAssigns);

            return;
        }
    }

    /**
     * @TODO maybe refactor or replace
     * @internal
     * @param ValidationException $validationException
     * @return array
     */
    protected function getMessageFromValidationMessage(ValidationException $validationException): array
    {
        $snippetManager = $this->get('snippets')->getNamespace('frontend/plugins/b2b_debtor_plugin');

        $errors = [];
        /** @var ConstraintViolation $violation */
        foreach ($validationException->getViolations() as $violation) {
            $message = '';

            if ($violation->getPropertyPath()) {
                $message .= $snippetManager->get(ucfirst($violation->getPropertyPath())) . ' : ';
            }

            $message .= $snippetManager->get(str_replace(['}', '{', '%', '.', ' '], '', $violation->getMessageTemplate()));

            foreach ($violation->getParameters() as $component => $route) {
                $routeTranslated = $route;

                if (!is_numeric($routeTranslated) && $violation->getCause() !== 'isProduct') {
                    $routeTranslated = $snippetManager->get('errorMessage' . ucfirst($routeTranslated));
                }

                $message = str_replace($component, $routeTranslated, $message);
            }

            $errors[] = $message;
        }

        return $errors;
    }
}
