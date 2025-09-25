<?php

namespace App\Tests\Controller\Admin;

use App\Entity\CandidateList;
use App\Entity\City;
use App\Entity\Category;
use App\Entity\Proposition;
use App\Entity\Commitment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CandidateListCrudControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        // Ne pas booter le kernel ici, le faire dans chaque test
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if (!$this->entityManager) {
            $kernel = self::bootKernel();
            $this->entityManager = $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        }
        return $this->entityManager;
    }

    public function testBatchCommitmentPageLoads(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();

        // Créer des données de test
        $city = new City();
        $city->setName('Test City');
        $city->setSlug('test-city');
        $entityManager->persist($city);

        $candidateList = new CandidateList();
        $candidateList->setFirstname('John');
        $candidateList->setLastname('Doe');
        $candidateList->setNameList('Test List');
        $candidateList->setCity($city);
        $entityManager->persist($candidateList);

        $category = new Category();
        $category->setName('Test Category');
        $category->setSlug('test-category');
        $entityManager->persist($category);

        $proposition = new Proposition();
        $proposition->setTitle('Test Proposition');
        $proposition->setCategory($category);
        $entityManager->persist($proposition);

        $entityManager->flush();

        // Tester l'accès à la page de batch commitment
        $crawler = $client->request('GET', '/admin', [
            'crudControllerFqcn' => 'App\\Controller\\Admin\\CandidateListCrudController',
            'crudAction' => 'batchCommitment',
            'entityId' => $candidateList->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Engager la liste sur toutes les propositions');
        $this->assertSelectorExists('form#batchCommitmentForm');
        $this->assertSelectorExists('textarea#global_comment');
        $this->assertSelectorExists('input[name="propositions[]"]');
    }

    public function testBatchCommitmentControllerExists(): void
    {
        // Test simple pour vérifier que la classe existe et est bien configurée
        $this->assertTrue(class_exists('App\\Controller\\Admin\\CandidateListCrudController'));

        $reflection = new \ReflectionClass('App\\Controller\\Admin\\CandidateListCrudController');
        $this->assertTrue($reflection->hasMethod('batchCommitment'));
        $this->assertTrue($reflection->hasMethod('processBatchCommitment'));
    }

    public function testBatchCommitmentTemplateExists(): void
    {
        // Vérifier que le template existe
        $templatePath = __DIR__ . '/../../../templates/admin/batch_commitment.html.twig';
        $this->assertFileExists($templatePath);

        $content = file_get_contents($templatePath);
        $this->assertStringContainsString('batchCommitmentForm', $content);
        $this->assertStringContainsString('global_comment', $content);
        $this->assertStringContainsString('propositions[]', $content);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyer la base de données de test
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
