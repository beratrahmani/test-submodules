<?php declare(strict_types=1);

/**
 * Renders sub requests for the B2B suite. As this component is used for catching contingent errors
 * which will be displayed as shopware error template component.
 *
 * @param array $params
 * @return string|false
 */
function smarty_function_b2b_error($params)
{
    $snippetNamespace = Shopware()->Snippets()->getNamespace('frontend/plugins/b2b_debtor_plugin');
    $template = Shopware()->Template();

    $errors = [];
    /** @var \Shopware\B2B\Cart\Framework\ErrorMessage $error */
    foreach ($params['list'] as $error) {
        $allowedValue = $error->params['allowedValue'];

        $currencyValueClasses = [
            \Shopware\B2B\ContingentRule\Framework\TimeRestrictionType\OrderAmountAccessStrategy::class,
            \Shopware\B2B\ContingentRule\Framework\ProductPriceType\ProductPriceAccessStrategy::class,
        ];

        if (in_array($error->sender, $currencyValueClasses, true)) {
            $allowedValue = $template->fetch('string: {"' . $allowedValue . '"|currency}');
        }

        $errors[] = sprintf(
            $snippetNamespace->get($error->error),
            $snippetNamespace->get($error->params['cartHistory']->timeRestriction),
            $allowedValue
        );
    }

    if (!$errors) {
        return false;
    }

    $template->assign([
        'type' => 'error',
        'b2bContentList' => $errors,
    ]);

    return $template->fetch('frontend/_includes/messages.tpl');
}
