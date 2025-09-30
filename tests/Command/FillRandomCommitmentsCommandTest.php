<?php

namespace App\Tests\Command;

use App\Entity\CandidateList;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FillRandomCommitmentsCommandTest extends KernelTestCase
{
    private $entityManager;
    private $application;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->application = new Application($kernel);

        // Nettoyer la base de données
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testExecuteWithDryRun(): void
    {
        // Créer des données de test
        $this->createTestData();

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter en mode dry-run
        $commandTester->execute([
            '--dry-run' => true,
            '--percentage' => 50,
            '--acceptance-rate' => 60,
        ]);

        // Vérifier que la commande s'est bien exécutée
        $this->assertEquals(0, $commandTester->getStatusCode());

        // Vérifier qu'aucun engagement n'a été créé (dry-run)
        $commitments = $this->entityManager->getRepository(Commitment::class)->findAll();
        $this->assertCount(0, $commitments);

        // Vérifier la sortie
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('MODE DRY-RUN', $output);
        $this->assertStringContainsString('Listes candidates trouvées : 3', $output);
        $this->assertStringContainsString('Propositions trouvées : 5', $output);
    }

    public function testExecuteWithForce(): void
    {
        // Créer des données de test
        $this->createTestData();

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec force
        $commandTester->execute([
            '--force' => true,
            '--percentage' => 80,
            '--acceptance-rate' => 60,
        ]);

        // Vérifier que la commande s'est bien exécutée
        $this->assertEquals(0, $commandTester->getStatusCode());

        // Vérifier que des engagements ont été créés
        $commitments = $this->entityManager->getRepository(Commitment::class)->findAll();
        $this->assertGreaterThan(0, count($commitments));

        // Vérifier la sortie
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Remplissage terminé !', $output);
        $this->assertStringContainsString('Engagements créés', $output);
    }

    public function testExecuteWithClear(): void
    {
        // Créer des données de test avec des engagements existants
        $this->createTestData();
        $this->createExistingCommitments();

        // Vérifier qu'il y a des engagements existants
        $commitmentsBefore = $this->entityManager->getRepository(Commitment::class)->findAll();
        $this->assertGreaterThan(0, count($commitmentsBefore));

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec clear et force
        $commandTester->execute([
            '--clear' => true,
            '--force' => true,
            '--percentage' => 50,
            '--acceptance-rate' => 50,
        ]);

        // Vérifier que la commande s'est bien exécutée
        $this->assertEquals(0, $commandTester->getStatusCode());

        // Vérifier que de nouveaux engagements ont été créés
        $commitmentsAfter = $this->entityManager->getRepository(Commitment::class)->findAll();
        $this->assertGreaterThan(0, count($commitmentsAfter));

        // Vérifier la sortie
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('engagements supprimés', $output);
        $this->assertStringContainsString('Remplissage terminé !', $output);
    }

    public function testAcceptanceRateIsRespected(): void
    {
        // Créer des données de test
        $this->createTestData();

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec 100% d'acceptation
        $commandTester->execute([
            '--force' => true,
            '--percentage' => 100,
            '--acceptance-rate' => 100,
        ]);

        // Vérifier que tous les engagements sont acceptés
        $commitments = $this->entityManager->getRepository(Commitment::class)->findAll();
        $acceptedCount = 0;
        foreach ($commitments as $commitment) {
            if ($commitment->getStatus() === CommitmentStatus::ACCEPTED) {
                $acceptedCount++;
            }
        }

        $this->assertEquals(count($commitments), $acceptedCount);
    }

    public function testRefusalRateIsRespected(): void
    {
        // Créer des données de test
        $this->createTestData();

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec 0% d'acceptation (100% de refus)
        $commandTester->execute([
            '--force' => true,
            '--percentage' => 100,
            '--acceptance-rate' => 0,
        ]);

        // Vérifier que tous les engagements sont refusés
        $commitments = $this->entityManager->getRepository(Commitment::class)->findAll();
        $refusedCount = 0;
        foreach ($commitments as $commitment) {
            if ($commitment->getStatus() === CommitmentStatus::REFUSED) {
                $refusedCount++;
            }
        }

        $this->assertEquals(count($commitments), $refusedCount);
    }

    public function testInvalidPercentage(): void
    {
        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec un pourcentage invalide
        $commandTester->execute([
            '--force' => true,
            '--percentage' => 150,
        ]);

        // Vérifier que la commande a échoué
        $this->assertEquals(1, $commandTester->getStatusCode());

        // Vérifier le message d'erreur
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Le pourcentage doit être entre 0 et 100', $output);
    }

    public function testInvalidAcceptanceRate(): void
    {
        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter avec un taux d'acceptation invalide
        $commandTester->execute([
            '--force' => true,
            '--acceptance-rate' => -10,
        ]);

        // Vérifier que la commande a échoué
        $this->assertEquals(1, $commandTester->getStatusCode());

        // Vérifier le message d'erreur
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Le taux d\'acceptation doit être entre 0 et 100', $output);
    }

    public function testCommentsAreGenerated(): void
    {
        // Créer des données de test
        $this->createTestData();

        $command = $this->application->find('app:fill-random-commitments');
        $commandTester = new CommandTester($command);

        // Exécuter
        $commandTester->execute([
            '--force' => true,
            '--percentage' => 100,
            '--acceptance-rate' => 50,
        ]);

        // Vérifier que tous les engagements ont un commentaire
        $commitments = $this->entityManager->getRepository(Commitment::class)->findAll();
        foreach ($commitments as $commitment) {
            $this->assertNotEmpty($commitment->getCommentCandidateList());
        }
    }

    private function createTestData(): void
    {
        // Créer des villes
        $cities = [];
        for ($i = 1; $i <= 3; $i++) {
            $city = new City();
            $city->setName('Ville Test ' . $i . ' ' . uniqid());
            $this->entityManager->persist($city);
            $cities[] = $city;
        }

        // Créer des listes candidates
        foreach ($cities as $city) {
            $candidateList = new CandidateList();
            $candidateList->setFirstname('Prénom' . uniqid());
            $candidateList->setLastname('Nom' . uniqid());
            $candidateList->setNameList('Liste Test ' . uniqid());
            $candidateList->setCity($city);
            $this->entityManager->persist($candidateList);
        }

        // Créer une catégorie
        $category = new Category();
        $category->setName('Catégorie Test ' . uniqid());
        $category->setBareme(100);
        $this->entityManager->persist($category);

        // Créer des propositions
        for ($i = 1; $i <= 5; $i++) {
            $proposition = new Proposition();
            $proposition->setTitle('Proposition Test ' . $i . ' ' . uniqid());
            $proposition->setBareme(20);
            $proposition->setCategory($category);
            $this->entityManager->persist($proposition);
        }

        $this->entityManager->flush();
    }

    private function createExistingCommitments(): void
    {
        $candidateLists = $this->entityManager->getRepository(CandidateList::class)->findAll();
        $propositions = $this->entityManager->getRepository(Proposition::class)->findAll();

        // Créer quelques engagements existants
        foreach ($candidateLists as $candidateList) {
            $commitment = new Commitment();
            $commitment->setCandidateList($candidateList);
            $commitment->setProposition($propositions[0]);
            $commitment->setStatus(CommitmentStatus::ACCEPTED);
            $commitment->setCommentCandidateList('Engagement existant');
            $this->entityManager->persist($commitment);
        }

        $this->entityManager->flush();
    }

    private function cleanDatabase(): void
    {
        // Supprimer les engagements
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Commitment::class, 'c')->getQuery()->execute();

        // Supprimer les propositions
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Proposition::class, 'p')->getQuery()->execute();

        // Supprimer les catégories
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Category::class, 'cat')->getQuery()->execute();

        // Supprimer les listes candidates
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(CandidateList::class, 'cl')->getQuery()->execute();

        // Supprimer les villes
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(City::class, 'c')->getQuery()->execute();
    }
}

