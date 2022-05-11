<?php

namespace App\Controller;

use App\Entity\Secret;
use App\Repository\SecretRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SecretController extends AbstractController
{
    private $encoders;
    // private $normalizers;
    private $serializer;

    public function __construct()
    {
        $this->encoders = [new XmlEncoder(), new JsonEncoder()];
        $this->serializer = new Serializer([new ObjectNormalizer()], $this->encoders);
    }

    #[Route('/secret', name: 'app_secret', methods: ['POST'])]
    public function addSecret(Request $request, SecretRepository $secretRepository, LoggerInterface $logger): Response
    {
        // Geting parameters from the request
        $secretText = $request->request->get('secret');
        $expireAfterViews =  $request->request->getInt('expireAfterViews');
        $expireAfter = $request->request->getInt('expireAfter');

        // Checking if the parameters are valid
        if ($secretText === '' | $expireAfterViews < 1) {
            return new Response('Invalid input', 405);
        }

        // Generating createdAt, $expiresAt and $hash based on parameters received in the request
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $expireAfter > 0 ? $createdAt->add(new DateInterval('PT'.$expireAfter.'M')) : $createdAt;
        $hash = Uuid::v4()->toBase58();

        // Creating an instance of Secret entity and populating it
        $secret = new Secret();
        $secret->setSecretText($secretText);
        $secret->setHash($hash);
        $secret->setCreatedAt($createdAt);
        $secret->setExpiresAt($expiresAt);
        $secret->setRemainingViews($expireAfterViews);

        // Saving Secret to the DB
        $secretRepository->add($secret, true);

        // Generating response based on the Accept type of the request, using helper function
        return $this->generateResponse($secret->getDataAsArray(), $request->headers->get('Accept'));
    }

    #[Route('/secret/{hash}', defaults: ['hash' => ''], name: 'get_secret', methods: ['GET'])]
    public function getSecret(string $hash, Request $request, SecretRepository $secretRepository, LoggerInterface $logger): Response{
        // Searching for the secret using $hash. 
        // findByHash() returns the Secret only if all the conditions are met: 
            // 1. hash coresponds
            // 2. remainingViews > 0
            // 3. secret is not expired
        $secret = $secretRepository->findByHash($hash);
        if (!$secret) {
            return new Response("Secret not found", 404);
        }

        // Generating response based on the Accept type of the request, using helper function
        return $this->generateResponse($secret->getDataAsArray(), $request->headers->get('Accept'));
    }   
    
    private function generateResponse(array $data, $headerType): Response{
        if (!$headerType | !in_array($headerType, ['application/json', 'application/xml']) ) {
            $headerType = 'application/json';
        }
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->add(['Content-Type'=>$headerType]);
        $responseType = substr($headerType, strrpos($headerType, '/') + 1);
        $response->setContent($this->serializer->serialize($data, $responseType));
        
        return $response;
    }
}
