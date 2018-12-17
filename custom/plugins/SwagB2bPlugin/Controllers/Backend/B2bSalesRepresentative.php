<?php declare(strict_types=1);

class Shopware_Controllers_Backend_B2bSalesRepresentative extends Shopware_Controllers_Backend_ExtJs
{
    public function clientListAction()
    {
        $salesRepresentativeId = (int) $this->Request()->getParam('sales_representative_id');

        if (!$salesRepresentativeId) {
            throw new InvalidArgumentException('no Id given');
        }

        $clients = $this->container->get('b2b_sales_representative.backend_extension')
            ->clientList($salesRepresentativeId);

        $this->View()->assign([
            'success' => true, 'data' => $clients,
        ]);
    }

    public function saveClientsAction()
    {
        $salesRepresentativeId = (int) $this->Request()->getParam('sales_representative_id');

        if (!$salesRepresentativeId) {
            throw new InvalidArgumentException('no Id given');
        }

        $clientIds = (array) json_decode($this->Request()->getParam('clients'));

        $this->container->get('b2b_sales_representative.backend_extension')
            ->saveClients($salesRepresentativeId, $clientIds);
    }
}
