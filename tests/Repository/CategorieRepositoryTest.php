<?php

namespace App\Tests\Repository;

use App\Entity\Categorie;
use App\Entity\Formation;
use App\Entity\Playlist;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategorieRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testFindAllForOnePlaylist()
    {
        $playlist = new Playlist();
        $playlist->setName('Playlist Test');

        $categorie1 = new Categorie();
        $categorie1->setName('Catégorie 1');

        $categorie2 = new Categorie();
        $categorie2->setName('Catégorie 2');

        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->setPlaylist($playlist);
        $formation1->addCategory($categorie1);

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->setPlaylist($playlist);
        $formation2->addCategory($categorie2);

        $this->entityManager->persist($playlist);
        $this->entityManager->persist($categorie1);
        $this->entityManager->persist($categorie2);
        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->flush();

        $categorieRepository = $this->entityManager->getRepository(Categorie::class);
        $categoriesForPlaylist = $categorieRepository->findAllForOnePlaylist($playlist->getId());

        $this->assertCount(2, $categoriesForPlaylist);
        $this->assertContains($categorie1, $categoriesForPlaylist);
        $this->assertContains($categorie2, $categoriesForPlaylist);
    }

    public function testFindOneByName()
    {
        $categorie = new Categorie();
        $categorie->setName('Catégorie Test');

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        $categorieRepository = $this->entityManager->getRepository(Categorie::class);
        $categorieFound = $categorieRepository->findOneByName('Catégorie Test');

        $this->assertSame($categorie, $categorieFound);
    }
    
    public function testAdd()
    {
        $categorie = new Categorie();
        $categorie->setName('Catégorie Test');

        $categorieRepository = $this->entityManager->getRepository(Categorie::class);
        $categorieRepository->add($categorie, true);

        $this->entityManager->clear();

        $categorieFound = $categorieRepository->findOneByName('Catégorie Test');
        $this->assertSame($categorie, $categorieFound);
    }

    public function testRemove()
    {
        $categorie = new Categorie();
        $categorie->setName('Catégorie Test');

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        $categorieRepository = $this->entityManager->getRepository(Categorie::class);
        $categorieRepository->remove($categorie, true);

        $this->entityManager->clear();

        $categorieFound = $categorieRepository->findOneByName('Catégorie Test');
        $this->assertNull($categorieFound);
    }
}