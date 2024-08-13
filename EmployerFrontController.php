<?php

namespace App\Controller;


use App\Entity\Employer;
use App\Entity\Urlizer;
use App\Entity\User;
use App\Form\Employer1Type;
use App\Repository\EmployerRepository;
use phpDocumentor\Reflection\Types\True_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/employer")
 */
class EmployerFrontController extends AbstractController
{
    private EncoderFactoryInterface $encoder;
    private UserPasswordEncoderInterface $pwdEncoder;
    public function __construct(EncoderFactoryInterface $encoder,UserPasswordEncoderInterface $enc)
    {
        $this->encoder = $encoder;
        $this->pwdEncoder = $enc;
    }
    /**
     * @Route("/", name="employer_front")
     */
    public function index(): Response
    {
        $users = $this->getDoctrine()->getRepository(Employer::class)->findAll();
        return $this->render('employer_front/index.html.twig', [
            'employers' => $users
        ]);
    }

    /**
     * @Route("/new", name="employer_newFront", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $employer = new Employer();
        $salt = md5(microtime());
        $form = $this->createForm(Employer1Type::class, $employer);

        $form->handleRequest($request);
        $encoder = $this->encoder->getEncoder(User::class);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $employer->setImg($newFilename);
            $entityManager = $this->getDoctrine()->getManager();
            $encodedPassword =$encoder->encodePassword($employer->getPassword(),$salt);
            $employer->setPassword($this->pwdEncoder->encodePassword($employer,$employer->getPassword()));
            $employer->setRoles(['ROLE_EMPLOYER']);
            $entityManager->persist($employer);
            $entityManager->flush();


            return $this->redirectToRoute('employer_front');
        }

        return $this->render('employer_front/register.html.twig', [
            'employer' => $employer,
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/{id}", name="employer_showFront", methods={"GET"})
     * @param $id
     * @return Response
     */
    public function show($id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        return $this->render('employer_front/show.html.twig', [
            'employer' => $employer,
        ]);

    }

    /**
     * @Route("/{id}/edit", name="employer_editFront", methods={"GET","POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request, $id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        $form = $this->createForm(Employer1Type::class, $employer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $employer->setImg($newFilename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('employer_front');
        }

        return $this->render('employer_front/edit.html.twig', [
            'employer' => $employer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="employer_deleteFront", methods={"DELETE"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function delete(Request $request, $id): Response
    {
        $employer = $this->getDoctrine()->getRepository(Employer::class)->find($id);
        if ($this->isCsrfTokenValid('delete' . $employer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($employer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('employer_front');
    }



    /**
     * @Route("/recherche", name="rechercheEmploye")
     */
    public function searchAction(Request $request)
    {

        $data = $request->request->get('search');


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Employer e
    WHERE e.categorie    LIKE :data')
            ->setParameter('data', '%'.$data.'%');


        $employers = $query->getResult();

        return $this->render('employer_front/index.html.twig', array(
            'employers' => $employers));

    }
    /**
     * @Route("/tri", name="triEmploye")
     */
    public function TriAction(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Employer e
    ORDER BY e.company ASC');



        $employers = $query->getResult();

        return $this->render('employer_front/index.html.twig', array(
            'employers' => $employers));

    }


    /**
     * @Route("/triTown", name="triEmployeTown")
     */
    public function TriActionTown(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Employer e
    ORDER BY e.town ASC');



        $employers = $query->getResult();

        return $this->render('employer_front/index.html.twig', array(
            'employers' => $employers));

    }
    /**
     * @Route("/triCategory", name="triEmployeCategory")
     */
    public function TriActionCategory(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Employer e
    ORDER BY e.categorie ASC');



        $employers = $query->getResult();

        return $this->render('employer_front/index.html.twig', array(
            'employers' => $employers));

    }


}
