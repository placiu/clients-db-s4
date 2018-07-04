<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findAllLimit($limit, $offset)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('SELECT p FROM App\Entity\Client p')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

        return $query->execute();
    }

    public function searchForClients($toSearch = null, $columnToSearch = null, $limit, $offset)
    {
        $entityManager = $this->getEntityManager();
        $clientRepository = $entityManager->getRepository(Client::class);

        if ($toSearch && $columnToSearch) {
            $allClients = $clientRepository->findBy([$columnToSearch => filter_var($toSearch, FILTER_SANITIZE_STRING)]);
            $clients = $clientRepository->findBy([$columnToSearch => filter_var($toSearch, FILTER_SANITIZE_STRING)], null, $limit, $offset);
        } else {
            $allClients = $clientRepository->findAll();
            $clients = $this->findAllLimit($limit, $offset);
        }

        return ['allClients' => $allClients, 'clients' => $clients];
    }

}
