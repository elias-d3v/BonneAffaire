<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Post;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favorites')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'favorites_list')]
    public function list(FavoriteRepository $repo): Response
    {
        $favorites = $repo->findBy(['user' => $this->getUser()]);

        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    #[Route('/add/{id}', name: 'favorites_add')]
    public function add(Post $post, EntityManagerInterface $em, FavoriteRepository $repo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // redirection vers la page login si pas connecté
            return $this->redirectToRoute('app_login');
        }

        // vérifier si déjà en favoris
        $existing = $repo->findOneBy(['user' => $user, 'post' => $post]);
        if (!$existing) {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setPost($post);
            $em->persist($favorite);
            $em->flush();
    }

    return $this->redirectToRoute('favorites_list');
    }

    #[Route('/remove/{id}', name: 'favorites_remove')]
    public function remove(Post $post, EntityManagerInterface $em, FavoriteRepository $repo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $favorite = $repo->findOneBy(['user' => $user, 'post' => $post]);

        if ($favorite) {
            $em->remove($favorite);
            $em->flush();
        }

        return $this->redirectToRoute('favorites_list');
    }
}
