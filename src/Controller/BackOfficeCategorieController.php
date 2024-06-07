<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeCategorieController extends AbstractController
{
    private $categorieRepository;
    private $formationRepository;
    private $entityManager;

    public function __construct(CategorieRepository $categorieRepository, FormationRepository $formationRepository, EntityManagerInterface $entityManager)
    {
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/categories", name="admin_categories")
     */
    public function index(Request $request): Response
    {
        $categories = $this->categorieRepository->findAll();
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existingCategorie = $this->categorieRepository->findOneByName($categorie->getName());
            if ($existingCategorie) {
                $this->addFlash('error', 'Cette catégorie existe déjà.');
            } else {
                $this->entityManager->persist($categorie);
                $this->entityManager->flush();
                $this->addFlash('success', 'La catégorie a été ajoutée avec succès.');
            }

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/categories/{id}/delete", name="admin_categories_delete", methods={"POST"})
     */
    public function delete(Request $request, Categorie $categorie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            // Vérifier si la catégorie est rattachée à des formations
            $formations = $this->formationRepository->findByCategorie($categorie);
            if (count($formations) > 0) {
                $this->addFlash('error', 'La catégorie ne peut pas être supprimée car elle est rattachée à des formations.');
                return $this->redirectToRoute('admin_categories');
            }

            $this->entityManager->remove($categorie);
            $this->entityManager->flush();
            $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_categories');
    }
}