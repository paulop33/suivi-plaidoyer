<?php

namespace App\Tests\Service;

use App\Entity\CandidateList;
use App\Entity\City;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
use App\Service\CommitmentDataService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CommitmentDataServiceTest extends TestCase
{
    private CommitmentDataService $commitmentDataService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->commitmentDataService = new CommitmentDataService($this->entityManager);
    }

    public function testCountAcceptedCommitmentsWithAllAccepted(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(true)],
            ['commitment' => $this->createCommitmentWithAccepted(true)],
            ['commitment' => $this->createCommitmentWithAccepted(true)],
        ];

        // Act
        $result = $this->commitmentDataService->countAcceptedCommitments($commitmentData);

        // Assert
        $this->assertEquals(3, $result);
    }

    public function testCountAcceptedCommitmentsWithMixedStatuses(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(true)],
            ['commitment' => $this->createCommitmentWithAccepted(false)],
            ['commitment' => $this->createCommitmentWithAccepted(true)],
            ['commitment' => $this->createCommitmentWithAccepted(false)],
        ];

        // Act
        $result = $this->commitmentDataService->countAcceptedCommitments($commitmentData);

        // Assert
        $this->assertEquals(2, $result);
    }

    public function testCountAcceptedCommitmentsWithNoAccepted(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(false)],
            ['commitment' => $this->createCommitmentWithAccepted(false)],
        ];

        // Act
        $result = $this->commitmentDataService->countAcceptedCommitments($commitmentData);

        // Assert
        $this->assertEquals(0, $result);
    }

    public function testCountAcceptedCommitmentsWithEmptyArray(): void
    {
        // Arrange
        $commitmentData = [];

        // Act
        $result = $this->commitmentDataService->countAcceptedCommitments($commitmentData);

        // Assert
        $this->assertEquals(0, $result);
    }

    public function testCountAcceptedCommitmentsWithNonAccepted(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(false)],
            ['commitment' => $this->createCommitmentWithAccepted(true)],
        ];

        // Act
        $result = $this->commitmentDataService->countAcceptedCommitments($commitmentData);

        // Assert
        $this->assertEquals(1, $result);
    }

    public function testFilterAcceptedCommitmentsWithMixedStatuses(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(true), 'candidateList' => 'List1'],
            ['commitment' => $this->createCommitmentWithAccepted(false), 'candidateList' => 'List2'],
            ['commitment' => $this->createCommitmentWithAccepted(true), 'candidateList' => 'List3'],
        ];

        // Act
        $result = $this->commitmentDataService->filterAcceptedCommitments($commitmentData);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('List1', $result[0]['candidateList']);
        $this->assertEquals('List3', $result[2]['candidateList']);
    }

    public function testFilterAcceptedCommitmentsWithNoAccepted(): void
    {
        // Arrange
        $commitmentData = [
            ['commitment' => $this->createCommitmentWithAccepted(false)],
            ['commitment' => $this->createCommitmentWithAccepted(false)],
        ];

        // Act
        $result = $this->commitmentDataService->filterAcceptedCommitments($commitmentData);

        // Assert
        $this->assertCount(0, $result);
    }

    private function createCommitmentWithAccepted(bool $isAccepted): Commitment
    {
        $commitment = $this->createMock(Commitment::class);
        $commitment->method('isAccepted')->willReturn($isAccepted);
        return $commitment;
    }
}
