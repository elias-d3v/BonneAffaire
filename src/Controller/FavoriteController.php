<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Post;
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
        $favorites = $repo->findBy(['user' => $this->getUser()]);

        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    #[Route('/favorites/toggle/{id}', name: 'favorites_toggle', methods: ['POST'])]
    public function toggle(Post $post, EntityManagerInterface $em, FavoriteRepository $repo): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['status' => 'unauthorized'], 401);
        }

        $existing = $repo->findOneBy(['user' => $user, 'post' => $post]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
            $em->refresh($user);
            return new JsonResponse(['status' => 'removed']);
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
