<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\PropositionRepository;
use App\Repository\CandidateListRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Génère le fichier sitemap.xml avec toutes les URLs du site',
)]
class GenerateSitemapCommand extends Command
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CityRepository $cityRepository,
        private PropositionRepository $propositionRepository,
        private CandidateListRepository $candidateListRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Génération du sitemap.xml');

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Créer l'élément racine urlset
        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlset->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $xml->appendChild($urlset);

        $today = date('Y-m-d');

        // Page d'accueil
        $this->addUrl($xml, $urlset, $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL), $today, 'daily', '1.0');
        $io->writeln('✓ Page d\'accueil ajoutée');

        // Index des communes
        $this->addUrl($xml, $urlset, $this->urlGenerator->generate('app_cities_index', [], UrlGeneratorInterface::ABSOLUTE_URL), $today, 'weekly', '0.9');
        $io->writeln('✓ Index des communes ajouté');

        // Index des propositions
        $this->addUrl($xml, $urlset, $this->urlGenerator->generate('app_propositions_index', [], UrlGeneratorInterface::ABSOLUTE_URL), $today, 'weekly', '0.9');
        $io->writeln('✓ Index des propositions ajouté');


        // Toutes les communes
        $cities = $this->cityRepository->findAll();
        foreach ($cities as $city) {
            $url = $this->urlGenerator->generate('app_city_show', ['slug' => $city->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addUrl($xml, $urlset, $url, $today, 'daily', '0.8');
        }
        $io->writeln(sprintf('✓ %d communes ajoutées', count($cities)));

        // Toutes les catégories
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as $category) {
            $url = $this->urlGenerator->generate('app_category_show', ['id' => $category->getId(), 'slug' => $category->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addUrl($xml, $urlset, $url, $today, 'daily', '0.8');
        }
        $io->writeln(sprintf('✓ %d catégories ajoutées', count($categories)));

        // Toutes les propositions
        $propositions = $this->propositionRepository->findAll();
        foreach ($propositions as $proposition) {
            $url = $this->urlGenerator->generate('app_proposition_show', ['id' => $proposition->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addUrl($xml, $urlset, $url, $today, 'daily', '0.9');
        }
        $io->writeln(sprintf('✓ %d propositions ajoutées', count($propositions)));

        // Toutes les listes de candidats
        $candidateLists = $this->candidateListRepository->findAll();
        foreach ($candidateLists as $list) {
            $url = $this->urlGenerator->generate('app_candidate_list_show', ['id' => $list->getId(), 'slug' => $list->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addUrl($xml, $urlset, $url, $today, 'weekly', '0.6');
        }
        $io->writeln(sprintf('✓ %d listes de candidats ajoutées', count($candidateLists)));

        // Sauvegarder le fichier
        $sitemapPath = $this->projectDir . '/public/sitemap.xml';
        $xml->save($sitemapPath);

        $io->success('Sitemap généré avec succès : ' . $sitemapPath);

        return Command::SUCCESS;
    }

    private function addUrl(\DOMDocument $xml, \DOMElement $urlset, string $loc, string $lastmod, string $changefreq, string $priority): void
    {
        $url = $xml->createElement('url');

        $locElement = $xml->createElement('loc', htmlspecialchars($loc));
        $url->appendChild($locElement);

        $lastmodElement = $xml->createElement('lastmod', $lastmod);
        $url->appendChild($lastmodElement);

        $changefreqElement = $xml->createElement('changefreq', $changefreq);
        $url->appendChild($changefreqElement);

        $priorityElement = $xml->createElement('priority', $priority);
        $url->appendChild($priorityElement);

        $urlset->appendChild($url);
    }
}

