<?php declare(strict_types=1);

class Shopware_Controllers_Backend_B2bDebtor extends Shopware_Controllers_Backend_ExtJs
{
    public function userListAction()
    {
        $debtorId = (int) $this->Request()->getParam('debtor_id');

        if (!$debtorId) {
            throw new InvalidArgumentException('no Id given');
        }

        $users = $this->container->get('b2b_dashboard.debtor_backend_extension')
            ->getUserList($debtorId);

        $this->View()->assign([
            'success' => true, 'data' => $users,
        ]);
    }

    public function updateUserAction()
    {
        $userId = (int) $this->Request()->getParam('id');
        $emotionId = (int) $this->Request()->getParam('emotion');

        $this->container->get('b2b_dashboard.debtor_backend_extension')
            ->updateUser($userId, $emotionId);
    }

    public function getAllEmotionsAction()
    {
        $emotions = $this->container->get('b2b_dashboard.debtor_backend_extension')
            ->getAllEmotions();

        array_unshift($emotions, ['id' => 0, 'name' => '-']);

        $this->View()->assign([
            'success' => true, 'data' => $emotions,
        ]);
    }
}
