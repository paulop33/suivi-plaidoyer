<?php

require_once 'vendor/autoload.php';

use App\Kernel;
use App\Entity\CandidateList;

// Créer le kernel
$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

try {
    $entityManager = $container->get('doctrine.orm.entity_manager');
    
    // Récupérer la liste candidate avec l'ID 108
    $candidateList = $entityManager->getRepository(CandidateList::class)->find(108);
    
    if ($candidateList) {
        echo "✅ Liste candidate trouvée : " . $candidateList->getNameList() . "\n";
        echo "ID : " . $candidateList->getId() . "\n";
        echo "URL attendue : /admin/candidate-list/" . $candidateList->getId() . "/batch-commitment\n";
        
        // Vérifier si la liste a des engagements
        $commitments = $candidateList->getCommitments();
        echo "Nombre d'engagements existants : " . $commitments->count() . "\n";
        
    } else {
        echo "❌ Liste candidate avec l'ID 108 non trouvée.\n";
        
        // Lister les premières listes disponibles
        $lists = $entityManager->getRepository(CandidateList::class)->findBy([], [], 5);
        echo "Listes disponibles :\n";
        foreach ($lists as $list) {
            echo "- ID " . $list->getId() . " : " . $list->getNameList() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

echo "\nTest terminé.\n";
