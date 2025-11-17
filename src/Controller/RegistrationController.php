<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    // Route d’inscription d’un nouvel utilisateur
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        // Création du formulaire à partir de l’entité User
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérifie que le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Récupère et hache le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Définit les autres champs du profil
            $user->setName($form->get('name')->getData());
            $user->setPhone($form->get('phone')->getData());
            $user->setIpAddress($request->getClientIp() ?? '127.0.0.1');
            $user->setRoles(['ROLE_USER']);

            // Gestion de l’upload de l’avatar
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $user->setAvatar($newFilename);
            }

            // Enregistre l’utilisateur en base
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Affiche la page d’inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
