<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l’utilisateur est déjà connecté, on le redirige vers son profil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile_show', [
                'id' => $this->getUser()->getId(),
            ]);
        }

        // Récupère la dernière erreur de connexion (s’il y en a une)
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Récupère le dernier identifiant saisi (utile pour pré-remplir le champ)
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche la page de connexion avec les infos d’erreur éventuelles
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    // Déconnexion de l’utilisateur
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est volontairement vide : Symfony gère la déconnexion via le firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
