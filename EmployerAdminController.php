<?php

namespace App\Controller;

use App\Entity\Employer;
use App\Form\Employer1Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/admin")
 */
class EmployerAdminController extends AbstractController
{
    /**
     * @Route("/", name="employer_admin")
     */
    public function index(): Response
    {$users = $this->getDoctrine()->getRepository(Employer::class)->findAll();
        return $this->render('employer_admin/index.html.twig', [
            'controller_name' => 'EmployerAdminController','employers' => $users
        ]);
    }
    /**
     * @Route("/new", name="employer_newAdmin", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
            $employer = new Employer();
            $form = $this->createForm(Employer1Type::class, $employer);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($employer);
                $entityManager->flush();



                return $this->redirectToRoute('employer_admin');
            }

            return $this->render('employer_admin/newAdmin.html.twig', [
                'employer' => $employer,
                'form' => $form->createView(),
            ]);


    }

    /**
     * @Route("/{id}/edit", name="employer_editAdmin", methods={"GET","POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request,$id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        $form = $this->createForm(Employer1Type::class, $employer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('employer_admin');
        }

        return $this->render('employer_admin/edit.html.twig', [
            'employer' => $employer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="employer_showAdmin", methods={"GET"})
     * @param $id
     * @return Response
     */
    public function show($id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        return $this->render('employer_admin/show.html.twig', [
            'employer' => $employer,
        ]);
    }

    /**
     * @Route("/{id}", name="employer_deleteAdmin", methods={"DELETE"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function delete(Request $request,$id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        if ($this->isCsrfTokenValid('delete' . $employer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($employer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('employer_admin');
    }
    /**
     * @Route("candidat/searchStudentx ", name="searchStudentx")
     */
    public function searchStudentx(Request $request,NormalizerInterface $Normalizer)
    {
        $repository = $this->getDoctrine()->getRepository(Employer::class);
        $requestString=$request->get('searchValue');
        $employers = $repository->findEmployerByCategorie($requestString);
        $jsonContent = $Normalizer->normalize($employers, 'json',['groups'=>'employers']);
        $retour = json_encode($jsonContent);
        return new JsonResponse($jsonContent);

    }
}
