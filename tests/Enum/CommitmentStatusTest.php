<?php

namespace App\Tests\Enum;

use App\Enum\CommitmentStatus;
use PHPUnit\Framework\TestCase;

class CommitmentStatusTest extends TestCase
{
    public function testCommitmentStatusValues(): void
    {
        $this->assertEquals('accepted', CommitmentStatus::ACCEPTED->value);
        $this->assertEquals('refused', CommitmentStatus::REFUSED->value);
    }

    public function testCommitmentStatusLabels(): void
    {
        $this->assertEquals('Accepté', CommitmentStatus::ACCEPTED->getLabel());
        $this->assertEquals('Refusé', CommitmentStatus::REFUSED->getLabel());
    }

    public function testCommitmentStatusColors(): void
    {
        $this->assertEquals('success', CommitmentStatus::ACCEPTED->getColor());
        $this->assertEquals('danger', CommitmentStatus::REFUSED->getColor());
    }

    public function testCommitmentStatusChoices(): void
    {
        $expectedChoices = [
            'Accepté' => 'accepted',
            'Refusé' => 'refused',
        ];

        $this->assertEquals($expectedChoices, CommitmentStatus::getChoices());
    }

    public function testCommitmentStatusFromValue(): void
    {
        $this->assertEquals(CommitmentStatus::ACCEPTED, CommitmentStatus::fromValue('accepted'));
        $this->assertEquals(CommitmentStatus::REFUSED, CommitmentStatus::fromValue('refused'));
        $this->assertNull(CommitmentStatus::fromValue('invalid'));
        $this->assertNull(CommitmentStatus::fromValue(''));
    }

    public function testCommitmentStatusFromStandardMethod(): void
    {
        $this->assertEquals(CommitmentStatus::ACCEPTED, CommitmentStatus::from('accepted'));
        $this->assertEquals(CommitmentStatus::REFUSED, CommitmentStatus::from('refused'));

        $this->expectException(\ValueError::class);
        CommitmentStatus::from('invalid');
    }


}
