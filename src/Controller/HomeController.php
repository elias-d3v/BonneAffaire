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
    // Page d'accueil du site
    #[Route('/', name: 'home')]
    public function index(PostRepository $postRepository, EntityManagerInterface $em, Request $request): Response
    {
        // Récupère les 4 dernières annonces validées
        $lastPosts = $postRepository->findBy(
            ['status' => 'validated'],
            ['publishedAt' => 'DESC'],
            4
        );

        // Initialise un tableau pour stocker les IDs des favoris de l'utilisateur
        $favorisIds = [];

        // Si un utilisateur est connecté, on récupère ses favoris
        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId();
            }
        }

        // Récupère la dernière catégorie consultée (si passée en paramètre dans l'URL)
        $lastCategoryId = $request->query->get('lastCategory'); 

        // Si une catégorie est spécifiée, on propose des annonces similaires
        if ($lastCategoryId) {
            $lastPostsSuggest = $postRepository->findBy(
                ['category' => $lastCategoryId, 'status' => 'validated'],
                ['publishedAt' => 'DESC'],
                4
            );
        // Sinon, on affiche simplement les dernières annonces validées
        } else {
            $lastPostsSuggest = $postRepository->findBy(
                ['status' => 'validated'],
                ['publishedAt' => 'DESC'],
                4
            );
        }

        // Envoie les données à la vue Twig
        return $this->render('home/index.html.twig', [
            'last_posts' => $lastPosts,
            'last_posts_suggest' => $lastPostsSuggest,
            'favorisIds' => $favorisIds,
        ]);
    }
}
