<?php

namespace MuzikSpirit\BackBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JewelerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username){
        $q = $this
            ->createQueryBuilder('u')
            ->select('u, g')
            ->leftJoin('u.groups', 'g')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username',$username)
            ->setParameter('email',$username)
            ->getQuery();
        try{
            $user = $q->getSingleResult();
        } catch (NoResultException $e){
            return null;
        }
        return $user;
    }


    /**
     * Refresh User by Token
     * @param UserInterface $user
     * @return null|object|UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    /**
     * Get User Class for recognize Authentification Class
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }
}