<?php
namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $postRepository, EntityManagerInterface $em, Request $request): Response
    {
        $lastPosts = $postRepository->findBy(
            ['status' => 'validated'],
            ['publishedAt' => 'DESC'],
            4
        );

        $favorisIds = [];

        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId();
            }
        }

        $lastCategoryId = $request->query->get('lastCategory'); 

        if ($lastCategoryId) {
            $lastPostsSuggest = $postRepository->findBy(
                ['category' => $lastCategoryId, 'status' => 'validated'],
                ['publishedAt' => 'DESC'],
                4
            );
        } else {
            $lastPostsSuggest = $postRepository->findBy(
                ['status' => 'validated'],
                ['publishedAt' => 'DESC'],
                4
            );
        }

        return $this->render('home/index.html.twig', [
            'last_posts' => $lastPosts,
            'last_posts_suggest' => $lastPostsSuggest,
            'favorisIds' => $favorisIds,
        ]);
    }
}
