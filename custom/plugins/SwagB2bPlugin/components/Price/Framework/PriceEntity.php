<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Framework;

use Shopware\B2B\Common\CrudEntity;

class PriceEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $debtorId;

    /**
     * @var int
     */
    public $articlesDetailsId;

    /**
     * @var float
     */
    public $price;

    /**
     * @var int
     */
    public $from;

    /**
     * @var int
     */
    public $to;

    /**
     * @var string
     */
    public $orderNumber;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'debtor_id' => $this->debtorId,
            'articles_details_id' => $this->articlesDetailsId,
            '`from`' => $this->from,
            '`to`' => $this->to,
            'price' => $this->price,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $priceData): CrudEntity
    {
        $this->price = (float) $priceData['price'];
        $this->from = (int) $priceData['from'];
        $this->to = (int) $priceData['to'];
        $this->id = (int) $priceData['id'];
        $this->debtorId = (int) $priceData['debtor_id'];
        $this->articlesDetailsId = (int) $priceData['articles_details_id'];

        if (array_key_exists('ordernumber', $priceData)) {
            $this->orderNumber = $priceData['ordernumber'];
        }

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $priceEntityArray = get_object_vars($this);
        unset($priceEntityArray['orderNumber']);

        return $priceEntityArray;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
