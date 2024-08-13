<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Employer;
use App\Form\Employer1Type;
use App\Repository\EmployerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;


class EmployerController extends AbstractController
{

    private EncoderFactoryInterface $encoder;

    public function __construct(EncoderFactoryInterface $encoder)
    {
        $this->encoder = $encoder;
    }



    /**
     * @Route("/", name="employer_index", methods={"GET"})
     */
    public function index(): Response
    {
        $users = $this->getDoctrine()->getRepository(Employer::class)->findAll();
        return $this->render('employer/index.html.twig', [
            'employers' => $users
        ]);
    }
    /**
     * @Route("/home", name="home", methods={"GET"})
     */
    public function home(): Response
    {

        return $this->render('employer/home.html.twig');
    }
    /**
     * @Route("/new", name="employer_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'payload' => $this->createEmployerFromAjaxRequest($request)
            ));
        } else {
            $employer = new Employer();
            $form = $this->createForm(Employer1Type::class, $employer);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($employer);
                $entityManager->flush();



                return $this->redirectToRoute('employer_index');
            }

            return $this->render('employer/new.html.twig', [
                'employer' => $employer,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/{id}", name="employer_show", methods={"GET"})
     */
    public function show(Employer $employer): Response
    {
        return $this->render('employer/show.html.twig', [
            'employer' => $employer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="employer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Employer $employer): Response
    {
        $form = $this->createForm(Employer1Type::class, $employer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('employer_index');
        }

        return $this->render('employer/edit.html.twig', [
            'employer' => $employer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="employer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Employer $employer): Response
    {
        if ($this->isCsrfTokenValid('delete' . $employer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($employer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('employer_index');
    }


    // --------------------- helpers space ---------------------
    private function createEmployerFromAjaxRequest(Request $request)
    {
        /** @var Employer $employer */
        $salt = md5(microtime());
        $employer  = (new Employer())
            ->setPassword($request->request->get('password'))
            ->setAddress($request->request->get('address'))
            ->setPhone($request->request->get('phone'))
            ->setTown($request->request->get('town'))
            ->setFb($request->request->get('fb'))
            ->setLinkdin($request->request->get('linkdin'))
            ->setDescription($request->request->get('description'))
            ->setCategorie($request->request->get('categorie'))
            ->setCompany($request->request->get('company'));
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($employer);
        $manager->flush($employer);
        return $employer->getLinkdin();
    }
}
