<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // plus tard tu pourras passer des annonces depuis la BDD
        $posts = [
            ['title' => 'Télévision', 'price' => 45, 'location' => 'Angers', 'image' => '/images/tv.jpg'],
            ['title' => 'Scooter', 'price' => 900, 'location' => 'Nantes', 'image' => '/images/scooter.jpg'],
        ];

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
