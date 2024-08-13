<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Urlizer;
use App\Entity\User;
use App\Form\CandidatType;
use App\Repository\CandidatRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Skills;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/admin")
 */
class CandidatController extends AbstractController
{
    private EncoderFactoryInterface $encoder;
    private UserPasswordEncoderInterface $pwdEncoder;
    public function __construct(EncoderFactoryInterface $encoder,UserPasswordEncoderInterface $enc)
    {
        $this->encoder = $encoder;
        $this->pwdEncoder = $enc;
    }
    /**
     * @Route("/candidat", name="candidat_index", methods={"GET"})
     */
    public function index(): Response
    {
        $candidatRepository = $this->getDoctrine()->getRepository(Candidat::class);
        return $this->render('candidat/index.html.twig', [
            'candidats' => $candidatRepository->findAll(),
        ]);
    }

    /**
     * @Route("/newCandidat", name="candidat_new", methods={"GET","POST"})
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

            return $this->redirectToRoute('candidat_index');
        }

        return $this->render('candidat/new.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/candidat", name="candidat_show", methods={"GET"})
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function show(Request $request,$id): Response
    {
        $candidat = $this->getDoctrine()->getRepository(Candidat::class)->find($id);
        return $this->render('candidat/show.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="candidat_edit", methods={"GET","POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request,  $id): Response
    {
        $candidat = $this->getDoctrine()->getRepository(Candidat::class)->find($id);
        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('candidat_index');
        }

        return $this->render('candidat/edit.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/candidat", name="candidat_delete", methods={"DELETE"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function delete(Request $request, $id): Response
    {
        $candidat = $this->getDoctrine()->getRepository(Candidat::class)->find($id);
        if ($this->isCsrfTokenValid('delete'.$candidat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($candidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('candidat_index');
    }

    /**
     * @Route("candidat/searchStudentx ", name="searchStudentx")
     * @param Request $request
     * @param NormalizerInterface $Normalizer
     * @return Response
     * @throws ExceptionInterface
     */
    public function searchStudentx(Request $request,NormalizerInterface $Normalizer)
    {
        $repository = $this->getDoctrine()->getRepository(Candidat::class);
        $requestString=$request->get('searchValue');
        $candidats = $repository->findCandidatByTown($requestString);
        $jsonContent = $Normalizer->normalize($candidats, 'json',['groups'=>'candidats']);
        $retour = json_encode($jsonContent);
        return new Response($retour);

    }

    public function pdf()
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/mypdf.html.twig', [
            'title' => "Welcome to our PDF Test"
        ]);
        $html .= '<link type="text/css" href="../../../app-assets/vendors/css/vendors.min.css" rel="stylesheet" />';
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

}
