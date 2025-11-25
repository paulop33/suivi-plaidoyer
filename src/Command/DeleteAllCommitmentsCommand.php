<?php

namespace App\Command;

use App\Entity\Commitment;
use App\Enum\CommitmentStatus;
use App\Repository\CandidateListRepository;
use App\Repository\PropositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'app:remove-all-commitments',
    description: 'Supprime tous les engagements des listes candidates',
)]
class DeleteAllCommitmentsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'exécution sans confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        // Confirmation
        if (!$force) {

            $io->warning('Utiliser l\'option force pour confirmer la suppression');
            return Command::SUCCESS;
        }

        $this->clearExistingCommitments();

        return Command::SUCCESS;
    }

    private function clearExistingCommitments(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Commitment::class, 'c');
        $qb->getQuery()->execute();
    }
}

