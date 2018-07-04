<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Form\EditClient;
use App\Repository\ClientRepository;
use App\Service\GusData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        return $this->redirectToRoute('client_index');
    }

    /**
     * @Route("/client/list/{page}/{search}/{searchColumn}", name="client_index", defaults={"page": 1, "search": null, "searchColumn": null})
     */
    public function index(ClientRepository $clientRepository, Request $request, $page, $search, $searchColumn)
    {
        $limit = 2;
        $offset = ($page - 1) * $limit;

        $em = $this->getDoctrine()->getManager();

        $dbColumns = $em->getClassMetadata(Client::class)->getColumnNames();
        $columns = [];
        foreach ($dbColumns as $dBColumn) {
            $columns[$dBColumn] = ucwords(str_replace('_', ' ', $dBColumn));
        }

        ($request->get('toSearch')) ? $toSearch = $request->get('toSearch') : $toSearch = $search;
        ($request->get('columnToSearch')) ? $columnToSearch = $request->get('columnToSearch') : $columnToSearch = $searchColumn;

        $clients = $clientRepository->searchForClients($toSearch, $columnToSearch, $limit, $offset);

        $nrOfClients = count($clients['allClients']);
        $nrOfPages = ceil($nrOfClients / $limit);

        return $this->render('client/list.html.twig', [
            'page' => $page,
            'nrOfPages' => $nrOfPages,
            'columns' => $columns,
            'clients' => $clients['clients'],
            'search' => $toSearch,
            'searchColumn' => $columnToSearch
        ]);
    }

    /**
     * @Route("/client/new", name="client_new", methods="POST")
     */
    public function new(Request $request)
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $gusData = new GusData($client);
            $gusData = $gusData->getParsedData();

            if ($gusData) {
                $em = $this->getDoctrine()->getManager();

                $client->setRegon($gusData['regon']);
                $client->setName($gusData['name']);
                $client->setCity($gusData['city']);
                $client->setStreet($gusData['street']);
                $client->setZipCode($gusData['zipCode']);
                $client->setProvince($gusData['province']);

                $em->persist($client);
                $em->flush();

                return $this->redirectToRoute('client_index');
            }

        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/{id}/show", name="client_show", methods="GET")
     */
    public function show(Client $client)
    {
        return $this->render('client/show.html.twig', ['client' => $client]);
    }

    /**
     * @Route("/client/{id}/edit", name="client_edit", methods="GET|POST")
     */
    public function edit(Request $request, Client $client)
    {
        $form = $this->createForm(EditClient::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('client_edit', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/{id}", name="client_delete", methods="DELETE")
     */
    public function delete(Request $request, Client $client)
    {
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($client);
            $em->flush();
        }

        return $this->redirectToRoute('client_index');
    }

}
