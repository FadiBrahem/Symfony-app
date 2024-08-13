<?php

namespace App\Controller;

use App\Entity\Skills;
use App\Form\SkillsType;
use App\Repository\SkillsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Candidat;

/**
 * @Route("/skill")
 */
class SkillsController extends AbstractController
{
    /**
     * @Route("/skill", name="skills_index", methods={"GET"})
     */
    public function index(): Response
    {
        $skillsRepository = $this->getDoctrine()->getRepository(Skills::class);
        return $this->render('skills/index.html.twig', [
            'skills' => $skillsRepository->findAll(),
        ]);
    }

    /**
     * @Route("skill/newSkill", name="skills_newSkill", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $skill = new Skills();
        $form = $this->createForm(SkillsType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($skill);
            $entityManager->flush();

            return $this->redirectToRoute('skills_index');
        }

        return $this->render('skills/new.html.twig', [
            'skill' => $skill,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="skills_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $skillRepo = $this->getDoctrine()->getRepository(Skills::class);
        $skill = $id !== null ? $skillRepo->find($id) : $skillRepo->findAll();
        return $this->render('skills/show.html.twig', [
            'skill' => $skill,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="skills_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id): Response
    {
        $skill = $this->getDoctrine()->getRepository(Skills::class)->find($id);
        $form = $this->createForm(SkillsType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('skills_index');
        }

        return $this->render('skills/edit.html.twig', [
            'skill' => $skill,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="skills_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id): Response
    {
        $skill = $this->getDoctrine()->getRepository(Skills::class)->find($id);
        if ($this->isCsrfTokenValid('delete'.$skill->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($skill);
            $entityManager->flush();
        }

        return $this->redirectToRoute('skills_index');
    }
}
