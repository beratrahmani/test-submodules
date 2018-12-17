<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

class OrderContext implements \JsonSerializable
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $listId;

    /**
     * @var string
     */
    public $orderNumber;

    /**
     * @var int
     */
    public $shippingAddressId;

    /**
     * @var int
     */
    public $billingAddressId;

    /**
     * @var int
     */
    public $paymentId;

    /**
     * shopware alias: dispatchID
     *
     * @var int
     */
    public $shippingId;

    /**
     * @var float
     */
    public $shippingAmount;

    /**
     * @var float
     */
    public $shippingAmountNet;

    /**
     * @var string
     */
    public $comment = '';

    /**
     * @var string
     */
    public $deviceType = '';

    /**
     * @var int
     */
    public $statusId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $authId;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $requestedDeliveryDate;

    /**
     * @var string
     */
    public $orderReference;

    /**
     * @var string
     */
    public $declinedAt;

    /**
     * @var string
     */
    public $clearedAt;

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->statusId === -2;
    }

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'list_id' => $this->listId,

            'ordernumber' => $this->orderNumber,

            'shipping_address_id' => $this->shippingAddressId,
            'billing_address_id' => $this->billingAddressId,

            'payment_id' => $this->paymentId,
            'shipping_id' => $this->shippingId,

            'shipping_amount' => $this->shippingAmount,
            'shipping_amount_net' => $this->shippingAmountNet,

            'order_reference' => $this->orderReference,
            'requested_delivery_date' => $this->requestedDeliveryDate,

            'comment' => $this->comment,
            'device_type' => $this->deviceType,
            'status_id' => $this->statusId,

            'auth_id' => $this->authId,
        ];
    }

    /**
     * @param array $data
     */
    public function fromDatabaseArray(array $data)
    {
        $this->id = (int) $data['id'];

        $this->listId = (int) $data['list_id'];

        $this->orderNumber = (string) $data['ordernumber'];

        $this->authId = (int) $data['auth_id'];

        $this->createdAt = $data['created_at'];
        $this->clearedAt = $data['cleared_at'];
        $this->declinedAt = $data['declined_at'];

        $this->shippingAddressId = (int) $data['shipping_address_id'];
        $this->billingAddressId = (int) $data['billing_address_id'];

        $this->paymentId = (int) $data['payment_id'];
        $this->shippingId = (int) $data['shipping_id'];
        $this->shippingAmount = (float) $data['shipping_amount'];
        $this->shippingAmountNet = (float) $data['shipping_amount_net'];

        $this->comment = $data['comment'];
        $this->deviceType = $data['device_type'];
        $this->statusId = (int) $data['status_id'];

        $this->orderReference = (string) $data['order_reference'];
        $this->requestedDeliveryDate = (string) $data['requested_delivery_date'];

        if (isset($data['status'])) {
            $this->status = (string) $data['status'];
        }

        if (!$this->orderNumber) {
            $this->orderNumber = null;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
