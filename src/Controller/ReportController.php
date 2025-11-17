<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\Report;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/report')]
class ReportController extends AbstractController
{
    // Permet à un utilisateur de signaler une annonce
    #[Route('/{id}', name: 'report_post')]
    public function report(Post $post, EntityManagerInterface $em, ReportRepository $repo): Response
    {
        $user = $this->getUser();

        // Si l’utilisateur n’est pas connecté → redirection vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifie si l’utilisateur a déjà signalé cette annonce
        $existing = $repo->findOneBy(['reporter' => $user, 'post' => $post]);

        // Si aucun signalement existant, on en crée un nouveau
        if (!$existing) {
            $report = new Report();
            $report->setAuthor($post->getUser());   // propriétaire de l’annonce
            $report->setReporter($user);            // utilisateur qui signale
            $report->setPost($post);
            $report->setIsHandled(false);           // non traité par défaut

            $em->persist($report);
            $em->flush();
        }

        // Retour à la page de l’annonce
        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    // Liste tous les signalements (accès réservé à l’administrateur)
    #[Route('/list', name: 'report_list')]
    public function list(ReportRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // réservé admin

        // Récupère tous les signalements
        $reports = $repo->findAll();

        // Affiche la liste dans le tableau d’administration
        return $this->render('report/list.html.twig', [
            'reports' => $reports,
        ]);
    }
}
