<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Message;
use App\Entity\User;
use App\Security\AccessChecker;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    // Affiche une conversation entre l'utilisateur connecté et un autre utilisateur
    #[Route('/conversation/{id}', name: 'messages_conversation')]
    public function conversation(User $user, MessageRepository $repo, EntityManagerInterface $em): Response
    {
        // Vérifie que l'utilisateur est connecté
        AccessChecker::checkAccess($this->getUser(), '');

        $me = $this->getUser();

        // Récupère tous les messages échangés entre les deux utilisateurs
        $messages = $repo->getConversation($me, $user);

        // Marquer les messages reçus comme lus
        foreach ($messages as $message) {
            if ($message->getReceiver() === $me && !$message->isRead()) {
                $message->setIsRead(true);
                $em->persist($message);
            }
        }
        $em->flush();

        // Affiche la conversation
        return $this->render('messages/conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $user,
        ]);
    }

    // Envoie un message à un utilisateur
    #[Route('/send/{id}', name: 'messages_send', methods: ['POST'])]
    public function send(User $user, Request $request, EntityManagerInterface $em): Response
    {
        AccessChecker::checkAccess($this->getUser(), '');

        // Récupère le contenu du message depuis le formulaire
        $content = $request->request->get('content');

        if ($content) {
            $message = new Message();
            $message->setSender($this->getUser());
            $message->setReceiver($user);
            $message->setContent($content);
            $message->setSentAt(new \DateTimeImmutable());

            $em->persist($message);
            $em->flush();
        }

        // Redirige vers la conversation après envoi
        return $this->redirectToRoute('messages_conversation', ['id' => $user->getId()]);
    }

    // Met à jour la partie des messages sans recharger toute la page (AJAX)
    #[Route('/fetch/{id}', name: 'messages_fetch')]
    public function fetch(User $user, MessageRepository $repo): Response
    {
        $messages = $repo->getConversation($this->getUser(), $user);

        return $this->render('messages/_conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $user,
        ]);
    }

    // Affiche la boîte de réception de l'utilisateur
    #[Route('/inbox', name: 'messages_inbox')]
    public function inbox(MessageRepository $repo): Response
    {
        AccessChecker::checkAccess($this->getUser(), '');
        $user = $this->getUser();

        // Récupère les conversations récentes
        $conversations = $repo->getConversations($user);

        // Compte le nombre total de messages non lus
        $unreadCount = $repo->countUnread($user);

        // Affiche la boîte de réception
        return $this->render('messages/inbox.html.twig', [
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
        ]);
    }

    // Permet de contacter le propriétaire d'une annonce
    #[Route('/contact/{id}', name: 'message_contact')]
    public function contact(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Redirige vers la connexion si non connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $receiver = $post->getUser();

        // Empêche l'utilisateur d'envoyer un message à lui-mêm
        if ($receiver === $user) {
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        // Message par défaut si aucun contenu n’est fourni
        $content = $request->request->get(
            'content', 
            "Bonjour ".$receiver->getName().", je suis intéressé par votre annonce. Cordialement, ".$user->getName()
        );

        // Création du message
        $message = new Message();
        $message->setSender($user);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setSentAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        // Redirige vers la boîte de réception
        return $this->redirectToRoute('messages_inbox');
    }
}
