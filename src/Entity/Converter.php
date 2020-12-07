<?php

namespace App\Entity;

use App\Repository\ConverterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ConverterRepository::class)
 */
class Converter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     * @Serializer\Type("integer")
     * @Serializer\Groups({"json"})
     *
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
     * @ORM\OneToMany(targetEntity=ConvertedFile::class, mappedBy="converter", orphanRemoval=true)
     * @Serializer\Expose()
     * @Serializer\Type("ArrayCollection<App\Entity\ConvertedFile>")
     * @Serializer\Groups({"json"})
     */
    private $convertedFiles;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getConvertedFiles(): ArrayCollection
    {
        return $this->convertedFiles;
    }

    public function setConvertedFiles(ArrayCollection $convertedFiles): void
    {
        $this->convertedFiles = $convertedFiles;
    }
}
