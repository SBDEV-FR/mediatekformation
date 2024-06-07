<?php
namespace App\Controller;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\PlaylistRepository;
use App\Repository\FormationRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficePlaylistController extends AbstractController
{
    private $playlistRepository;
    private $formationRepository;
    private $categorieRepository;
    private $entityManager;

    public function __construct(PlaylistRepository $playlistRepository, FormationRepository $formationRepository, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager)
    {
        $this->playlistRepository = $playlistRepository;
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/playlists", name="admin_playlists")
     */
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAll();
        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/playlists/index.html.twig', [
            'playlists' => $playlists,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin/playlists/new", name="admin_playlists_new")
     */
    public function new(Request $request): Response
    {
        $playlist = new Playlist();
        $form = $this->createForm(PlaylistType::class, $playlist);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($playlist);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/playlists/{id}/edit", name="admin_playlists_edit")
     */
    public function edit(Request $request, Playlist $playlist): Response
    {
        $form = $this->createForm(PlaylistType::class, $playlist);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/edit.html.twig', [
            'form' => $form->createView(),
            'playlist' => $playlist,
        ]);
    }

    /**
     * @Route("/admin/playlists/{id}/delete", name="admin_playlists_delete", methods={"POST"})
     */
    public function delete(Request $request, Playlist $playlist): Response
    {
        if ($this->isCsrfTokenValid('delete'.$playlist->getId(), $request->request->get('_token'))) {
            // Vérifier si la playlist contient des formations
            if ($playlist->getFormations()->count() > 0) {
                $this->addFlash('error', 'La playlist ne peut pas être supprimée car elle contient des formations.');
                return $this->redirectToRoute('admin_playlists');
            }

            $this->entityManager->remove($playlist);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_playlists');
    }

    /**
     * @Route("/admin/playlists/tri/{champ}/{ordre}", name="admin_playlists_sort")
     */
    public function sort($champ, $ordre): Response
    {
        switch ($champ) {
            case "name":
                $playlists = $this->playlistRepository->findAllOrderByName($ordre);
                break;
            case "nbre":
                $playlists = $this->playlistRepository->findAllOrderByNbre($ordre);
                break;
        }

        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/playlists/index.html.twig', [
            'playlists' => $playlists,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin/playlists/recherche/{champ}/{table}", name="admin_playlists_findallcontain")
     */
    public function findAllContain($champ, Request $request, $table=""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/playlists/index.html.twig', [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table,
        ]);
    }
}