<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Bridge;

use Shopware\B2B\Account\Framework\AccountImageServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\File;

class AccountImageService implements AccountImageServiceInterface
{
    const IMAGE_DESCRIPTION = 'Profile image';
    const ALBUM_NAME = 'b2b';
    const ALLOWED_FILE_EXTENSIONS = [
      'bmp',
      'gif',
      'ico',
      'jpeg',
      'jpg',
      'png',
    ];

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authenticationRepository;

    /**
     * @param StoreFrontAuthenticationRepository $authenticationRepository
     */
    public function __construct(StoreFrontAuthenticationRepository $authenticationRepository)
    {
        $this->authenticationRepository = $authenticationRepository;
    }

    /**
     * @param int $authId
     * @param array $uploadedFile
     * @return array
     */
    public function uploadImage(int $authId, array $uploadedFile): array
    {
        /** @var File $file */
        $file = $this->getFileObject($uploadedFile);

        if (!$this->isImage($file)) {
            return ['success' => false];
        }

        return $this->createImage($authId, $file);
    }

    /**
     * @param int $authId
     * @param File $file
     * @return array
     * @internal
     */
    protected function createImage(int $authId, File $file): array
    {
        $media = new Media();

        $album = Shopware()->Models()->getRepository(Album::class)->findOneBy(['name' => self::ALBUM_NAME]);
        $media->setAlbum($album);
        $media->setUserId(0);

        $media->setDescription(self::IMAGE_DESCRIPTION);
        $media->setCreated(new \DateTime());

        //set the upload file into the model. The model saves the file to the directory
        $media->setFile($file);

        //persist the model into the model manager
        Shopware()->Models()->persist($media);
        Shopware()->Models()->flush();

        $this->authenticationRepository->syncAvatarImage($authId, $media->getId());

        return ['success' => true, 'path' => $media->getPath()];
    }

    /**
     * @param File $file
     * @return bool
     * @internal
     */
    protected function isImage(File $file): bool
    {
        $lowerExtension = strtolower($file->getExtension());

        return @is_array(getimagesize($file->getPathname()))
            && in_array($lowerExtension, self::ALLOWED_FILE_EXTENSIONS, true);
    }

    /**
     * @param array $file
     * @return File
     * @internal
     */
    protected function getFileObject(array $file): File
    {
        $fileObject = new File($file['tmp_name']);
        $fileName = uniqid('ShopwareB2bAccountImage_', true) . '_' . $file['name'];

        $fileObject = $fileObject->move($fileObject->getPath(), $fileName);

        return $fileObject;
    }
}
