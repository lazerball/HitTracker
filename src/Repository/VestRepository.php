<?php declare(strict_types=1);
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace App\Repository;

use App\Model\Vest;
use App\Util\Arrays;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class VestRepository extends EntityRepository
{
    /** @return Vest[]|null */
    public function findActiveVests(?string $unitType = null): ?array
    {
        $query = ['active' => true];
        if (!empty($unitType)) {
            $query['unitType'] = $unitType;
        }

        return $this->findBy($query, ['id' => 'ASC']);
    }

    /** @return Vest[]|null */
    public function findActiveVestsByColor(string $color): ?array
    {
        $query = ['active' => true];
        $query['color'] = $color;

        return $this->findBy($query, ['id' => 'ASC']);
    }

    /** @return array<string, Vest[]> */
    public function findVestsGroupedByColor(bool $active = true): array
    {
        $query = ['active' => $active];
        $results = Arrays::groupBy($this->findBy($query, ['color' => 'ASC', 'id' => 'ASC']), function ($row) {
            return $row->getColor();
        });

        return $results;
    }
}
