<?php

namespace EK\PDBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * WishRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WishRepository extends EntityRepository {

   /* public function findAllJoinedToOwner() {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT * FROM EKPDBundle:Wish p
            JOIN p.ownerId c
            WHERE p.id = :id'
                        );

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }*/

}
