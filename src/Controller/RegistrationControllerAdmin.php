<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormTypeAdmin;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationControllerAdmin extends AbstractController
{
    #[Route('/adminregister', name: 'app_register_admin')]
    public function register(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormTypeAdmin::class, $user);
        $form->handleRequest($request);
        $message = null;

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            // $user->setPassword(
            // $userPasswordHasher->hashPassword(
            //         $user,
            //         $form->get('plainPassword')->getData()
            //     )
            // );

            $test = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($test){
                $message = 'Cet email est déjà utilisé';
            }

            else{
                $user->setNom($form->get('nom')->getData());
                $user->setPrenom($form->get('prenom')->getData());
                $user->setEmail($form->get('email')->getData());
                $user->setPoste($form->get('poste')->getData());

                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email

                $email = new TemplatedEmail();

                $email->from('yamjk1er@gmail.com');
                $email->to($form->get('email')->getData());
                $email->subject('Création de compte Africa Etudes');
                $email->htmlTemplate('email/email.html.twig');
                $email->context([
                    'nom' => $form->get('nom')->getData(),
                    'prenom' => $form->get('prenom')->getData(),
                    'mail' => $form->get('email')->getData(),
                    'poste' => $form->get('poste')->getData(),
                ]);

                $mailer->send($email);

                return $this->redirectToRoute('app_projets');
            }            
        }

        return $this->render('registration/register_admin.html.twig', [
            'registrationForm' => $form->createView(),
            'message' => $message,
        ]);
    }
}
