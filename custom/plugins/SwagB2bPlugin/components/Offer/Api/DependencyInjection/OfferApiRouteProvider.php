<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class OfferApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/offer',
                'b2b_offer.api_offer_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/offer/{offerId}',
                'b2b_offer.api_offer_controller',
                'update',
                ['debtorEmail', 'offerId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/offer/{offerId}',
                'b2b_offer.api_offer_controller',
                'remove',
                ['debtorEmail', 'offerId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/offer/{offerId}',
                'b2b_offer.api_offer_controller',
                'get',
                ['debtorEmail', 'offerId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/offer/{offerId}/updateExpiredDate',
                'b2b_offer.api_offer_controller',
                'updateOfferExpiredDate',
                ['debtorEmail', 'offerId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/offer/{offerId}/accept',
                'b2b_offer.api_offer_controller',
                'accept',
                ['debtorEmail', 'offerId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/offer/{offerId}/decline',
                'b2b_offer.api_offer_controller',
                'declineOffer',
                ['debtorEmail', 'offerId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/offer/{offerId}/items',
                'b2b_offer.api_offer_line_item_reference_controller',
                'addItems',
                ['debtorEmail', 'offerId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/offer/{offerId}/items',
                'b2b_offer.api_offer_line_item_reference_controller',
                'removeItems',
                ['debtorEmail', 'offerId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/offer/{offerId}/items',
                'b2b_offer.api_offer_line_item_reference_controller',
                'updateItems',
                ['debtorEmail', 'offerId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/offer/{offerId}/items',
                'b2b_offer.api_offer_line_item_reference_controller',
                'getItems',
                ['debtorEmail', 'offerId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/offer/{offerId}/log',
                'b2b_offer.api_offer_log_controller',
                'log',
                ['debtorEmail', 'offerId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/offer/{offerId}/comment',
                'b2b_offer.api_offer_log_controller',
                'commentList',
                ['debtorEmail', 'offerId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/offer/{offerId}/comment',
                'b2b_offer.api_offer_log_controller',
                'comment',
                ['debtorEmail', 'offerId'],
            ],
        ];
    }
}
