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

    public function testControllerExists(): void
    {
        // Test simple pour vérifier que la classe existe et est bien configurée
        $this->assertTrue(class_exists('App\\Controller\\Admin\\CandidateListCrudController'));

        $reflection = new \ReflectionClass('App\\Controller\\Admin\\CandidateListCrudController');
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
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
