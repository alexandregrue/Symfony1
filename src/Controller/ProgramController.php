<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Form\ProgramType;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Service\Slugify;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @Route("/program/", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render(
            'program/index.html.twig',
            ['programs' => $programs,]
        );
    }

    /**
     * @Route("new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        // Create a new Program Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                    ->from($this->getParameter('mailer_from'))
                    ->to('your_email@example.com')
                    ->subject('Une nouvelle série vient d\'être publiée !')
                    ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("{slug}", methods={"GET"}, name="show")
     * @return Response
     */
    public function show(Program $program): Response
    {
        
        if(!$program) {
            throw $this->createNotFoundException(
                'No program with id = ' . $program . 'found in program\'s table.'
            );
        }

        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }

    /**
     * @Route("{slug}/season/{season}", requirements={"season"="\d+"}, methods={"GET"}, name="season_show")
     * @return Response
     */
    public function showSeason(Program $program, Season $season): Response
    {
        
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id = ' . $program . 'found in program\'s table.'
            );
        }

        if (!$season) {
            throw $this->createNotFoundException(
                'No season with id = ' . $season . 'found in season\'s table.'
            );
        }
        

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
        ]);
    }

    /**
     * @Route("{program_slug}/season/{season}/episode/{episode_slug}",
     * requirements={"program"="\d+", "season"="\d+", "episode"="\d+"},
     * methods={"GET"}, name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episode": "slug"}})
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id = ' . $program . 'found in program\'s table.'
            );
        }

        if (!$season) {
            throw $this->createNotFoundException(
                'No season with id = ' . $season . 'found in season\'s table.'
            );
        }

        if (!$episode) {
            throw $this->createNotFoundException(
                'No episode with id = ' . $episode . 'found in episode\'s table.'
            );
        }
        
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
        ]);
    }
}
