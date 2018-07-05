<?php

namespace App\Service;

use App\Entity\Client;
use App\Exception\NipValidationException;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\GusApi;
use GusApi\RegonConstantsInterface;

class GusData
{
    private $gus;
    private $client;

    public function __construct(Client $client)
    {
        $this->gus = new GusApi('abcde12345abcde12345', new \GusApi\Adapter\Soap\SoapAdapter(
            RegonConstantsInterface::BASE_WSDL_URL,
            RegonConstantsInterface::BASE_WSDL_ADDRESS_TEST));
        $this->client = $client;
    }

    public function getData()
    {
        try {
            NipValidator::validate($this->client->getNip());
            $nipToCheck = $this->client->getNip();
            $sessionId = $this->gus->login();
            return $gusReports = $this->gus->getByNip($sessionId, $nipToCheck);

        } catch (InvalidUserKeyException $e) {
            echo 'Bad user key';

        } catch (\GusApi\Exception\NotFoundException $e) {
            echo 'No data found <br>';
            echo 'For more information read server message below: <br>';
            echo $this->gus->getResultSearchMessage($sessionId);

        } catch (NipValidationException $e) {
            echo $e->getMessage();
        }
    }

    public function getParsedData()
    {
        $data = $this->getData();
        $gusRegon = $data[0]->getRegon14();
        $gusName = mb_convert_case(ucwords(mb_strtolower(str_replace('"', '', $data[0]->getName()))), MB_CASE_TITLE, "UTF-8");
        $gusCity = ucwords(mb_strtolower($data[0]->getCity()));
        $gusStreet = str_replace('ul. ', '', $data[0]->getStreet());
        $gusZipCode = $data[0]->getZipCode();
        $gusProvince = mb_convert_case(ucwords(mb_strtolower($data[0]->getProvince())), MB_CASE_TITLE, "UTF-8");

        return ['regon' => $gusRegon,
                'name' => $gusName,
                'city' => $gusCity,
                'street' => $gusStreet,
                'zipCode' => $gusZipCode,
                'province' => $gusProvince];

    }

    public function saveClientFromGusData($em, $gusData)
    {
        $this->client->setRegon($gusData['regon']);
        $this->client->setName($gusData['name']);
        $this->client->setCity($gusData['city']);
        $this->client->setStreet($gusData['street']);
        $this->client->setZipCode($gusData['zipCode']);
        $this->client->setProvince($gusData['province']);
        $em->persist($this->client);
        $em->flush();
    }

}


