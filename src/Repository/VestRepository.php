<?php declare(strict_types=1);

/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace LazerBall\HitTracker\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use LazerBall\HitTracker\Model\Vest;

class VestRepository extends EntityRepository
{
    /** @return Vest[]|null */
    public function findActiveVests(?string $unitType = null)
    {
        $query = ['active' => true];
        if (!empty($unitType)) {
            $query['unitType'] = $unitType;
        }

        return $this->findBy($query, ['id' => 'ASC']);
    }

    /** @return Vest[]|null */
    public function findActiveVestsByColor($color)
    {
        $query = ['active' => true];
        $query['color'] = $color;

        return $this->findBy($query, ['id' => 'ASC']);
    }
}
