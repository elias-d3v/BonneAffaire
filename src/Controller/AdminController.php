<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Repository\PostRepository;
use App\Repository\ReportRepository;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Report;

final class AdminController extends AbstractController
{
    // Tableau de bord administrateur
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(UserRepository $userRepo, PostRepository $postRepo, ReportRepository $reportRepo): Response
    {
        // Vérifie que l'utilisateur connecté est administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupère toutes les données nécessaires
        $users = $userRepo->findAll();
        $posts = $postRepo->findAll();
        $reports = $reportRepo->findAll();

        // Affiche la page du tableau de bord
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'posts' => $posts,
            'reports' => $reports,
        ]);
    }

    // Suppression d'un utilisateur
    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }

    // Suppression d'une annonce
    #[Route('/admin/post/delete/{id}', name: 'admin_post_delete')]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }

    // Marque un signalement comme traité
    #[Route('/admin/report/handle/{id}', name: 'admin_report_handle')]
    public function handleReport(Report $report, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $report->setIsHandled(true);
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }

    // Validation d'une annonce en attente
    #[Route('/admin/post/validate/{id}', name: 'admin_post_validate')]
    public function validatePost(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post->setStatus('validated');
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }
}
