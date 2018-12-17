<?php declare(strict_types=1);

use Shopware\B2B\Cart\Framework\InformationMessage;

/**
 * Renders sub requests for the B2B suite. As this component is used catching contingent rules and restrictions
 * which will in default be displayed as shopware success template component.
 *
 * @param $params
 * @return false|string
 */
function smarty_function_b2b_contingent_information($params)
{
    $snippetNamespace = Shopware()->Snippets()->getNamespace('frontend/plugins/b2b_debtor_plugin');
    $template = Shopware()->Template();

    $informationList  = array_filter($params['information'], function ($information) use ($params) {
        foreach ($params['errors'] as $error) {
            if ($error->params['identifier'] === $information->params['identifier']) {
                return false;
            }
        }

        return true;
    });

    $templateInformation = [];
    /** @var InformationMessage $information */
    foreach ($informationList as $information) {
        $allowedValue = $information->params['allowedValue'];

        $currencyValueClasses = [
            \Shopware\B2B\ContingentRule\Framework\TimeRestrictionType\OrderAmountAccessStrategy::class,
            \Shopware\B2B\ContingentRule\Framework\ProductPriceType\ProductPriceAccessStrategy::class,
        ];

        if (in_array($information->sender, $currencyValueClasses, true)) {
            $allowedValue = $template->fetch('string: {"' . $allowedValue . '"|currency}');
        }

        $templateInformation[] = sprintf(
            $snippetNamespace->get($information->type),
            $snippetNamespace->get($information->params['cartHistory']->timeRestriction),
            $allowedValue
        );
    }

    if (!$templateInformation) {
        return false;
    }

    $type = 'success';
    if ($params['type']) {
        $type = $params['type'];
    }

    $template->assign([
        'type' => $type,
        'b2bContentList' => $templateInformation,
    ]);

    return $template->fetch('frontend/_includes/messages.tpl');
}
