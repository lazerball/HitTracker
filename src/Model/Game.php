<?php declare(strict_types=1);

namespace App\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\Constraints as CommonAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Game
 *
 * @ApiResource(
 *   normalizationContext={"groups"={"read"}},
 *   denormalizationContext={"groups"={"write"}},
 * )
 * @ORM\Entity
 * @ORM\Table(name="games")
 */
class Game implements ResourceInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"read"})
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.arena_not_exists"
     * )
     * @Serializer\Groups({"read", "write"})
     */
    private $arena;

    /**
     * @var MatchSettings
     * @ORM\Column(type="json_document", options={"jsonb": "true"})
     * @Serializer\Groups({"read", "write"})
     */
    private $settings;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"read"})
     */
    private $endsAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"read"})
     */
    private $createdAt;

    /**
     * @var Collection
     *
     * @Assert\Valid(traverse=true)
     * @Assert\Count(min="1",
     *               minMessage="hittracker.game.not_enough_teams"
     * )
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="[0][players][0].unit",
     *         message="hittracker.game.unique_vests_required")
     * })
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="[0][players][0].name",
     *         message="hittracker.game.unique_names_required"
     *     )
     * })
     * @ORM\OneToMany(targetEntity="MatchTeam", mappedBy="game",
     *                cascade={"persist", "remove"})
     * @Serializer\Groups({"read", "write"})
     */
    protected $teams;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_long_enough"
     * )
     * @Serializer\Groups({"read", "write"})
     */
    protected $length;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\Choice(callback="getGameTypes")
     * @Serializer\Groups({"read", "write"})
     */
    protected $gameType;

    public function __construct(string $gameType, int $length, MatchSettings $settings, int $arena = 1, ?\DateTime $createdAt = null)
    {
        $this->arena = $arena;
        $this->gameType = $gameType;
        $this->teams = new ArrayCollection();
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->setGameLength($length);
        $this->settings = $settings;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getArena(): int
    {
        return $this->arena;
    }

    public function getEndsAt(): \DateTime
    {
        return $this->endsAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the length of a game in minutes
     */
    private function setGameLength(int $minutes): void
    {
        $this->length = $minutes;

        $end = clone $this->createdAt;
        $end->add(new \DateInterval('PT'.$this->length.'M'));
        $this->endsAt = $end;
    }

    /**
     * @return int the length of the game in minutes
     */
    public function getGameLength(): int
    {
        return $this->length;
    }

    /** @return string[] */
    public static function getHumanGameTypes(): array
    {
        return array_map(function ($t) {
            return ucwords(str_replace('_', ' ', $t));
        }, self::getGameTypes());
    }

    /** @return string[] */
    public static function getGameTypes(): array
    {
        return ['team', 'target'];
    }

    public function getGameType(): string
    {
        return $this->gameType;
    }

    public function getSettings(): MatchSettings
    {
        return $this->settings;
    }

    /**
     * @Serializer\Groups({"read"})
     */
    public function isActive(): bool
    {
        return $this->endsAt > new \DateTime();
    }

    /**
     * Mark the game as stopped
     *
     * Sets endsAt to now
     */
    public function stop(): void
    {
        $this->endsAt = new \DateTime();
    }

    public function timeLeft(): \DateInterval
    {
        $now = new \DateTime();

        return $now->diff($this->endsAt);
    }

    /**
     * @Serializer\Groups({"read"})
     */
    public function getTimeTotal(): int
    {
        return $this->endsAt->getTimestamp() - $this->createdAt->getTimestamp();
    }

    public function timeTotal(): \DateInterval
    {
        return $this->endsAt->diff($this->createdAt);
    }

    public function addTeam(MatchTeam $team): void
    {
        $this->teams->add($team);
        $team->setGame($this);
    }

    public function getPlayers(): Collection
    {
        $players = [];
        foreach ($this->teams as $team) {
            $players[] = $team->getPlayers()->toArray();
        }

        return new ArrayCollection(array_merge(...$players));
    }

    /**
     * @return Player|mixed
     */
    public function getPlayerByRadioId(string $radioId)
    {
        $players = $this->getPlayers()->filter(function (Player $player) use ($radioId) {
            return $player->getUnit()->getRadioId() === $radioId;
        });

        return $players->first();
    }

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    /** @return string[] */
    public function getTeamNames(): array
    {
        return $this->teams->map(function ($team) {
            return $team->getName();
        })->toArray();
    }

    /**
     * @Serializer\Groups({"read"})
     */
    public function getTotalHitPoints(): int
    {
        $totalHP = array_sum($this->getPlayers()->map(function (Player $player) {
            return $player->getHitPoints();
        })->toArray());

        return (int) $totalHP;
    }

    /**
     * @Serializer\Groups({"read"})
     */
    public function getTotalScore(): int
    {
        $score = array_sum($this->getPlayers()->map(function (Player $player) {
            return $player->getScore();
        })->toArray());

        return (int) $score;
    }
}
