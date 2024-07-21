<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;

trait EntitySaveTrait
{
    public function save(User|Product $entity, bool $flush = false): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);

        if ($flush) {
            $entityManager->flush();
        }
    }
}
