<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:generate-signed-url',
    description: 'Génère une URL signée pour l\'accès public au batch commitment',
)]
class GenerateSignedUrlCommand extends Command
{
    public function __construct(
        private UriSigner $uriSigner,
        private UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('candidateListId', InputArgument::REQUIRED, 'ID de la liste candidate')
            ->addOption('expiration', null, InputOption::VALUE_OPTIONAL, 'Durée d\'expiration en secondes (optionnel)')
            ->addOption('base-url', 'b', InputOption::VALUE_OPTIONAL, 'URL de base (par défaut: http://localhost:8000)', 'http://localhost:8000')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $candidateListId = $input->getArgument('candidateListId');
        $expiration = $input->getOption('expiration');
        $baseUrl = $input->getOption('base-url');

        // Générer l'URL de base
        $url = $this->urlGenerator->generate(
            'public_batch_commitment',
            ['candidateListId' => $candidateListId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Remplacer l'URL de base si spécifiée
        if ($baseUrl !== 'http://localhost:8000') {
            $parsedUrl = parse_url($url);
            $parsedBaseUrl = parse_url($baseUrl);

            $url = sprintf(
                '%s://%s%s%s%s',
                $parsedBaseUrl['scheme'] ?? 'http',
                $parsedBaseUrl['host'] ?? 'localhost',
                isset($parsedBaseUrl['port']) ? ':' . $parsedBaseUrl['port'] : '',
                $parsedUrl['path'] ?? '',
                isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : ''
            );
        }

        // Signer l'URL
        if ($expiration) {
            $expirationTime = time() + (int)$expiration;
            $signedUrl = $this->uriSigner->sign($url, $expirationTime);
        } else {
            $signedUrl = $this->uriSigner->sign($url);
        }

        $io->success('URL signée générée avec succès !');
        $io->section('Informations');
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['ID Liste Candidate', $candidateListId],
                ['URL de base', $url],
                ['Expiration', $expiration ? date('Y-m-d H:i:s', $expirationTime) . ' (' . $expiration . 's)' : 'Aucune'],
                ['URL signée', $signedUrl],
            ]
        );

        $io->note([
            'Copiez cette URL signée pour accéder à la page de batch commitment.',
            'Cette URL est sécurisée et ne peut pas être modifiée sans invalider la signature.',
            $expiration ? 'Cette URL expirera le ' . date('Y-m-d H:i:s', $expirationTime) : 'Cette URL n\'a pas d\'expiration.'
        ]);

        return Command::SUCCESS;
    }
}
