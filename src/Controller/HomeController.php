<?php
namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $lastPosts = $postRepository->findBy(
            ['status' => 'validated'],
            ['publishedAt' => 'DESC'],
            2
        );

        $favorisIds = [];

        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId(); // ⚡ récupère l’ID du post
            }
        }

        return $this->render('home/index.html.twig', [
            'last_posts' => $lastPosts,
            'favorisIds' => $favorisIds,
        ]);
    }
}
