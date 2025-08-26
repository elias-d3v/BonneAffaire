<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        $isOwner = $currentUser && $currentUser->getId() === $user->getId();

        // Si c'est le proprio → autoriser édition
        if ($isOwner) {
            $form = $this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'Profil mis à jour.');
                return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
            }

            return $this->render('profile/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'isOwner' => true,
            ]);
        }

        // Sinon → profil en lecture seule
        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'isOwner' => false,
        ]);
    }
}
