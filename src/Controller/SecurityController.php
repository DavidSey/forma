<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, ObjectManager $manager, User $user = null): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $password
            ));
            $manager->persist($user);
            $manager->flush();
//            $node= '<user><id>111</id><email>'.$_POST['user']['email'].'</email><password>'.$_POST['user']['password'].'</password><roles>ROLE_USER</roles></user>';
//            dump($user); die();
            libxml_disable_entity_loader(false);
            $xml= new \XMLWriter();
            $xml->openUri('file.xml');
            $xml->startDocument('1.0', 'utf-8');
            $xml->startElement('users');
            $xml->startElement('user');
            $xml->writeElement('id', $user->getId());
            $xml->writeElement('email', $_POST['user']['email']);
            $xml->writeElement('password', $_POST['user']['password']);
            $xml->writeElement('roles', 'ROLE_USER');
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
            $xml->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('security/register.html.twig', [
        'form' => $form->createView()
        ]);
    }
}
