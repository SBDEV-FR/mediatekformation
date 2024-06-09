<?php

namespace App\Tests\Repository;

use App\Entity\Categorie;
use App\Entity\Formation;
use App\Entity\Playlist;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FormationRepositoryTest extends KernelTestCase
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

    public function testFindAllOrderBy()
    {
        $formation1 = new Formation();
        $formation1->setTitle('Formation A');
        $formation1->setPublishedAt(new \DateTime('2023-01-01'));

        $formation2 = new Formation();
        $formation2->setTitle('Formation B');
        $formation2->setPublishedAt(new \DateTime('2023-02-01'));

        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formations = $formationRepository->findAllOrderBy('publishedAt', 'DESC');

        $this->assertCount(2, $formations);
        $this->assertSame($formation2, $formations[0]);
        $this->assertSame($formation1, $formations[1]);
    }

    public function testFindByContainValue()
    {
        $formation1 = new Formation();
        $formation1->setTitle('Formation Test 1');

        $formation2 = new Formation();
        $formation2->setTitle('Formation Test 2');

        $formation3 = new Formation();
        $formation3->setTitle('Formation Autre');

        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->persist($formation3);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formations = $formationRepository->findByContainValue('title', 'Test');

        $this->assertCount(2, $formations);
        $this->assertContains($formation1, $formations);
        $this->assertContains($formation2, $formations);
        $this->assertNotContains($formation3, $formations);
    }

    public function testFindAllLasted()
    {
        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->setPublishedAt(new \DateTime('2023-01-01'));

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->setPublishedAt(new \DateTime('2023-02-01'));

        $formation3 = new Formation();
        $formation3->setTitle('Formation 3');
        $formation3->setPublishedAt(new \DateTime('2023-03-01'));

        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->persist($formation3);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formations = $formationRepository->findAllLasted(2);

        $this->assertCount(2, $formations);
        $this->assertSame($formation3, $formations[0]);
        $this->assertSame($formation2, $formations[1]);
    }

    public function testFindAllForOnePlaylist()
    {
        $playlist = new Playlist();
        $playlist->setName('Playlist Test');

        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->setPlaylist($playlist);

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->setPlaylist($playlist);

        $formation3 = new Formation();
        $formation3->setTitle('Formation 3');

        $this->entityManager->persist($playlist);
        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->persist($formation3);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formations = $formationRepository->findAllForOnePlaylist($playlist->getId());

        $this->assertCount(2, $formations);
        $this->assertContains($formation1, $formations);
        $this->assertContains($formation2, $formations);
        $this->assertNotContains($formation3, $formations);
    }

    public function testFindByCategorie()
    {
        $categorie = new Categorie();
        $categorie->setName('Catégorie Test');

        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->addCategory($categorie);

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->addCategory($categorie);

        $formation3 = new Formation();
        $formation3->setTitle('Formation 3');

        $this->entityManager->persist($categorie);
        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->persist($formation3);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formations = $formationRepository->findByCategorie($categorie);

        $this->assertCount(2, $formations);
        $this->assertContains($formation1, $formations);
        $this->assertContains($formation2, $formations);
        $this->assertNotContains($formation3, $formations);
    }
    
    public function testAdd()
    {
        $formation = new Formation();
        $formation->setTitle('Formation Test');

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formationRepository->add($formation, true);

        $this->entityManager->clear();

        $formationFound = $formationRepository->findOneBy(['title' => 'Formation Test']);
        $this->assertSame($formation, $formationFound);
    }

    public function testRemove()
    {
        $formation = new Formation();
        $formation->setTitle('Formation Test');

        $this->entityManager->persist($formation);
        $this->entityManager->flush();

        $formationRepository = $this->entityManager->getRepository(Formation::class);
        $formationRepository->remove($formation, true);

        $this->entityManager->clear();

        $formationFound = $formationRepository->findOneBy(['title' => 'Formation Test']);
        $this->assertNull($formationFound);
    }
}