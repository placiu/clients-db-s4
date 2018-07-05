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

    public function getColumns($em)
    {
        $dbColumns = $em->getClassMetadata(Client::class)->getColumnNames();
        $columns = [];
        foreach ($dbColumns as $dBColumn) {
            $columns[$dBColumn] = ucwords(str_replace('_', ' ', $dBColumn));
        }
        return $columns;
    }

    public function searchForClients($request, $page, $toSearch = null, $columnToSearch = null)
    {
        $limit = 2;
        $offset = ($page - 1) * $limit;

        $entityManager = $this->getEntityManager();
        $clientRepository = $entityManager->getRepository(Client::class);

        if ($request->get('toSearch')) $toSearch = $request->get('toSearch');
        if ($request->get('columnToSearch')) $columnToSearch = $request->get('columnToSearch');

        if ($toSearch && $columnToSearch) {
            $allClients = $clientRepository->findBy([$columnToSearch => filter_var($toSearch, FILTER_SANITIZE_STRING)]);
            $clients = $clientRepository->findBy([$columnToSearch => filter_var($toSearch, FILTER_SANITIZE_STRING)], null, $limit, $offset);
        } else {
            $allClients = $clientRepository->findAll();
            $clients = $this->findAllLimit($limit, $offset);
        }

        $nrOfClients = count($allClients);
        $nrOfPages = ceil($nrOfClients / $limit);

        return ['clients' => $clients, 'nrOfPages' => $nrOfPages, 'toSearch' => $toSearch, 'columnToSearch' => $columnToSearch];
    }

}
