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
        $em = $this->getDoctrine()->getManager();

        $columns = $clientRepository->getColumns($em);
        $clients = $clientRepository->searchForClients($request, $page, $search, $searchColumn);

        return $this->render('client/list.html.twig', [
            'page' => $page,
            'nrOfPages' => $clients['nrOfPages'],
            'columns' => $columns,
            'clients' => $clients['clients'],
            'search' => $search,
            'searchColumn' => $searchColumn
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
            $gusDataParsed = $gusData->getParsedData();

            if ($gusDataParsed) {
                $em = $this->getDoctrine()->getManager();
                $gusData->saveClientFromGusData($em, $gusDataParsed);
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
