<?php

namespace App\Repository;

use App\Entity\User;

trait RepositoryTrait
{
    public function save(User $entity, bool $flush = false): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        if ($flush) {
            $entityManager->flush();
        }
    }
}
