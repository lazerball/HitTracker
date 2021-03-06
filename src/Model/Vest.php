<?php declare(strict_types=1);

namespace App\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\Constraints as HitTrackerAssert;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *   normalizationContext={"groups"={"read"}},
 *   denormalizationContext={"groups"={"write"}},
 * )
 * @ORM\Entity
 * @UniqueEntity("radioId")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="units",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_unit_radio_id",
 *                                      columns={"radio_id"}
 *                ),
 *            }
 * )
 */
class Vest implements ResourceInterface, UnitInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="integer")
     * @Serializer\Groups({"read"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=17, unique=true)
     * @Assert\NotBlank()
     * @HitTrackerAssert\MacAddress
     * @Serializer\Groups({"read","write"})
     */
    private $radioId;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Choice(callback = "getUnitTypes")
     * @Serializer\Groups({"read","write"})
     */
    private $unitType;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Choice(callback = "getColors")
     * @Serializer\Groups({"read","write"})
     */
    private $color;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"read","write"})
     */
    private $illuminationStyle;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"read","write"})
     */
    private $zones;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups({"read","write"})
     */
    private $active;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"read"})
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->id = 0;
        $this->radioId = '';
        $this->active = true;
        $this->zones = 0;
        $this->illuminationStyle = 'none';
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setNumber(int $number): void
    {
        $this->id = $number;
    }

    public function getNumber(): ?int
    {
        return $this->id;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    /** @return string[] */
    public static function getColors(): array
    {
        return ['red', 'blue', 'orange', 'green'];
    }

    public function setUnitType(string $unitType): void
    {
        $this->unitType = $unitType;
    }

    public function getUnitType(): ?string
    {
        return $this->unitType;
    }

    /** @return string[] */
    public static function getUnitTypes(): array
    {
        return ['vest', 'target'];
    }

    public function setIlluminationStyle(string $illuminationStyle): void
    {
        $this->illuminationStyle = $illuminationStyle;
    }

    public function getIlluminationStyle(): ?string
    {
        return $this->illuminationStyle;
    }

    /** @return string[] */
    public static function getIlluminationStyles(): array
    {
        return ['rgbw', 'rgb', 'simple_led', 'none'];
    }

    public function setZones(int $zones): void
    {
        $this->zones = $zones;
    }

    public function getZones(): ?int
    {
        return $this->zones;
    }

    public function setRadioId(string $radioId): void
    {
        $this->radioId = strtolower($radioId);
    }

    public function getRadioId(): string
    {
        return $this->radioId;
    }

    public function setActive(bool $active = true): void
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /** @ORM\PrePersist */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
