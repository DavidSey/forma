<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserProvider.
 */
class UserProvider
{
    /** @var RegistryInterface */
    private $doctrine;

    /** @var BrokenSqlEscaper */
    private $sqlEscaper;

    /**
     * UserProvider constructor.
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine, BrokenSqlEscaper $sqlEscaper)
    {
        $this->doctrine = $doctrine;
        $this->sqlEscaper = $sqlEscaper;
    }

    /**
     * @param $username
     * @return User[]
     */
    public function provideUsers($username)
    {
        $escapedUsername = $this->sqlEscaper->escapeValue($username);
        return $this->doctrine->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.username = '.$escapedUsername)
            ->getQuery()
            ->getResult();
    }
}
