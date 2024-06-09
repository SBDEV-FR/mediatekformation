<?php

namespace App\Tests\Repository;

use App\Entity\Formation;
use App\Entity\Playlist;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlaylistRepositoryTest extends KernelTestCase
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

    public function testFindAllOrderByName()
    {
        $playlist1 = new Playlist();
        $playlist1->setName('Playlist A');

        $playlist2 = new Playlist();
        $playlist2->setName('Playlist B');

        $this->entityManager->persist($playlist1);
        $this->entityManager->persist($playlist2);
        $this->entityManager->flush();

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $playlists = $playlistRepository->findAllOrderByName('ASC');

        $this->assertCount(2, $playlists);
        $this->assertSame($playlist1, $playlists[0]);
        $this->assertSame($playlist2, $playlists[1]);
    }

    public function testCountFormationsForPlaylist()
    {
        $playlist = new Playlist();
        $playlist->setName('Playlist Test');

        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->setPlaylist($playlist);

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->setPlaylist($playlist);

        $this->entityManager->persist($playlist);
        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->flush();

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $formationCount = $playlistRepository->countFormationsForPlaylist($playlist->getId());

        $this->assertSame(2, $formationCount);
    }

    public function testAdd()
    {
        $playlist = new Playlist();
        $playlist->setName('Playlist Test');

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $playlistRepository->add($playlist, true);

        $this->entityManager->clear();

        $playlistFound = $playlistRepository->findOneBy(['name' => 'Playlist Test']);
        $this->assertSame($playlist, $playlistFound);
    }

    public function testRemove()
    {
        $playlist = new Playlist();
        $playlist->setName('Playlist Test');

        $this->entityManager->persist($playlist);
        $this->entityManager->flush();

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $playlistRepository->remove($playlist, true);

        $this->entityManager->clear();

        $playlistFound = $playlistRepository->findOneBy(['name' => 'Playlist Test']);
        $this->assertNull($playlistFound);
    }

    public function testFindByContainValue()
    {
        $playlist1 = new Playlist();
        $playlist1->setName('Playlist Test 1');

        $playlist2 = new Playlist();
        $playlist2->setName('Playlist Test 2');

        $playlist3 = new Playlist();
        $playlist3->setName('Playlist Autre');

        $this->entityManager->persist($playlist1);
        $this->entityManager->persist($playlist2);
        $this->entityManager->persist($playlist3);
        $this->entityManager->flush();

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $playlists = $playlistRepository->findByContainValue('name', 'Test');

        $this->assertCount(2, $playlists);
        $this->assertContains($playlist1, $playlists);
        $this->assertContains($playlist2, $playlists);
        $this->assertNotContains($playlist3, $playlists);
    }

    public function testFindAllOrderByNbre()
    {
        $playlist1 = new Playlist();
        $playlist1->setName('Playlist 1');

        $playlist2 = new Playlist();
        $playlist2->setName('Playlist 2');

        $formation1 = new Formation();
        $formation1->setTitle('Formation 1');
        $formation1->setPlaylist($playlist1);

        $formation2 = new Formation();
        $formation2->setTitle('Formation 2');
        $formation2->setPlaylist($playlist1);

        $formation3 = new Formation();
        $formation3->setTitle('Formation 3');
        $formation3->setPlaylist($playlist2);

        $this->entityManager->persist($playlist1);
        $this->entityManager->persist($playlist2);
        $this->entityManager->persist($formation1);
        $this->entityManager->persist($formation2);
        $this->entityManager->persist($formation3);
        $this->entityManager->flush();

        $playlistRepository = $this->entityManager->getRepository(Playlist::class);
        $playlists = $playlistRepository->findAllOrderByNbre('DESC');

        $this->assertCount(2, $playlists);
        $this->assertSame($playlist1, $playlists[0]);
        $this->assertSame($playlist2, $playlists[1]);
    }

}