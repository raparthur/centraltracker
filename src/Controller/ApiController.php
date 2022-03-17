<?php

namespace App\Controller;

use App\Entity\CobanTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return new Response('server ok');
    }

    #[Route('/parsetrack', name: 'parsetrack')]
    public function parsetrack(Request $request, EntityManagerInterface $em): Response
    {

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $port = $request->get('port');
        $data = $request->get('data');
        $key = $request->get('google_api_key');

        $coban = new CobanTest();

        $jsonContent = $serializer->serialize($coban->parse($data,$key), 'json');

        return new Response($jsonContent);
    }
}
