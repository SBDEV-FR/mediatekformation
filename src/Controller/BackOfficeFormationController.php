<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeFormationController extends AbstractController
{
    private $formationRepository;
    private $playlistRepository;
    private $categorieRepository;
    private $entityManager;

    public function __construct(FormationRepository $formationRepository, PlaylistRepository $playlistRepository, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager)
    {
        $this->formationRepository = $formationRepository;
        $this->playlistRepository = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/formations", name="admin_formations")
     */
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render('admin/formations/index.html.twig', [
            'formations' => $formations,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin/formations/new", name="admin_formations_new")
     */
    public function new(Request $request): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($formation);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/formations/{id}/edit", name="admin_formations_edit")
     */
    public function edit(Request $request, Formation $formation): Response
    {
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/edit.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    /**
     * @Route("/admin/formations/{id}/delete", name="admin_formations_delete", methods={"POST"})
     */
    public function delete(Request $request, Formation $formation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            // Supprimer la formation de la playlist associée
            $playlist = $formation->getPlaylist();
            if ($playlist) {
                $playlist->removeFormation($formation);
                $this->entityManager->persist($playlist);
            }

            $this->entityManager->remove($formation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_formations');
    }

    /**
     * @Route("/admin/formations/tri/{champ}/{ordre}/{table}", name="admin_formations_sort")
     */
    public function sort($champ, $ordre, $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/formations/index.html.twig', [
            'formations' => $formations,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin/formations/recherche/{champ}/{table}", name="admin_formations_findallcontain")
     */
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/formations/index.html.twig', [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table,
        ]);
    }
}