<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\Antispam;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(Request $request, \Swift_Mailer $mailer, Antispam $antispam)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($antispam->isSpam($data['message'])) {
                throw new \Exception('Votre message a été détecté comme spam car il comporte moins de 50 caracteres.');
            }

            $message = (new \Swift_Message('Message de communaute-ebeniste.fr'))
                ->setFrom($data['email'])
                ->setTo('testcommunaute-ebeniste@yopmail.com')
                ->setBody(
                    $this->renderView(
                        'contact/mail.html.twig',
                        array('data' => $data)
                    ),
                    'text/html'
                );
            $mailer->send($message);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
