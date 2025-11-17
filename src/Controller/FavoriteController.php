<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Post;
use App\Security\AccessChecker;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/favorites')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'favorites_list')]
    public function list(FavoriteRepository $repo): Response
    {
        // Affiche la liste des favoris de l'utilisateur connecté
        AccessChecker::checkAccess($this->getUser(), '');

        // Récupère les favoris de l'utilisateur
        $favorites = $repo->findBy(['user' => $this->getUser()]);

        // Récupère uniquement les IDs des posts favoris (utile pour l'affichage)
        $favorisIds = [];
        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId();
            }
        }

        // Rend la page avec la liste des favoris
        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites,
            'favorisIds' => $favorisIds,
        ]);
    }

    // Ajoute ou retire un post des favoris
    #[Route('/favorites/toggle/{id}', name: 'favorites_toggle', methods: ['POST'])]
    public function toggle(Post $post, EntityManagerInterface $em, FavoriteRepository $repo): JsonResponse
    {
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté
        if (!$user) {
            return new JsonResponse(['status' => 'unauthorized'], 401);
        }

        // Vérifie si le post est déjà dans les favoris
        $existing = $repo->findOneBy(['user' => $user, 'post' => $post]);

        // Si le favori existe déjà -> on le supprime
        if ($existing) {
            $em->remove($existing);
            $em->flush();
            $em->refresh($user);
            return new JsonResponse(['status' => 'removed']);
            // Sinon -> on l'ajoute
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setPost($post);
            $em->persist($favorite);
            $em->flush();
            $em->refresh($user);
            return new JsonResponse(['status' => 'added']);
        }
    }
}
