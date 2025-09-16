<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('', name: 'contact_form', methods: ['GET', 'POST'])]
    public function index(Request $req, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $msg = new ContactMessage();
        $form = $this->createForm(ContactType::class, $msg);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($msg);
            $em->flush();

            $email = (new Email())
                ->from($msg->getEmail())
                ->to($_ENV['CONTACT_EMAIL'])
                ->subject('New message')
                ->text($msg->getMessage());
            $mailer->send($email);

            $this->addFlash('success', 'Sent!');
            return $this->redirectToRoute('contact_form');
        }

        return $this->render('contact/form.html.twig', ['form' => $form->createView()]);
    }
}
