<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Candidat;
use App\Entity\Urlizer;
use App\Entity\User;
use App\Form\CandidatType;
use App\Repository\CandidatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
/**
 * @Route("/candidat")
 */
class CandidatFrontController extends AbstractController
{
    private EncoderFactoryInterface $encoder;
    private UserPasswordEncoderInterface $pwdEncoder;
    public function __construct(EncoderFactoryInterface $encoder,UserPasswordEncoderInterface $enc)
    {
        $this->encoder = $encoder;
        $this->pwdEncoder = $enc;
    }

    /**
     * @Route("/", name="candidat_front")
     */
    public function index(): Response
    {
        $candidatRepository = $this->getDoctrine()->getRepository(Candidat::class);
        return $this->render('candidat_front/index.html.twig', [
            'candidats' => $candidatRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="candidat_newFront", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $candidat = new Candidat();
        $salt = md5(microtime());
        $form = $this->createForm(CandidatType::class, $candidat);
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
            $candidat->setImg($newFilename);
            $entityManager = $this->getDoctrine()->getManager();
            $encodedPassword =$encoder->encodePassword($candidat->getPassword(),$salt);
            $candidat->setPassword($this->pwdEncoder->encodePassword($candidat,$candidat->getPassword()));
            $candidat->setRoles(['ROLE_CANDIDATE']);
            $entityManager->persist($candidat);
            $entityManager->flush();

            return $this->redirectToRoute('candidat_front');
        }

        return $this->render('candidat_front/register.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="candidat_showFront", methods={"GET"})
     */
    public function show(Candidat $candidat): Response
    {
        return $this->render('candidat_front/show.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    /**
     * @Route("/{id}/editFront", name="candidat_editFront", methods={"GET","POST"})
     */
    public function edit(Request $request, Candidat $candidat): Response
    {
        $form = $this->createForm(CandidatType::class, $candidat);
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
            $candidat->setImg($newFilename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('candidat_front');
        }

        return $this->render('candidat_front/edit.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="candidat_deleteFront", methods={"DELETE"})
     */
    public function delete(Request $request, Candidat $candidat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($candidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('candidat_front');
    }

    /**
     * @Route("/recherche", name="rechercheCandidat")
     */
    public function searchAction(Request $request)
    {

        $data = $request->request->get('search');


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Candidat e
    WHERE e.town    LIKE :data')
            ->setParameter('data', '%'.$data.'%');


        $candidats = $query->getResult();

        return $this->render('candidat_front/index.html.twig', array(
            'candidats' => $candidats));

    }
    /**
     * @Route("/tri", name="triNomCandidat")
     */
    public function TriAction(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Candidat e
    ORDER BY e.nom ASC');




        $candidats = $query->getResult();

        return $this->render('candidat_front/index.html.twig', array(
            'candidats' => $candidats));

    }
    /**
     * @Route("/tri", name="triCandidat")
     */
    public function TriActionCandidat(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Candidat e
    where e.type_candidat = candidat ' );



        $candidats = $query->getResult();

        return $this->render('candidat_front/index.html.twig', array(
            'candidats' => $candidats));

    }
    /**
     * @Route("/tri", name="triStagiaire")
     */
    public function TriActionStagiaire(Request $request)
    {




        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT e FROM App\Entity\Candidat e
    where e.type_candidat = stagiaire ');



        $candidats = $query->getResult();

        return $this->render('candidat_front/index.html.twig', array(
            'candidats' => $candidats));

    }

    /**
     * @Route("/cv/{id}", name="candidat_cvFront", methods={"GET"})
     */
    public function showCV(Candidat $candidat): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);


        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('candidat_front/cv.html.twig', [
            'candidat' => $candidat,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);


    }


}
