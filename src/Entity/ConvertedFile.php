<?php

namespace App\Entity;

use App\Repository\ConvertedFileRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @ORM\Entity(repositoryClass=ConvertedFileRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConvertedFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Serializer\Expose()
     * @Serializer\Type("string")
     * @Serializer\Groups({"json"})
     * @Assert\Unique()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Converter::class, inversedBy="convertedFiles")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     * @Serializer\Type("App\Entity\Converter")
     * @Serializer\Groups({"json"})
     */
    private $converter;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Expose()
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s\Z', '', 'Y-m-d\TH:i:s\Z'>")
     * @Serializer\Groups({"json"})
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConverter(): Converter
    {
        return $this->converter;
    }

    public function setConverter(Converter $converter): void
    {
        $this->converter = $converter;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }
}
