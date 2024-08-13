<?php

namespace App\Controller;

use App\Entity\Stage;
use App\Form\StageType;
use App\Repository\StageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stageAdmin")
 */
class StageAdminController extends AbstractController
{
    /**
     * @Route("/", name="stage_admin_index", methods={"GET"})
     */
    public function index(StageRepository $stageRepository): Response
    {
        $stages = $this->getDoctrine()->getRepository(Stage::class)
            ->findAll();
        return $this->render('stage_admin/index.html.twig', [
            'stages' => $stages,
        ]);
    }

    /**
     * @Route("/new", name="stage_admin_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $stage = new Stage();
        $form = $this->createForm(StageType::class, $stage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($stage);
            $entityManager->flush();

            return $this->redirectToRoute('stage_admin_index');
        }

        return $this->render('stage_admin/new.html.twig', [
            'stage' => $stage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="stage_admin_show", methods={"GET"})
     */
    public function show(Stage $stage): Response
    {
        return $this->render('stage_admin/show.html.twig', [
            'stage' => $stage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="stage_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Stage $stage): Response
    {
        $form = $this->createForm(StageType::class, $stage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('stage_admin_index');
        }

        return $this->render('stage_admin/edit.html.twig', [
            'stage' => $stage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="stage_admin_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Stage $stage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($stage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('stage_admin_index');
    }
}
