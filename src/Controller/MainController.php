<?php

namespace App\Controller;

use App\Repository\StarshipRepository;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(
        StarshipRepository  $starshipRepository,
        HttpClientInterface $client,
        CacheInterface $issLocationPool,
        #[Autowire(param: 'iss_location_cache_ttl')]
        int $issLocationCacheTtl,
        #[Autowire(service:'twig.command.debug')]
        DebugCommand $twigDebugCommand,
    ): Response {
        $output = new BufferedOutput();
        $twigDebugCommand->run(new ArrayInput([]), $output);
        dd($output);
        $ships = $starshipRepository->findAll();
        $myShip = $ships[array_rand($ships)];

        $issData = $issLocationPool->get('iss_location_data', function () use ($client): array {

            $response = $client->request('GET', 'https://api.wheretheiss.at/v1/satellites/25544');
            return $response->toArray();
        });

        return $this->render('main/homepage.html.twig', [
            'myShip' => $myShip,
            'ships' => $ships,
            'issData' => $issData,
        ]);
    }
}
