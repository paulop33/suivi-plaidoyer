<?php

namespace App\Command;

use App\Entity\CandidateList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:remove-all-candidate-lists',
    description: 'Supprime toutes les listes candidates',
)]
class DeleteAllCandidateListsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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

        $this->clearCandidateLists();

        return Command::SUCCESS;
    }

    private function clearCandidateLists(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(CandidateList::class, 'c');
        $qb->getQuery()->execute();
    }
}

