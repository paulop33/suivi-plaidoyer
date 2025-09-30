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
    name: 'app:fill-random-commitments',
    description: 'Remplit aléatoirement les engagements des listes candidates (accepté ou refusé)',
)]
class FillRandomCommitmentsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandidateListRepository $candidateListRepository,
        private PropositionRepository $propositionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Supprimer tous les engagements existants avant de remplir')
            ->addOption('percentage', 'p', InputOption::VALUE_REQUIRED, 'Pourcentage de propositions à engager par liste (0-100)', 100)
            ->addOption('acceptance-rate', 'a', InputOption::VALUE_REQUIRED, 'Pourcentage d\'acceptation (0-100)', 60)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simuler sans persister les données')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'exécution sans confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clear = $input->getOption('clear');
        $percentage = (int) $input->getOption('percentage');
        $acceptanceRate = (int) $input->getOption('acceptance-rate');
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');

        // Validation des paramètres
        if ($percentage < 0 || $percentage > 100) {
            $io->error('Le pourcentage doit être entre 0 et 100');
            return Command::FAILURE;
        }

        if ($acceptanceRate < 0 || $acceptanceRate > 100) {
            $io->error('Le taux d\'acceptation doit être entre 0 et 100');
            return Command::FAILURE;
        }

        $io->title('Remplissage aléatoire des engagements');

        if ($dryRun) {
            $io->note('MODE DRY-RUN - Aucune donnée ne sera persistée');
        }

        // Récupérer les données
        $candidateLists = $this->candidateListRepository->findAll();
        $propositions = $this->propositionRepository->findAll();

        if (empty($candidateLists)) {
            $io->error('Aucune liste candidate trouvée dans la base de données');
            return Command::FAILURE;
        }

        if (empty($propositions)) {
            $io->error('Aucune proposition trouvée dans la base de données');
            return Command::FAILURE;
        }

        $io->info(sprintf('Listes candidates trouvées : %d', count($candidateLists)));
        $io->info(sprintf('Propositions trouvées : %d', count($propositions)));
        $io->info(sprintf('Pourcentage d\'engagement : %d%%', $percentage));
        $io->info(sprintf('Taux d\'acceptation : %d%%', $acceptanceRate));

        // Confirmation
        if (!$force && !$dryRun) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf(
                    'Voulez-vous vraiment %s et créer des engagements aléatoires ? (y/N) ',
                    $clear ? 'SUPPRIMER tous les engagements existants' : 'continuer'
                ),
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $io->warning('Opération annulée');
                return Command::SUCCESS;
            }
        }

        // Supprimer les engagements existants si demandé
        if ($clear && !$dryRun) {
            $io->section('Suppression des engagements existants');
            $deleted = $this->clearExistingCommitments();
            $io->success(sprintf('%d engagements supprimés', $deleted));
        }

        // Remplir les engagements
        $io->section('Création des engagements aléatoires');

        $stats = [
            'created' => 0,
            'skipped' => 0,
            'accepted' => 0,
            'refused' => 0,
        ];

        $io->progressStart(count($candidateLists));

        foreach ($candidateLists as $candidateList) {
            $result = $this->fillCommitmentsForList(
                $candidateList,
                $propositions,
                $percentage,
                $acceptanceRate,
                $dryRun
            );

            $stats['created'] += $result['created'];
            $stats['skipped'] += $result['skipped'];
            $stats['accepted'] += $result['accepted'];
            $stats['refused'] += $result['refused'];

            $io->progressAdvance();
        }

        $io->progressFinish();

        // Flush si pas en dry-run
        if (!$dryRun) {
            $this->entityManager->flush();
        }

        // Afficher les statistiques
        $io->success('Remplissage terminé !');

        $io->table(
            ['Statistique', 'Valeur'],
            [
                ['Engagements créés', $stats['created']],
                ['Engagements ignorés (existants)', $stats['skipped']],
                ['Acceptés', sprintf('%d (%.1f%%)', $stats['accepted'], $stats['created'] > 0 ? ($stats['accepted'] / $stats['created'] * 100) : 0)],
                ['Refusés', sprintf('%d (%.1f%%)', $stats['refused'], $stats['created'] > 0 ? ($stats['refused'] / $stats['created'] * 100) : 0)],
            ]
        );

        return Command::SUCCESS;
    }

    private function fillCommitmentsForList(
        $candidateList,
        array $propositions,
        int $percentage,
        int $acceptanceRate,
        bool $dryRun
    ): array {
        $stats = [
            'created' => 0,
            'skipped' => 0,
            'accepted' => 0,
            'refused' => 0,
        ];

        // Récupérer les engagements existants pour cette liste
        $existingCommitments = [];
        foreach ($candidateList->getCommitments() as $commitment) {
            $existingCommitments[$commitment->getProposition()->getId()] = true;
        }

        // Mélanger les propositions pour un ordre aléatoire
        $shuffledPropositions = $propositions;
        shuffle($shuffledPropositions);

        // Calculer le nombre de propositions à engager
        $totalToEngage = (int) ceil(count($propositions) * $percentage / 100);

        $engaged = 0;
        foreach ($shuffledPropositions as $proposition) {
            if ($engaged >= $totalToEngage) {
                break;
            }

            // Vérifier si un engagement existe déjà
            if (isset($existingCommitments[$proposition->getId()])) {
                $stats['skipped']++;
                continue;
            }

            // Déterminer aléatoirement le statut (accepté ou refusé)
            $isAccepted = (rand(1, 100) <= $acceptanceRate);
            $status = $isAccepted ? CommitmentStatus::ACCEPTED : CommitmentStatus::REFUSED;

            // Créer l'engagement
            $commitment = new Commitment();
            $commitment->setCandidateList($candidateList);
            $commitment->setProposition($proposition);
            $commitment->setStatus($status);

            // Ajouter un commentaire aléatoire
            $comment = $this->generateRandomComment($status);
            $commitment->setCommentCandidateList($comment);

            if (!$dryRun) {
                $this->entityManager->persist($commitment);
            }

            $stats['created']++;
            if ($isAccepted) {
                $stats['accepted']++;
            } else {
                $stats['refused']++;
            }

            $engaged++;
        }

        return $stats;
    }

    private function generateRandomComment(CommitmentStatus $status): string
    {
        $acceptedComments = [
            'Nous soutenons pleinement cette proposition.',
            'Cette mesure correspond à nos priorités.',
            'Nous nous engageons à mettre en œuvre cette proposition.',
            'Proposition en accord avec notre programme.',
            'Nous approuvons cette initiative.',
            'Cette proposition répond aux besoins de nos citoyens.',
            'Nous sommes favorables à cette mesure.',
        ];

        $refusedComments = [
            'Cette proposition ne correspond pas à nos priorités actuelles.',
            'Nous ne pouvons pas nous engager sur cette mesure pour des raisons budgétaires.',
            'Cette proposition nécessite une étude plus approfondie.',
            'Nous avons des réserves sur la faisabilité de cette mesure.',
            'Cette proposition n\'est pas compatible avec notre programme.',
            'Nous préférons explorer d\'autres alternatives.',
            'Les contraintes techniques ne nous permettent pas d\'accepter cette proposition.',
        ];

        $comments = $status === CommitmentStatus::ACCEPTED ? $acceptedComments : $refusedComments;
        return $comments[array_rand($comments)];
    }

    private function clearExistingCommitments(): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Commitment::class, 'c');
        return $qb->getQuery()->execute();
    }
}

