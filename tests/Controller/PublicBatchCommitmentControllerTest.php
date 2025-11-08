<?php

namespace App\Tests\Controller;

use App\Entity\CandidateList;
use App\Entity\City;
use App\Entity\Proposition;
use App\Entity\Category;
use App\Entity\Commitment;
use App\Enum\CommitmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;

class PublicBatchCommitmentControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UriSigner $uriSigner;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->uriSigner = static::getContainer()->get(UriSigner::class);
    }

    public function testAccessWithoutSignatureIsDenied(): void
    {
        $candidateList = $this->createTestCandidateList();

        $this->client->request('GET', '/public/batch-commitment/' . $candidateList->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSelectorTextContains('body', 'URL non signée ou signature invalide');
    }

    public function testAccessWithInvalidSignatureIsDenied(): void
    {
        $candidateList = $this->createTestCandidateList();

        $this->client->request('GET', '/public/batch-commitment/' . $candidateList->getId() . '?_hash=invalid_signature');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSelectorTextContains('body', 'URL non signée ou signature invalide');
    }

    public function testAccessWithValidSignatureIsAllowed(): void
    {
        $candidateList = $this->createTestCandidateList();
        $url = 'http://localhost/public/batch-commitment/' . $candidateList->getId();
        $signedUrl = $this->uriSigner->sign($url);



        $this->client->request('GET', $signedUrl);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Gérer les engagements de la liste');
        $this->assertSelectorTextContains('.alert-info', $candidateList->getNameList());
    }

    public function testNonExistentCandidateListReturns404(): void
    {
        $url = 'http://localhost/public/batch-commitment/99999';
        $signedUrl = $this->uriSigner->sign($url);

        $this->client->request('GET', $signedUrl);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testFormSubmissionWithValidSignature(): void
    {
        $candidateList = $this->createTestCandidateList();
        $proposition = $this->createTestProposition();
        $url = 'http://localhost/public/batch-commitment/' . $candidateList->getId();
        $signedUrl = $this->uriSigner->sign($url);

        $this->client->request('POST', $signedUrl, [
            'global_comment' => 'Test comment',
            'proposition_status' => [
                $proposition->getId() => 'accepted'
            ],
            'proposition_comments' => [
                $proposition->getId() => 'Test proposition comment'
            ]
        ]);

        // La redirection devrait être vers une nouvelle URL signée
        $this->assertResponseRedirects();

        // Vérifier que l'engagement a été créé
        $commitment = $this->entityManager->getRepository(Commitment::class)
            ->findOneBy(['candidateList' => $candidateList, 'proposition' => $proposition]);

        $this->assertNotNull($commitment);
        $this->assertEquals(CommitmentStatus::ACCEPTED, $commitment->getStatus());
        $this->assertEquals('Test proposition comment', $commitment->getCommentCandidateList());
    }

    public function testFormSubmissionWithoutSignatureIsDenied(): void
    {
        $candidateList = $this->createTestCandidateList();

        $this->client->request('POST', '/public/batch-commitment/' . $candidateList->getId(), [
            'global_comment' => 'Test comment'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testValidationErrorRedirectsToSignedUrl(): void
    {
        $candidateList = $this->createTestCandidateList();
        $url = 'http://localhost/public/batch-commitment/' . $candidateList->getId();
        $signedUrl = $this->uriSigner->sign($url);

        // Soumettre un formulaire avec un commentaire trop long pour déclencher une erreur de validation
        $longComment = str_repeat('a', 1001); // 1001 caractères, dépasse la limite de 1000

        $this->client->request('POST', $signedUrl, [
            'global_comment' => $longComment
        ]);

        // Vérifier que la réponse est une redirection
        $this->assertResponseRedirects();

        // Vérifier que l'URL de redirection est signée et valide
        $redirectUrl = $this->client->getResponse()->headers->get('Location');
        $this->assertTrue($this->uriSigner->check($redirectUrl), 'L\'URL de redirection doit être signée');

        // Suivre la redirection et vérifier que le message d'erreur est affiché
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert-danger, .flash-error');
    }

    private function createTestCandidateList(): CandidateList
    {
        $city = new City();
        $city->setName('Test City ' . uniqid());
        $this->entityManager->persist($city);

        $uniqueId = uniqid();
        $candidateList = new CandidateList();
        $candidateList->setNameList('Test List ' . $uniqueId);
        $candidateList->setFirstname('John');
        $candidateList->setLastname('Doe ' . $uniqueId);
        $candidateList->setEmail('test@example.com');
        $candidateList->setPhone('0123456789');
        $candidateList->setCity($city);

        $this->entityManager->persist($candidateList);
        $this->entityManager->flush();

        return $candidateList;
    }

    private function createTestProposition(): Proposition
    {
        $category = new Category();
        $category->setName('Test Category ' . uniqid());
        $category->setDescription('Test description');
        $this->entityManager->persist($category);

        $proposition = new Proposition();
        $proposition->setTitle('Test Proposition');
        $proposition->setDescription('Test proposition description');
        $proposition->setBareme(10);
        $proposition->setPosition(1);
        $proposition->setCategory($category);

        $this->entityManager->persist($proposition);
        $this->entityManager->flush();

        return $proposition;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyer la base de données de test
        if ($this->entityManager) {
            $this->entityManager->close();
        }
    }
}
