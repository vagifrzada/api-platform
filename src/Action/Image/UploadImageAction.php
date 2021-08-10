<?php

declare(strict_types=1);

namespace App\Action\Image;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploadImageAction
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Image
    {
        /** @var UploadedFile $uploadedFile */
        if (!($uploadedFile = $request->files->get('file'))) {
            throw new BadRequestHttpException('"File" is required !');
        }

        $image = new Image();
        $image->setFile($uploadedFile);

        $this->validator->validate($image);

        return $image;
    }
}