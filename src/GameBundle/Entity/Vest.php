<?php declare(strict_types=1);

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity("radioId")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vests",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_vest_radio_id",
 *                                      columns={"radio_id"}
 *                ),
 *               @ORM\UniqueConstraint(name="idx_vest_no",
 *                                      columns={"no"}
 *                ),
 *            }
 * )
 */
class Vest implements ResourceInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $no;

    /**
     * @var string
     * @ORM\Column(type="string", length=8, unique=true)
     * @Assert\Length(min="8", max="8")
     * @Assert\Type(type="xdigit",
     *              message="hittracker.vest.bad_radio_id"
     * )
     */
    private $radioId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->radioId = '';
        $this->active = true;
        $this->no = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setNo(int $no)
    {
        $this->no = $no;
    }

    public function getNo()
    {
        return $this->no;
    }

    public function setRadioId(string $radioId)
    {
        $this->radioId = strtolower($radioId);
    }

    public function getRadioId() : string
    {
        return $this->radioId;
    }

    public function setActive(bool $active = true)
    {
        $this->active = $active;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt() : \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt() : \DateTime
    {
        return $this->updatedAt;
    }

    /** @ORM\PrePersist */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
