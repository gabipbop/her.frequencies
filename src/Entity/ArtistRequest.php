<?php

namespace App\Entity;

use App\Enum\ArtistRequestStatus;
use App\Repository\ArtistRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRequestRepository::class)]
class ArtistRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private array $raw_links = [];

    #[ORM\Column]
    private array $raw_categories = [];

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(enumType: ArtistRequestStatus::class)]
    private ?ArtistRequestStatus $status = null;

    #[ORM\Column(length: 255)]
    private ?string $emailConfirmed = null;

    #[ORM\Column(length: 255)]
    private ?string $confirmationToken = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRawLinks(): array
    {
        return $this->raw_links;
    }

    public function setRawLinks(array $raw_links): static
    {
        $this->raw_links = $raw_links;

        return $this;
    }

    public function getRawCategories(): array
    {
        return $this->raw_categories;
    }

    public function setRawCategories(array $raw_categories): static
    {
        $this->raw_categories = $raw_categories;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): ?ArtistRequestStatus
    {
        return $this->status;
    }

    public function setStatus(ArtistRequestStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEmailConfirmed(): ?string
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(string $emailConfirmed): static
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
