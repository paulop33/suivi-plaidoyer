<?php

namespace App\Tests\Entity;

use App\Entity\Commitment;
use App\Entity\CandidateList;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
use PHPUnit\Framework\TestCase;

class CommitmentTest extends TestCase
{


    public function testCommitmentStatusAccepted(): void
    {
        $commitment = new Commitment();
        $commitment->setStatus(CommitmentStatus::ACCEPTED);

        $this->assertEquals(CommitmentStatus::ACCEPTED, $commitment->getStatus());
        $this->assertTrue($commitment->isAccepted());
        $this->assertFalse($commitment->isRefused());
    }

    public function testCommitmentStatusRefused(): void
    {
        $commitment = new Commitment();
        $commitment->setStatus(CommitmentStatus::REFUSED);

        $this->assertEquals(CommitmentStatus::REFUSED, $commitment->getStatus());
        $this->assertFalse($commitment->isAccepted());
        $this->assertTrue($commitment->isRefused());
    }

    public function testCommitmentRequiresStatus(): void
    {
        $commitment = new Commitment();

        // Le statut doit être défini obligatoirement
        $commitment->setStatus(CommitmentStatus::ACCEPTED);
        $this->assertEquals(CommitmentStatus::ACCEPTED, $commitment->getStatus());
        $this->assertTrue($commitment->isAccepted());

        $commitment->setStatus(CommitmentStatus::REFUSED);
        $this->assertEquals(CommitmentStatus::REFUSED, $commitment->getStatus());
        $this->assertTrue($commitment->isRefused());
    }

    public function testCommitmentStatusFluentInterface(): void
    {
        $commitment = new Commitment();
        $result = $commitment->setStatus(CommitmentStatus::ACCEPTED);

        $this->assertSame($commitment, $result);
        $this->assertEquals(CommitmentStatus::ACCEPTED, $commitment->getStatus());
    }
}
