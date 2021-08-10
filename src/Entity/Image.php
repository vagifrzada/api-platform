<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\HasDatesInterface;
use App\Action\Image\UploadImageAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table("images")
 * @Vich\Uploadable
 */
#[ApiResource(
    collectionOperations: [
        "post" => [
            "method" => "POST",
            "path" => "/images",
            "controller" => UploadImageAction::class,
            'deserialize' => false,
            'openapi_context' => [
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        "get",
    ],
    iri: 'http://schema.org/MediaObject',
    itemOperations: [
        "get",
        "put",
        "delete"
    ],
    attributes: [
        "order" => ["createdAt" => "DESC"],
    ],
    normalizationContext: ['groups' => ['images:read']],
)]
class Image implements HasDatesInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    #[Groups(['images:read'])]
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    #[ApiProperty(iri: 'http://schema.org/contentUrl')]
    #[Groups(["images:read", "posts:show"])]
    private ?string $url;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="url")
     */
    #[Assert\NotNull]
    #[Assert\Image]
    private File $file;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $dateTime): void
    {
        $this->createdAt = $dateTime;
    }
}