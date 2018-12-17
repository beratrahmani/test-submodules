<?php

/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Form\Field;
use Shopware\Models\Form\Form;
use Shopware\Models\Media\Album;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use Shopware\Models\User\User;
use Shopware_Components_Translation as Translator;
use SwagTicketSystem\Components\CreateAnswerAdapterInterface;
use SwagTicketSystem\Components\TicketPdfInterface;
use SwagTicketSystem\Models\Ticket\File;
use SwagTicketSystem\Models\Ticket\Mail;
use SwagTicketSystem\Models\Ticket\Repository;
use SwagTicketSystem\Models\Ticket\Status;
use SwagTicketSystem\Models\Ticket\Support;
use SwagTicketSystem\Models\Ticket\Type;

class Shopware_Controllers_Backend_Ticket extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * @var array
     */
    protected $blackList = [
        'php',
        'php3',
        'php4',
        'php5',
        'phtml',
        'cgi',
        'pl',
        'sh',
        'com',
        'bat',
        '',
        'py',
        'rb',
        'exe',
    ];

    /**
     * @var ModelManager
     */
    private $manager;

    public function preDispatch()
    {
        $this->manager = $this->container->get('models');

        parent::preDispatch();
    }

    /**
     * read the widgetList and assign it to the view.
     */
    public function getWidgetListAction()
    {
        $currentUserId = (int) $this->getEmployee()->id;
        $statusId = (int) $this->Request()->getParam('statusId');
        $order = $this->Request()->getParam('sort', [['property' => 'lastContact', 'direction' => 'ASC']]);

        /** @var $repository Repository */
        $repository = $this->getManager()->getRepository(Support::class);
        $dataQuery = $repository->getWidgetQuery($statusId, $currentUserId, $order);

        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(
            [
                'success' => true,
                'data' => $data,
                'total' => $totalCount,
            ]
        );
    }

    /**
     * returns a JSON string to with all found items for the backend listing
     */
    public function getListAction()
    {
        try {
            $result = $this->getListData();
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * returns a JSON string to with all found items for the backend listing
     */
    public function getStatusListAction()
    {
        /** @var $repository Repository */
        $repository = $this->getManager()->getRepository(Support::class);
        $dataQuery = $repository->getStatusListQuery();
        $data = $dataQuery->getArrayResult();
        $localeId = (int) $this->get('Auth')->getIdentity()->locale->getId();

        if ($localeId !== 1) {
            $data = $this->getStatusTranslations($data, $localeId);
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * returns a JSON string to with all found item for the backend listing
     */
    public function getMailListAction()
    {
        $locale = (int) $this->Request()->getParam('locale');
        $onlyCustomSubmissions = (bool) $this->Request()->getParam('onlyCustomSubmissions');
        $onlyDefaultSubmission = (bool) $this->Request()->getParam('onlyDefaultSubmission');

        /** @var $repository Repository */
        $repository = $this->getManager()->getRepository(Support::class);
        $dataQuery = $repository->getMailListQuery($locale, $onlyCustomSubmissions, $onlyDefaultSubmission);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalCount]);
    }

    /**
     * returns a JSON string to with all found item for the backend listing
     */
    public function getCustomerListAction()
    {
        $limit = (int) $this->Request()->getParam('limit');
        $offset = (int) $this->Request()->getParam('start');
        $filter = $this->Request()->getParam('query', null);
        $connection = $this->get('dbal_connection');

        $queryBuilder = $connection->createQueryBuilder();
        $totalCount = $queryBuilder->select('COUNT(id)')
            ->from('s_user')
            ->execute()
            ->fetchColumn();

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->select(['id', 'firstname', 'lastname'])
            ->from('s_user')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($filter !== null) {
            $queryBuilder->andWhere('firstname LIKE :filter')
                ->orWhere('lastname LIKE :filter')
                ->setParameter('filter', '%' . $filter . '%');
        }

        $data = $queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalCount]);
    }

    /**
     * returns a JSON string with all backend users, which have rights on the resource 'ticket'
     */
    public function getEmployeeListAction()
    {
        $dataQuery = $this->getUserQuery();
        $data = $dataQuery->getArrayResult();
        $finaldata = [];
        foreach ($data as $empl) {
            if ($this->get('acl')->isAllowed($empl['roleName'], 'ticket')) {
                $finaldata[] = $empl;
            }
        }
        $this->View()->assign(['success' => true, 'data' => $finaldata]);
    }

    /**
     * Returns the list of ticket types
     */
    public function getTicketTypesAction()
    {
        $limit = (int) $this->Request()->getParam('limit');
        $offset = (int) $this->Request()->getParam('start');

        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', []);

        //order data
        $order = (array) $this->Request()->getParam('sort', []);

        /** @var $repository Repository */
        $repository = $this->getManager()->getRepository(Support::class);
        $dataQuery = $repository->getTicketTypeListQuery($filter, $order, $offset, $limit);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalCount]);
    }

    /**
     * Shops that have already email submission which can be deleted
     */
    public function getShopsWithSubmissionsAction()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['shops.id', 'shops.name'])
            ->from(Mail::class, 'mail')
            ->leftJoin('mail.shop', 'shops')
            ->groupBy('shops');
        $dataQuery = $builder->getQuery();
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Shops that haven't  email submission which can be added
     */
    public function getShopsWithOutSubmissionsAction()
    {
        $sql = <<<SQL
SELECT shops.id AS id, shops.name AS name
FROM `s_core_shops` AS shops
WHERE id NOT IN (
  SELECT DISTINCT shop_id AS id
  FROM `s_ticket_support_mails`
)
SQL;
        $data = $this->get('dbal_connection')->fetchAll($sql);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Returns the form data for the form mapping
     */
    public function getFormsAction()
    {
        $manager = $this->getManager();
        $builder = $manager->createQueryBuilder();
        $builder->select(['forms', 'fields'])
            ->from(Form::class, 'forms')
            ->leftJoin('forms.fields', 'fields')
            ->orderBy('forms.name', 'ASC');
        $dataQuery = $builder->getQuery();
        $totalCount = $manager->getQueryCount($dataQuery);
        $data = $builder->getQuery()->getArrayResult();
        $formMapping = [
            'message' => 0,
            'subject' => 0,
            'author' => 0,
            'email' => 0,
        ];

        foreach ($data as &$form) {
            foreach ($form['fields'] as $field) {
                $ticketTask = $field['ticketTask'];
                if (!empty($ticketTask) && array_key_exists($ticketTask, $formMapping)) {
                    $formMapping[$ticketTask] = $field['id'];
                }
            }
            $form['mapping'] = $formMapping;
        }
        unset($form);

        $result = $this->checkMappingFields($data);

        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $totalCount]);
    }

    /**
     * Remove mapping from form if mapping dont have mapped fields
     * or ticket type dont exist
     *
     * @param array $data
     *
     * @return array
     */
    public function checkMappingFields($data)
    {
        foreach ($data as &$form) {
            $ticketType = $this->getManager()->getRepository(Type::class)->find($form['ticketTypeid']);
            if (!$ticketType) {
                unset($form['ticketTypeid']);
            }

            foreach ($form['mapping'] as &$mapping) {
                $existingId = null;
                foreach ($form['fields'] as $field) {
                    if ($mapping == $field['id']) {
                        $existingId = $field['id'];
                    }
                }

                $mapping = 0;
                if ($existingId) {
                    $mapping = $existingId;
                }
            }
        }

        return $data;
    }

    /**
     * save the the mapping form data
     */
    public function updateFormAction()
    {
        try {
            $formId = (int) $this->Request()->getParam('id');
            $ticketTypeId = (int) $this->Request()->getParam('ticketTypeid');
            $manager = $this->getManager();

            $formRepository = $manager->getRepository(Form::class);
            $fieldModel = $formRepository->find($formId);
            if (!empty($fieldModel) && is_object($fieldModel)) {
                $fieldModel->setTicketTypeid($ticketTypeId);
                $manager->persist($fieldModel);
                $manager->flush();
            }
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * save the mapping of the form
     */
    public function saveMappingAction()
    {
        $manager = $this->getManager();
        //delete the previous selected items
        $builder = $manager->createQueryBuilder();
        $builder->update(Field::class, 'formField')
            ->set('formField.ticketTask', $builder->expr()->literal(''))
            ->where('formField.formId = :id')
            ->setParameter('id', $this->Request()->getParam('formId'));
        $builder->getQuery()->execute();

        try {
            $mapping = [
                'message' => (int) $this->Request()->getParam('message'),
                'subject' => (int) $this->Request()->getParam('subject'),
                'author' => (int) $this->Request()->getParam('author'),
                'email' => (int) $this->Request()->getParam('email'),
            ];

            $formFieldRepository = $manager->getRepository(Field::class);

            foreach ($mapping as $key => $value) {
                if (!empty($value)) {
                    $fieldModel = $formFieldRepository->find($value);
                    if (!empty($fieldModel) && is_object($fieldModel)) {
                        $fieldModel->setTicketTask($key);
                        $manager->persist($fieldModel);
                    }
                }
            }
            $manager->flush();

            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Updates ticket status
     */
    public function updateTicketAction()
    {
        $ticketId = (int) $this->Request()->getParam('id');
        $employeeId = (int) $this->Request()->getParam('employeeId');
        $statusId = (int) $this->Request()->getParam('statusId');
        try {
            $manager = $this->getManager();
            /** @var Support $ticketModel */
            $ticketModel = $manager->getRepository(Support::class)->find($ticketId);
            $ticketModel->setEmployeeId($employeeId);
            if (!empty($statusId)) {
                /** @var Status $statusModel */
                $statusModel = $manager->getRepository(Status::class)->find($statusId);
                if (!empty($statusModel) && is_object($statusModel)) {
                    $ticketModel->setStatus($statusModel);
                }
            }

            $manager->persist($ticketModel);
            $manager->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Submits the answer to the Customer
     */
    public function answerTicketAction()
    {
        $mailData = $this->Request()->getParams();
        $mediaData = $this->Request()->getParam('media-manager-selection');

        /* @var CreateAnswerAdapterInterface $adapter */
        $adapter = $this->get('swag_ticket_system.create_answer_adapter');

        //returns the link to the frontend which can be used by the unregistered user to view the ticket
        $ticketUniqueLink = $this->getTicketUniqueLink((int) $mailData['id']);

        //Check for selected attachments in ticket answer
        if (!$mediaData) {
            $attachments = [];
        } else {
            $attachments = explode(',', $mediaData);
        }

        try {
            $adapter->createAnswer(
                $mailData,
                $ticketUniqueLink,
                $attachments
            );
        } catch (RuntimeException $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Creates a new ticket type
     */
    public function createTicketTypeAction()
    {
        $this->saveTicketType();
    }

    /**
     * Creates a new ticket type
     */
    public function updateTicketTypeAction()
    {
        $this->saveTicketType();
    }

    /**
     * create a new mail submission
     */
    public function createMailAction()
    {
        $this->saveTicketMail();
    }

    /**
     * updates a mail submission
     */
    public function updateMailAction()
    {
        $this->saveTicketMail();
    }

    /**
     * Deletes a ticket mail from the database
     */
    public function destroyMailAction()
    {
        try {
            $manager = $this->getManager();
            /** @var $model Support */
            $model = $manager->getRepository(Mail::class)->find($this->Request()->getParam('id'));
            $manager->remove($model);
            $manager->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * duplicates ticket mail submissions from the database
     */
    public function duplicateMailsAction()
    {
        $baseShopId = (int) $this->Request()->getParam('baseShopId');
        $newShopId = (int) $this->Request()->getParam('newShopId');
        $duplicateIndividualSubmissions = (int) $this->Request()->getParam('duplicateIndividualSubmissions');

        if (!empty($baseShopId) && !empty($newShopId)) {
            $sqlPart = '';
            if (empty($duplicateIndividualSubmissions)) {
                $sqlPart = 'AND sys_dependent = 1';
            }
            $sql = ' INSERT INTO s_ticket_support_mails (
                    name, description, frommail, fromname, subject, content, contentHTML, ishtml,
                    attachment, sys_dependent, isocode, shop_id
                )
                SELECT name, description, frommail, fromname, subject, content, contentHTML, ishtml,
                    attachment, sys_dependent, isocode, ?
                FROM  s_ticket_support_mails WHERE shop_id = ?' . $sqlPart;
            $this->get('dbal_connection')->executeQuery($sql, [$newShopId, $baseShopId]);

            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        }
    }

    /**
     * deletes the mail submissions based on the shop id
     */
    public function deleteMailSubmissionByShopIdAction()
    {
        try {
            $shopId = (int) $this->Request()->getParam('shopId');
            $dql = 'DELETE SwagTicketSystem\Models\Ticket\Mail mail WHERE mail.shopId = ?1';
            $query = $this->getManager()->createQuery($dql);
            $query->setParameter(1, $shopId);
            $query->execute();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
        $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
    }

    /**
     * Deletes a ticket from the database
     */
    public function destroyTicketAction()
    {
        try {
            $manager = $this->getManager();
            /** @var $model Support */
            $model = $manager->getRepository(Support::class)->find($this->Request()->getParam('id'));
            $manager->remove($model);
            $manager->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Deletes a ticket type  from the database
     */
    public function destroyTicketTypeAction()
    {
        try {
            $manager = $this->getManager();
            /** @var $model Type */
            $model = $manager->getRepository(Support::class)->find($this->Request()->getParam('id'));
            $manager->remove($model);
            $manager->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * reads the ticket history
     */
    public function getTicketHistoryAction()
    {
        $ticketId = (int) $this->Request()->getParam('id');

        try {
            $dataQuery = $this->getManager()->getRepository(Support::class)->getTicketHistoryQuery($ticketId);
            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Redirect to the frontend to the right form for ticket creation
     */
    public function redirectToFormAction()
    {
        $formId = (int) $this->Request()->getParam('formId');
        $customerId = (int) $this->Request()->getParam('customerId');

        if (!empty($formId)) {
            /* @var ShopRepository $repository */
            $repository = $this->getManager()->getRepository(Shop::class);
            $shop = $repository->getActiveDefault();
            $shop->registerResources();
            $this->getManager()->clear();

            $params = [
                'module' => 'frontend',
                'controller' => 'ticket',
                'sFid' => $formId,
            ];

            if (!empty($customerId)) {
                $params['userData'] = $this->getUserData($customerId);
            }

            $url = $this->Front()->Router()->assemble($params);
            $this->redirect($url);
        }
    }

    /**
     * Function to get ticket for the customer
     */
    public function getTicketsForCustomerAction()
    {
        try {
            $limit = (int) $this->Request()->getParam('limit');
            $offset = (int) $this->Request()->getParam('start');
            $statusId = (int) $this->Request()->getParam('statusId');

            /** @var $filter array */
            $filter = $this->Request()->getParam('filter', []);

            $customerId = $this->Request()->getParam('customerID');
            if ($customerId === null || $customerId === 0) {
                $this->View()->assign(['success' => false, 'message' => 'No customer id passed']);

                return;
            }

            //order data
            $order = (array) $this->Request()->getParam(
                'sort',
                [
                    [
                        'property' => 'ticket.id',
                        'direction' => 'DESC',
                    ],
                ]
            );

            /** @var $repository Repository */
            $repository = $this->getManager()->getRepository(Support::class);

            $dataQuery = $repository->getCustomerTicketListQuery(
                $customerId,
                $statusId,
                $order,
                $offset,
                $limit,
                $filter
            );

            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            foreach ($data as &$listItem) {
                if (!empty($listItem['additional'])) {
                    $listItem['additional'] = unserialize($listItem['additional']);
                }
            }

            $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Print current ticket information and answers for the ticket only if ticket exists
     */
    public function printAction()
    {
        $manager = $this->getManager();
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $ticketId = (int) $this->Request()->getParam('ticketId');

        $detailQuery = $manager->getRepository(Support::class)->getTicketDetailQueryById($ticketId);
        $detailData = $detailQuery->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        if (!$detailData) {
            return;
        }

        $detailData['additional'] = unserialize($detailData['additional']);

        $dataQuery = $manager->getRepository(Support::class)->getTicketHistoryQuery($ticketId);
        $ticketHistoryData = $dataQuery->getArrayResult();

        foreach ($ticketHistoryData as &$ticketAnswer) {
            $attachments = $manager->getRepository(Support::class)->findBy(['answerId' => $ticketAnswer['id']]);
            if ($attachments) {
                /** @var File $attachment */
                foreach ($attachments as $attachment) {
                    $ticketAnswer['attachment'][] = $attachment->getName();
                }
            }
        }

        /** @var TicketPdfInterface $pdf */
        $pdf = $this->get('swag_ticket_system.ticket_pdf');
        $pdf->downloadPdf($ticketId, $detailData, $ticketHistoryData);
    }

    /**
     * Get attachments for current ticket answer
     */
    public function getAttachmentsAction()
    {
        $answerId = $this->Request()->getParam('answerId');

        $data = [];
        $attachments = $this->getManager()->getRepository(File::class)->findBy(['answerId' => $answerId]);

        $mediaManager = $this->container->get('shopware_media.media_service');

        /** @var File $attachment */
        foreach ($attachments as $attachment) {
            $data[] = [
                'attachment' => $mediaManager->getUrl($attachment->getName()),
                'hash' => $attachment->getHash(),
                'location' => $attachment->getLocation(),
            ];
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Get attachment for current ticket support
     * which are not part of any ticket answer
     */
    public function getFilesAction()
    {
        $ticketId = $this->Request()->getParam('ticketId');

        $data = [];
        $attachments = $this->getManager()->getRepository(File::class)->findBy(['ticketId' => $ticketId]);

        $mediaService = $this->container->get('shopware_media.media_service');

        if ($attachments) {
            /** @var File $attachment */
            foreach ($attachments as $attachment) {
                $data[] = [
                    'attachment' => $mediaService->getUrl($attachment->getName()),
                    'hash' => $attachment->getHash(),
                    'location' => $attachment->getLocation(),
                ];
            }
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete attachment for current ticket support
     */
    public function deleteFileAction()
    {
        $ticketId = $this->Request()->getParam('ticketId');
        $hashId = $this->Request()->getParam('hash');

        try {
            $manager = $this->getManager();
            $answer = $manager->getRepository(File::class)->findOneBy(['ticketId' => $ticketId, 'hash' => $hashId]);
            if ($answer) {
                $manager->remove($answer);
                $manager->flush();

                $this->View()->assign(['success' => true]);
            }
        } catch (Exception $ex) {
            $this->View()->assign(['success' => false]);
        }
    }

    /**
     * Delete attachment from current ticket answer
     */
    public function deleteAttachmentAction()
    {
        $hashId = $this->Request()->getParam('hash');

        try {
            $manager = $this->getManager();
            $answer = $manager->getRepository(File::class)->findOneBy(['hash' => $hashId]);
            if ($answer) {
                $manager->remove($answer);
                $manager->flush();

                $this->View()->assign(['success' => true]);
            }
        } catch (Exception $ex) {
            $this->View()->assign(['success' => false]);
        }
    }

    /**
     * Download attachment from current ticket answer
     */
    public function downloadAttachmentAction()
    {
        $hashId = $this->Request()->getParam('hash');

        $attachment = $this->getManager()->getRepository(File::class)->findOneBy(['hash' => $hashId]);
        if ($attachment) {
            /** @var \Shopware\Bundle\MediaBundle\MediaService $mediaService */
            $mediaService = $this->get('shopware_media.media_service');
            $fileName = $mediaService->getUrl($attachment->getName());
            $fileName = $mediaService->encode($fileName);

            $response = $this->Response();
            $response->setHeader('Cache-Control', 'public');
            $response->setHeader('Content-Description', 'File Transfer');
            $response->setHeader('Content-disposition', 'attachment; filename=' . basename($fileName));
            $response->setHeader('Content-Transfer-Encoding', 'binary');
            $response->setHeader('Content-Length', filesize($fileName));
            $response->sendHeaders();

            readfile($fileName);
        }

        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);
    }

    /**
     * Update isRead by user
     */
    public function updateIsReadAction()
    {
        $manager = $this->getManager();
        /** @var $model Support */
        $ticket = $manager->getRepository(Support::class)->find($this->Request()->getParam('id'));

        $isRead = $ticket->getIsRead();
        if ($isRead) {
            $ticket->setIsRead(0);
        } else {
            $ticket->setIsRead(1);
        }
        $manager->flush($ticket);
    }

    /**
     * Assign the userId to the view
     */
    public function getCurrentEmployeeIdAction()
    {
        $user = $this->getEmployee();
        $this->View()->assign(['success' => true, 'userID' => $user->id]);
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'redirectToForm',
            'print',
            'downloadAttachment',
        ];
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * read permissions
         */
        $this->addAclPermission('getCustomerList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getEmployeeList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getForms', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getMailList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getShopsWithOutSubmissions', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getShopsWithSubmissions', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getStatusList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTicketHistory', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTicketTypes', 'read', 'Insufficient Permissions');

        /*
         * update permissions
         */
        $this->addAclPermission('answerTicket', 'update', 'Insufficient Permissions');
        $this->addAclPermission('saveMapping', 'update', 'Insufficient Permissions');
        $this->addAclPermission('updateForm', 'update', 'Insufficient Permissions');
        $this->addAclPermission('updateMail', 'update', 'Insufficient Permissions');
        $this->addAclPermission('updateTicket', 'update', 'Insufficient Permissions');
        $this->addAclPermission('updateTicketType', 'update', 'Insufficient Permissions');

        /*
         * create permissions
         */
        $this->addAclPermission('createMail', 'create', 'Insufficient Permissions');
        $this->addAclPermission('createTicketType', 'create', 'Insufficient Permissions');
        $this->addAclPermission('duplicateMails', 'create', 'Insufficient Permissions');
        $this->addAclPermission('redirectToForm', 'create', 'Insufficient Permissions');

        /*
         * delete permissions
         */
        $this->addAclPermission('deleteMailSubmissionByShopId', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('destroyMail', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('destroyTicket', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('destroyTicketType', 'delete', 'Insufficient Permissions');
    }

    /**
     * Creates or updates a new ticket type
     */
    protected function saveTicketType()
    {
        $params = $this->Request()->getParams();

        $id = $this->Request()->getParam('id');

        if (!empty($id)) {
            //edit Data
            $typeModel = $this->getManager()->getRepository(Type::class)->find($id);
        } else {
            //new Data
            $typeModel = new Type();
        }
        $typeModel->fromArray($params);

        try {
            $this->getManager()->persist($typeModel);
            $this->getManager()->flush();

            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper method which returns the unique link for the system mail to the frontend
     * This link will be used by the unregistered user to view the ticket answer
     *
     * @param int $ticketId
     *
     * @return string|null
     */
    private function getTicketUniqueLink($ticketId)
    {
        $manager = $this->getManager();
        /** @var Support $ticketModel */
        $ticketModel = $manager->getRepository(Support::class)->find($ticketId);

        if (!$ticketModel) {
            return null;
        }
        $uniqueId = $ticketModel->getUniqueId();
        $shopId = $ticketModel->getShop()->getId();

        /* @var ShopRepository $repository */
        $repository = $manager->getRepository(Shop::class);
        $shop = $repository->getActiveById($shopId);
        $shop->registerResources();
        $manager->clear();

        $link = $this->Front()->Router()->assemble(
            [
                'module' => 'frontend',
                'controller' => 'ticket',
                'action' => 'detail',
                'sAID' => $uniqueId,
            ]
        );

        return $link;
    }

    /**
     * Creates or updates a mail submission
     */
    private function saveTicketMail()
    {
        $params = $this->Request()->getParams();
        $manager = $this->getManager();

        $id = (int) $this->Request()->getParam('id');

        if (!empty($id)) {
            //edit Data
            $ticketMailModel = $manager->getRepository(Mail::class)->find($id);
        } else {
            //new Data
            $ticketMailModel = new Mail();
        }

        $ticketMailModel->fromArray($params);

        $shopRepository = $manager->getRepository(Shop::class);
        $shop = $shopRepository->find($params['shopId']);
        if (!empty($shop) && is_object($shop)) {
            $ticketMailModel->setShop($shop);
        } else {
            $shop = $shopRepository->find(1);
            $ticketMailModel->setShop($shop);
        }

        try {
            $manager->persist($ticketMailModel);
            $manager->flush();

            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper method to get all the necessary data.
     *
     * @return array
     */
    private function getListData()
    {
        $limit = (int) $this->Request()->getParam('limit');
        $offset = (int) $this->Request()->getParam('start');
        $userId = !$this->Request()->has('userId') ? null : (int) $this->Request()->getParam('userId');
        $statusId = (int) $this->Request()->getParam('statusId');

        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', []);

        //order data
        $order = (array) $this->Request()->getParam(
            'sort',
            [
                [
                    'property' => 'ticket.id',
                    'direction' => 'DESC',
                ],
            ]
        );

        $album = $this->getManager()->getRepository(Album::class)
            ->findOneBy(['name' => 'TicketAttachment']);

        /** @var $repository Repository */
        $repository = $this->getManager()->getRepository(Support::class);
        $dataQuery = $repository->getListQuery($userId, $statusId, $filter, $order, $offset, $limit);

        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        foreach ($data as &$listItem) {
            $listItem['albumId'] = $album->getId();
            if (!empty($listItem['additional'])) {
                $listItem['additional'] = unserialize($listItem['additional']);
            }
        }

        return ['success' => true, 'data' => $data, 'total' => $totalCount];
    }

    /**
     * returns query for backend user
     */
    private function getUserQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(
            [
                'user.id as id',
                'role.name as roleName',
                'user.name as name',
            ]
        );
        $builder->from(User::class, 'user');
        $builder->join('user.role', 'role');

        return $builder->getQuery();
    }

    /**
     * @return mixed|null
     */
    private function getEmployee()
    {
        /** @var $auth Shopware_Components_Auth */
        $auth = $this->get('auth');

        return $auth->getIdentity();
    }

    /**
     * Helper method to read all the import user-data for the frontend-form.
     *
     * @param $customerId
     *
     * @return array
     */
    private function getUserData($customerId)
    {
        $sql = <<<SQL
SELECT email
FROM `s_user` AS u
LEFT JOIN s_user_billingaddress AS ub ON ( u.id = ub.userID )
WHERE u.id = :customerId
SQL;

        return $this->get('dbal_connection')->executeQuery($sql, ['customerId' => $customerId])->fetch();
    }

    /**
     * Returns the translations for each status, if any available.
     *
     * @param array $statusArray
     * @param int   $localeId
     *
     * @return array
     */
    private function getStatusTranslations(array $statusArray, $localeId)
    {
        $translationCmp = new Translator();
        $fallbackLocaleId = 2;

        foreach ($statusArray as &$status) {
            $statusTranslation = $translationCmp->read($localeId, 'ticketStatus', $status['id']);

            if (!$statusTranslation || !isset($statusTranslation['description'])) {
                // Try with default-fallback, english in this case
                $statusTranslation = $translationCmp->read($fallbackLocaleId, 'ticketStatus', $status['id']);

                if (!$statusTranslation || !isset($statusTranslation['description'])) {
                    continue;
                }
            }

            $status['description'] = $statusTranslation['description'];
        }

        return $statusArray;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return ModelManager
     */
    private function getManager()
    {
        return $this->manager;
    }
}
