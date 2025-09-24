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
    #[Route('/{id}', name: 'report_post')]
    public function report(Post $post, EntityManagerInterface $em, ReportRepository $repo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // éviter de signaler deux fois la même annonce
        $existing = $repo->findOneBy(['reporter' => $user, 'post' => $post]);

        if (!$existing) {
            $report = new Report();
            $report->setAuthor($post->getUser());
            $report->setReporter($user);
            $report->setPost($post);
            $report->setIsHandled(false);

            $em->persist($report);
            $em->flush();
        }

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/list', name: 'report_list')]
    public function list(ReportRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // réservé admin

        $reports = $repo->findAll();

        return $this->render('report/list.html.twig', [
            'reports' => $reports,
        ]);
    }
}
