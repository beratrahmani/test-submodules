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

namespace SwagPromotion\Components\MetaData;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use RuntimeException;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Detail as Variant;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;

/**
 * Generic search handler for the various kind of usable fields in the backend
 */
class ValueSearch
{
    /**
     * @var array
     */
    private $mapping = [
        'product' => Product::class,
        'detail' => Variant::class,
        'productAttribute' => ProductAttribute::class,
        'categories' => Category::class,
        'user' => Customer::class,
        'price' => Price::class,
        'supplier' => Supplier::class,
        'address' => Address::class,
    ];

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param ModelManager                         $modelManager
     * @param ContextServiceInterface              $contextService
     * @param AdditionalTextServiceInterface       $additionalTextService
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        ModelManager $modelManager,
        ContextServiceInterface $contextService,
        AdditionalTextServiceInterface $additionalTextService,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->modelManager = $modelManager;
        $this->dbalConnection = $modelManager->getConnection();
        $this->contextService = $contextService;
        $this->additionalTextService = $additionalTextService;
        $this->snippetManager = $snippetManager;
    }

    /**
     * Return a unique list of possible values for $field
     *
     * @param string      $field
     * @param int         $offset
     * @param int         $limit
     * @param null|string $searchTerm
     *
     * @return array
     */
    public function get($field, $offset, $limit, $searchTerm)
    {
        switch ($field) {
            case 'user::id':
                return $this->selectUserViaId($offset, $limit, $searchTerm);
            case 'user::paymentID':
                return $this->getPayments($offset, $limit, $searchTerm);
            case 'user::email':
                return $this->selectUserViaEmail($offset, $limit, $searchTerm);
            case 'user::accountmode':
                return $this->selectUserViaAccountMode();
            case 'user::validation':
                return $this->selectUserViaValidation($offset, $limit, $searchTerm);
            case 'user::paymentpreset':
                return $this->getPaymentPresets($offset, $limit, $searchTerm);
            case 'user::internalcomment':
                return $this->selectUserViaInternalComment($offset, $limit, $searchTerm);
            case 'address::country_id':
                    return $this->getCountries($offset, $limit, $searchTerm);
            case 'address::state_id':
                    return $this->getStates($offset, $limit, $searchTerm);
            case 'detail::kind':
                return $this->getDetailKinds();
            case 'customer_stream::id':
                return $this->getCustomerStreams($offset, $limit, $searchTerm);
        }

        list($model, $mainField) = $this->split($field);

        $metaData = $this->getClassMetaData($model);

        foreach ($metaData->getAssociationMappings() as $mapping) {
            $sourceToTargetKeyColumns = $mapping['sourceToTargetKeyColumns'];
            if ($sourceToTargetKeyColumns && array_key_exists($mainField, $sourceToTargetKeyColumns)) {
                $model = $mapping['targetEntity'];
                $mainField = $mapping['sourceToTargetKeyColumns'][$mainField];
                $metaData = $this->getClassMetaData($model);
                break;
            }
        }

        $table = $metaData->getTableName();
        $names = implode(
            ', ',
            array_intersect(
                array_diff(
                    ['name', 'description', 'firstname', 'lastname', 'email'],
                    [$mainField]
                ),
                $metaData->getColumnNames()
            )
        );

        if ($names) {
            $names = ', ' . $names;
        }

        $join = '';
        if ($table === 's_articles_prices') {
            $join = 'INNER JOIN (SELECT id as articleId, name FROM s_articles AS articles) AS articles ON articles.articleId = s_articles_prices.articleID';
            $names .= ', name';
        } elseif ($table === 's_articles_details') {
            $join = 'INNER JOIN (SELECT id as articleId, name FROM s_articles AS articles) AS articles ON articles.articleId = s_articles_details.articleID';
            $names .= ', name, additionaltext, ordernumber, articles.articleId';
        } elseif ($table === 's_articles') {
            $join = 'INNER JOIN (SELECT articleID, ordernumber FROM s_articles_details AS details) AS details ON details.articleID = s_articles.id';
            $names .= ', ordernumber';
        }

        $where = $this->getWhere($names . ', ' . $mainField, $searchTerm);

        $sql = "SELECT SQL_CALC_FOUND_ROWS `$mainField` {$names}, id as internalId
                FROM $table
                $join
                $where
                GROUP BY `$mainField`
                LIMIT $offset,$limit";

        $result = $this->dbalConnection->fetchAll($sql, ['searchTerm' => '%' . $searchTerm . '%']);

        $result = $this->changeColumns($result, $field);
        $total = $this->dbalConnection->fetchColumn('SELECT FOUND_ROWS()');

        if (array_key_exists('additionaltext', $result[0])) {
            /** @var Repository $shopRepo */
            $shopRepo = $this->modelManager->getRepository(Shop::class);
            $shop = $shopRepo->getActiveDefault();
            $shop->registerResources();
            /** @var ShopContextInterface $shopContext */
            $shopContext = $this->contextService->getShopContext();

            foreach ($result as &$variant) {
                if ($variant['additionaltext'] === null || $variant['additionaltext'] === '') {
                    $listProduct = new ListProduct(
                        $variant['articleId'],
                        $variant['internalId'],
                        $variant['ordernumber']
                    );

                    $listProduct = $this->additionalTextService->buildAdditionalText($listProduct, $shopContext);
                    $variant['additionaltext'] = $listProduct->getAdditional();
                }
                unset($variant['articleId']);
            }
            unset($variant);
        }

        return [
            'data' => $result,
            'total' => $total,
        ];
    }

    /**
     * @param string $name
     *
     * @return ClassMetadata
     */
    private function getClassMetaData($name)
    {
        return $this->modelManager->getClassMetadata($name);
    }

    /**
     * A helper function that renames the database columns to a better matching (readable) name.
     *
     * @param array  $data
     * @param string $field
     *
     * @return array
     */
    private function changeColumns(array $data, $field)
    {
        switch ($field) {
            case 'user::language':
                return $this->replaceKeys($data, 'name', 'shopName');
            case 'categories.description':
                return $this->replaceKeys($data, 'description', 'name');
            case 'categories.id':
                return $this->replaceKeys($data, 'description', 'name');
            case 'categories.cmstext':
                $data = $this->replaceKeys($data, 'description', 'name');

                return $this->replaceKeys($data, 'cmstext', 'description');
            case 'categories.cmsheadline':
                return $this->replaceKeys($data, 'description', 'name');
            case 'product::taxID':
                return $this->replaceKeys($data, 'description', 'name');
            case 'product::pricegroupID':
                return $this->replaceKeys($data, 'description', 'name');
            case 'product::description':
                $data = $this->replaceKeys($data, 'description', 'shortDescription');

                return $this->replaceKeys($data, 'name', 'articleName');
            case 'product::description_long':
                $data = $this->replaceKeys($data, 'name', 'articleName');

                return $this->replaceKeys($data, 'description', 'shortDescription');
            case 'product::name':
                return $this->replaceKeys($data, 'description', 'shortDescription');
            case 'product::keywords':
                return $this->replaceKeys($data, 'description', 'shortDescription');
            case 'product::id':
                return $this->replaceKeys($data, 'description', 'shortDescription');
            case 'price::price':
                $data = $this->replaceKeys($data, 'name', 'articleName');

                return $this->replaceKeys($data, 'price', 'netPrice');
            case 'price::to':
                return $this->replaceKeys($data, 'price', 'netPrice');
            case 'price::pseudoprice':
                $data = $this->replaceKeys($data, 'pseudoprice', 'netPseudoprice');

                return $this->replaceKeys($data, 'name', 'articleName');
            case 'price::id':
                return $this->replaceKeys($data, 'name', 'articleName');
            case 'price::baseprice':
                return $this->replaceKeys($data, 'name', 'articleName');
            default:
                return $data;
        }
    }

    /**
     * Replaces all keys in the provided array with a new one.
     *
     * @param array  $input
     * @param string $oldKey
     * @param string $newKey
     *
     * @return array
     */
    private function replaceKeys(array $input, $oldKey, $newKey)
    {
        $result = [];
        foreach ($input as $key => $value) {
            if ($key === $oldKey) {
                $key = $newKey;
            }

            if (is_array($value)) {
                $value = $this->replaceKeys($value, $oldKey, $newKey);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Split fields if needed (e.g. MAPPING::FIELD and MAPPING.NESTED_FIELDS)
     *
     * @param string $inputField
     *
     * @throws RuntimeException
     *
     * @return array
     */
    private function split($inputField)
    {
        list($type, $field) = explode('::', $inputField, 2);
        if (!$field) {
            list($type, $field) = explode('.', $inputField, 2);
        }

        if (!isset($this->mapping[$type])) {
            throw new RuntimeException("Type $type not defined");
        }

        $model = $this->mapping[$type];

        return [$model, $field];
    }

    /**
     * @param string $names
     * @param string $searchTerm
     *
     * @return string
     */
    private function getWhere($names, $searchTerm)
    {
        if ($searchTerm === '') {
            return $searchTerm;
        }

        $addWhere = '';

        if (strpos($names, 'firstname') !== false) {
            $addWhere = 'WHERE firstname LIKE :searchTerm';
        }

        if (strpos($names, 'lastname') !== false) {
            if (empty($addWhere)) {
                $addWhere = 'WHERE lastname LIKE :searchTerm';
            } else {
                $addWhere .= ' OR lastname LIKE :searchTerm';
            }
        }

        if (strpos($names, 'name') !== false) {
            if (empty($addWhere)) {
                $addWhere = 'WHERE name LIKE :searchTerm';
            }
        }

        if (strpos($names, 'description') !== false) {
            if (empty($addWhere)) {
                $addWhere = 'WHERE description LIKE :searchTerm';
            } else {
                $addWhere .= ' OR description LIKE :searchTerm';
            }
        }

        if (strpos($names, 'ordernumber') !== false) {
            if (empty($addWhere)) {
                $addWhere = 'WHERE ordernumber LIKE :searchTerm';
            } else {
                $addWhere .= ' OR ordernumber LIKE :searchTerm';
            }
        }

        return $addWhere;
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return array
     */
    private function returnData(QueryBuilder $builder)
    {
        $query = $builder->execute();
        $result = $query->fetchAll();
        $total = $builder->getConnection()->fetchColumn('SELECT FOUND_ROWS()');

        return [
            'data' => $result,
            'total' => $total,
        ];
    }

    /**
     * @return \Enlight_Components_Snippet_Namespace
     */
    private function getSnippetNamespace()
    {
        $snippets = $this->snippetManager->getNamespace('backend/swag_promotion/snippets');

        return $snippets;
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function getPayments($offset, $limit, $searchTerm)
    {
        $builder = $this->getPaymentQueryBuilder($offset, $limit, $searchTerm);

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function getPaymentPresets($offset, $limit, $searchTerm)
    {
        $snippets = $this->getSnippetNamespace();

        $builder = $this->getPaymentQueryBuilder($offset, $limit, $searchTerm);
        $builder->resetQueryPart('where');
        $builder->orWhere('payment.name LIKE :searchTerm')
            ->orWhere('payment.description LIKE :searchTerm');

        $query = $builder->execute();

        $result = $query->fetchAll();
        array_unshift($result, ['id' => 0, 'paymentDescription' => $snippets->get('searchWindowNoPaymentPreset')]);
        $total = $builder->getConnection()->fetchColumn('SELECT FOUND_ROWS()') + 1;

        return [
            'data' => $result,
            'total' => $total,
        ];
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return QueryBuilder
     */
    private function getPaymentQueryBuilder($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select([
                'SQL_CALC_FOUND_ROWS payment.id',
                'payment.description as paymentDescription', ])
            ->from('s_core_paymentmeans', 'payment')
            ->orWhere('payment.name LIKE :searchTerm')
            ->orWhere('payment.description LIKE :searchTerm')
            ->andWhere('payment.active = 1')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $builder;
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function selectUserViaId($offset, $limit, $searchTerm)
    {
        $builder = $this->getUserQueryBuilder($offset, $limit, $searchTerm);

        $builder->select(
            ['SQL_CALC_FOUND_ROWS user.id', 'user.email', 'address.firstname', 'address.lastname', 'address.company']
        );

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function selectUserViaEmail($offset, $limit, $searchTerm)
    {
        $builder = $this->getUserQueryBuilder($offset, $limit, $searchTerm);

        $builder->select(
            ['SQL_CALC_FOUND_ROWS user.email', 'address.firstname', 'address.lastname', 'address.company']
        );

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function selectUserViaInternalComment($offset, $limit, $searchTerm)
    {
        $builder = $this->getUserQueryBuilder($offset, $limit, $searchTerm);

        $builder->select(
            ['SQL_CALC_FOUND_ROWS user.internalcomment', 'address.firstname', 'address.lastname', 'address.company']
        );

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return QueryBuilder
     */
    private function getUserQueryBuilder($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select(['SQL_CALC_FOUND_ROWS user'])
            ->from('s_user', 'user')
            ->innerJoin('user', 's_user_addresses', 'address', 'user.default_billing_address_id = address.id')
            ->orWhere('user.email LIKE :searchTerm')
            ->orWhere('address.firstname LIKE :searchTerm')
            ->orWhere('address.lastname LIKE :searchTerm')
            ->orWhere('address.company LIKE :searchTerm')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $builder;
    }

    /**
     * @return array
     */
    private function selectUserViaAccountMode()
    {
        $snippets = $this->getSnippetNamespace();

        $result = [
            ['accountmode' => 0, 'description' => $snippets->get('searchWindowAccountModeNormal')],
            ['accountmode' => 1, 'description' => $snippets->get('searchWindowAccountModeGuest')],
        ];

        return [
            'data' => $result,
            'total' => 2,
        ];
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function selectUserViaValidation($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select('SQL_CALC_FOUND_ROWS groupkey as validateGroupKey')
            ->from('s_core_customergroups')
            ->where('groupkey LIKE :searchTerm')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function getCountries($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select('SQL_CALC_FOUND_ROWS id, countryname, countryen')
            ->from('s_core_countries')
            ->orWhere('countryname LIKE :searchTerm')
            ->orWhere('countryen LIKE :searchTerm')
            ->orWhere('countryiso LIKE :searchTerm')
            ->orWhere('iso3 LIKE :searchTerm')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $this->returnData($builder);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function getStates($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select('SQL_CALC_FOUND_ROWS id, name as stateName')
            ->from('s_core_countries_states')
            ->orWhere('name LIKE :searchTerm')
            ->orWhere('shortcode LIKE :searchTerm')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $this->returnData($builder);
    }

    /**
     * @return array
     */
    private function getDetailKinds()
    {
        $snippets = $this->getSnippetNamespace();

        $result = [
            ['kind' => 1, 'description' => $snippets->get('searchWindowKindMainDetail')],
            ['kind' => 2, 'description' => $snippets->get('searchWindowKindVariant')],
        ];

        return [
            'data' => $result,
            'total' => 2,
        ];
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $searchTerm
     *
     * @return array
     */
    private function getCustomerStreams($offset, $limit, $searchTerm)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->select('SQL_CALC_FOUND_ROWS id, name')
            ->from('s_customer_streams')
            ->orWhere('name LIKE :searchTerm')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $this->returnData($builder);
    }
}
