<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\User;
use App\Form\ContactType;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class ContactController extends AbstractController
{


    /**
     * @Route("con/contact/{id}", name="contact" )
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param $id
     * @return RedirectResponse|Response
     */
    public function index(Request $request, Swift_Mailer $mailer, $id)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $contact = $form->getData();

$message = (new \Swift_Message('New Contact'))
    ->setFrom($user->getAddress())
    ->setTo($contact['email'])
    ->setBody(
        $this->renderView(
            'emails/contact.html.twig',compact('contact')
    ),
    'text/html'
    );
     $mailer->send($message);
     $this->addFlash('message','the message is sent successfully');
     return $this->redirectToRoute('employer_front');
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
