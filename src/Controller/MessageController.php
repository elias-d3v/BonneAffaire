<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    #[Route('/conversation/{id}', name: 'messages_conversation')]
    public function conversation(User $user, MessageRepository $repo, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $me = $this->getUser();
        $messages = $repo->getConversation($me, $user);

        // Marquer tous les messages reçus comme lus
        foreach ($messages as $message) {
            if ($message->getReceiver() === $me && !$message->isRead()) {
                $message->setIsRead(true);
                $em->persist($message);
            }
        }
        $em->flush();

        return $this->render('messages/conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $user,
        ]);
    }

    #[Route('/send/{id}', name: 'messages_send', methods: ['POST'])]
    public function send(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

        return $this->redirectToRoute('messages_conversation', ['id' => $user->getId()]);
    }

    #[Route('/fetch/{id}', name: 'messages_fetch')]
    public function fetch(User $user, MessageRepository $repo): Response
    {
        $messages = $repo->getConversation($this->getUser(), $user);

        return $this->render('messages/_conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $user,
        ]);
    }

    #[Route('/inbox', name: 'messages_inbox')]
    public function inbox(MessageRepository $repo): Response
    {
        $user = $this->getUser();
        $conversations = $repo->getConversations($user);

        // Compte total des non lus
        $unreadCount = $repo->countUnread($user);

        return $this->render('messages/inbox.html.twig', [
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/contact/{id}', name: 'message_contact')]
    public function contact(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $receiver = $post->getUser();
        if ($receiver === $user) {
            $this->addFlash('warning', 'Vous ne pouvez pas envoyer de message à vous-même.');
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        $content = $request->request->get('content', "Bonjour ".$receiver->getName().", je suis intéressé par votre annonce. Cordialement, ".$user->getName());

        $message = new Message();
        $message->setSender($user);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setSentAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        $this->addFlash('success', 'Votre message a été envoyé à '.$receiver->getName());
        return $this->redirectToRoute('messages_inbox');
    }
}
