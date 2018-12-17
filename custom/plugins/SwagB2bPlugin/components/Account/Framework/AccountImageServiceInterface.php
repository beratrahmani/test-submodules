<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Framework;

interface AccountImageServiceInterface
{
    /**
     * Upload a new avatar image
     *
     * @param int $authId
     * @param array $uploadedFile
     * @return array
     */
    public function uploadImage(int $authId, array $uploadedFile): array;
}
