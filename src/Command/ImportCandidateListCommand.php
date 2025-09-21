<?php

namespace App\Command;

use App\Entity\CandidateList;
use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-candidate-list',
    description: 'Import candidate list from CSV file in data/ directory',
)]
class ImportCandidateListCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CityRepository $cityRepository,
        private ContactRepository $candidateListRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::OPTIONAL, 'CSV filename in data/ directory', 'candidateList.csv')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run without persisting data')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear existing candidate list data before import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');
        $dryRun = $input->getOption('dry-run');
        $clear = $input->getOption('clear');

        $filePath = sprintf('%s/data/%s', $this->getProjectDir(), $filename);

        if (!file_exists($filePath)) {
            $io->error(sprintf('File not found: %s', $filePath));
            return Command::FAILURE;
        }

        $io->title('Importing Candidate List from CSV');
        $io->info(sprintf('File: %s', $filePath));

        if ($dryRun) {
            $io->note('DRY RUN MODE - No data will be persisted');
        }

        if ($clear && !$dryRun) {
            $io->warning('Clearing existing candidate list data...');
            $this->clearExistingData();
            $io->success('Existing data cleared');
        }

        $csvData = $this->readCsvFile($filePath);
        
        if (empty($csvData)) {
            $io->error('No data found in CSV file');
            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d rows to process', count($csvData)));

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        $io->progressStart(count($csvData));

        foreach ($csvData as $rowIndex => $row) {
            try {
                $result = $this->processRow($row, $dryRun);
                
                if ($result === 'imported') {
                    $imported++;
                } elseif ($result === 'skipped') {
                    $skipped++;
                }
                
                $io->progressAdvance();
            } catch (\Exception $e) {
                $errors++;
                $io->error(sprintf('Error processing row %d: %s', $rowIndex + 2, $e->getMessage()));
                $io->progressAdvance();
            }
        }

        $io->progressFinish();

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            'Import completed! Imported: %d, Skipped: %d, Errors: %d',
            $imported,
            $skipped,
            $errors
        ));

        return Command::SUCCESS;
    }

    private function readCsvFile(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException('Cannot open CSV file');
        }

        // Read header
        $header = fgetcsv($handle);
        
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('Cannot read CSV header');
        }

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    private function processRow(array $row, bool $dryRun): string
    {
        // Extract data from CSV row
        $cityName = trim($row['Libellé commune'] ?? '');
        $firstname = trim($row['Prénom candidat'] ?? '');
        $lastname = trim($row['Nom candidat'] ?? '');
        $listName = trim($row['Libellé Etendu Liste'] ?? '');

        if (empty($cityName) || empty($firstname) || empty($lastname) || empty($listName)) {
            return 'skipped';
        }

        // Find or create city
        $city = $this->findOrCreateCity($cityName, $dryRun);
        
        if (!$city) {
            throw new \RuntimeException(sprintf('Cannot create city: %s', $cityName));
        }

        // Check if candidate already exists
        $existingCandidate = $this->candidateListRepository->findOneBy([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'nameList' => $listName,
            'city' => $city
        ]);

        if ($existingCandidate) {
            return 'skipped';
        }

        // Create new candidate
        $candidate = new CandidateList();
        $candidate->setFirstname($firstname);
        $candidate->setLastname($lastname);
        $candidate->setNameList($listName);
        $candidate->setCity($city);

        // Set optional fields if available
        if (!empty($row['Email candidat'] ?? '')) {
            $candidate->setEmail(trim($row['Email candidat']));
        }
        
        if (!empty($row['Téléphone candidat'] ?? '')) {
            $candidate->setPhone(trim($row['Téléphone candidat']));
        }

        if (!$dryRun) {
            $this->entityManager->persist($candidate);
        }

        return 'imported';
    }

    private function findOrCreateCity(string $cityName, bool $dryRun): ?City
    {
        $city = $this->cityRepository->findOneBy(['name' => $cityName]);
        
        if (!$city) {
            $city = new City();
            $city->setName($cityName);
            
            if (!$dryRun) {
                $this->entityManager->persist($city);
            }
        }

        return $city;
    }

    private function clearExistingData(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(CandidateList::class, 'cl');
        $qb->getQuery()->execute();
    }

    private function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }
}
