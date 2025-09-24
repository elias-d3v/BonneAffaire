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
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(UserRepository $userRepo, PostRepository $postRepo, ReportRepository $reportRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $userRepo->findAll();
        $posts = $postRepo->findAll();
        $reports = $reportRepo->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'posts' => $posts,
            'reports' => $reports,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/post/delete/{id}', name: 'admin_post_delete')]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Annonce supprimée.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/report/handle/{id}', name: 'admin_report_handle')]
    public function handleReport(Report $report, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $report->setIsHandled(true);
        $em->flush();

        $this->addFlash('success', 'Signalement marqué comme traité.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/post/validate/{id}', name: 'admin_post_validate')]
    public function validatePost(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post->setStatus('validated');
        $em->flush();

        $this->addFlash('success', 'Annonce validée.');
        return $this->redirectToRoute('admin_dashboard');
    }
}
