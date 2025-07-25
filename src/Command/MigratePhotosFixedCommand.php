<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FormRepository;
use App\Service\ImageStorageService;

#[AsCommand(
    name: 'app:migrate-photos-fixed',
    description: 'Migration des photos - VERSION CORRIGÉE avec debug'
)]
class MigratePhotosFixedCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormRepository $formRepository,
        private ImageStorageService $imageStorageService,
        private HttpClientInterface $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('agency', InputArgument::REQUIRED, 'Code agence (S140, S50, etc.)')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Taille des lots', 10)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Simulation sans modification')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer le re-téléchargement des photos existantes')
            ->addOption('single', 's', InputOption::VALUE_OPTIONAL, 'Traiter un seul équipement')
            ->setHelp('
Version corrigée de la migration des photos avec debug intégré.

Exemples:
  php bin/console app:migrate-photos-fixed S140 --dry-run
  php bin/console app:migrate-photos-fixed S140 --batch-size=5
  php bin/console app:migrate-photos-fixed S140 --single=RAP01
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agency = $input->getArgument('agency');
        $batchSize = (int) $input->getOption('batch-size');
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');
        $single = $input->getOption('single');

        $io->title("🔧 Migration corrigée des photos pour l'agence {$agency}");

        if ($dryRun) {
            $io->note('Mode simulation activé');
        }

        if ($single) {
            return $this->processSingleEquipment($single, $agency, $dryRun, $force, $io);
        }

        try {
            // Récupérer les équipements
            $entityClass = "App\\Entity\\Equipement{$agency}";
            $repository = $this->entityManager->getRepository($entityClass);
            
            $totalEquipments = $repository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $io->writeln("📊 Total équipements: {$totalEquipments}");

            $results = [
                'processed' => 0,
                'migrated' => 0,
                'skipped' => 0,
                'errors' => 0,
                'photos_downloaded' => 0,
                'details' => []
            ];

            // Traiter par lots
            $offset = 0;

            while ($offset < $totalEquipments && $offset < $batchSize) {
                $equipments = $repository->createQueryBuilder('e')
                    ->setFirstResult($offset)
                    ->setMaxResults(min($batchSize, $totalEquipments - $offset))
                    ->getQuery()
                    ->getResult();

                foreach ($equipments as $equipment) {
                    $equipmentResult = $this->processEquipmentDetailed($equipment, $agency, $dryRun, $force, $io);
                    
                    $results['processed']++;
                    $results[$equipmentResult['status']]++;
                    $results['photos_downloaded'] += $equipmentResult['photos_downloaded'];
                    $results['details'][] = $equipmentResult;

                    // Log détaillé pour chaque équipement
                    $status = $equipmentResult['status'] === 'migrated' ? '✅' : 
                            ($equipmentResult['status'] === 'skipped' ? '⏭️' : '❌');
                    
                    $io->writeln("{$status} {$equipment->getNumeroEquipement()}: {$equipmentResult['message']} ({$equipmentResult['photos_downloaded']} photos)");
                }

                $offset += count($equipments);
            }

            // Afficher les résultats détaillés
            $this->displayDetailedResults($io, $results, $dryRun);

            return $results['migrated'] > 0 ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $io->error("❌ Erreur: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function processSingleEquipment(string $equipmentId, string $agency, bool $dryRun, bool $force, SymfonyStyle $io): int
    {
        $io->section("🎯 Migration de l'équipement {$equipmentId}");

        try {
            $entityClass = "App\\Entity\\Equipement{$agency}";
            $repository = $this->entityManager->getRepository($entityClass);
            $equipment = $repository->findOneBy(['numero_equipement' => $equipmentId]);

            if (!$equipment) {
                $io->error("❌ Équipement {$equipmentId} non trouvé");
                return Command::FAILURE;
            }

            $result = $this->processEquipmentDetailed($equipment, $agency, $dryRun, $force, $io);
            
            $io->section('📊 Résultat détaillé');
            $io->definitionList(
                ['Status' => $result['status']],
                ['Message' => $result['message']],
                ['Photos téléchargées' => $result['photos_downloaded']],
                ['Photos disponibles' => $result['available_photos']],
                ['Chemin local' => $result['local_path'] ?? 'N/A']
            );

            if (!empty($result['errors'])) {
                $io->section('❌ Erreurs détectées');
                foreach ($result['errors'] as $error) {
                    $io->writeln("  • {$error}");
                }
            }

            return $result['status'] === 'migrated' ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $io->error("❌ Erreur: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function processEquipmentDetailed($equipment, string $agency, bool $dryRun, bool $force, SymfonyStyle $io): array
    {
        $equipmentId = $equipment->getNumeroEquipement();
        
        $result = [
            'equipment_id' => $equipmentId,
            'status' => 'skipped',
            'message' => 'Non traité',
            'photos_downloaded' => 0,
            'available_photos' => 0,
            'local_path' => null,
            'errors' => []
        ];

        try {
            // 1. Chercher les données Form
            $formData = $this->formRepository->findOneBy([
                'equipment_id' => $equipmentId
            ]);

            if (!$formData) {
                $result['message'] = 'Aucune donnée Form trouvée';
                return $result;
            }

            // 2. Vérifier les données API
            if (!$formData->getFormId() || !$formData->getDataId()) {
                $result['message'] = 'Données API manquantes';
                return $result;
            }

            // 3. Inventorier les photos disponibles
            $availablePhotos = $this->getAvailablePhotos($formData);
            $result['available_photos'] = count($availablePhotos);

            if (empty($availablePhotos)) {
                $result['message'] = 'Aucune photo disponible';
                return $result;
            }

            // 4. Calculer le chemin local
            $raisonSociale = explode('\\', $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale();
            $raisonSocialeClean = $this->cleanFileName($raisonSociale);
            $anneeVisite = date('Y', strtotime($equipment->getDateEnregistrement()));
            $typeVisite = $equipment->getVisite();

            $result['local_path'] = "{$agency}/{$raisonSocialeClean}/{$anneeVisite}/{$typeVisite}";

            // 5. Vérifier si déjà migré
            if (!$force && $this->isAlreadyMigrated($equipment, $agency, $availablePhotos)) {
                $result['message'] = 'Photos déjà migrées';
                return $result;
            }

            // 6. Migration des photos
            if (!$dryRun) {
                $downloadResult = $this->downloadPhotosDetailed($equipment, $formData, $agency, $availablePhotos);
                $result['photos_downloaded'] = $downloadResult['downloaded'];
                $result['errors'] = $downloadResult['errors'];
                
                if ($downloadResult['downloaded'] > 0) {
                    $result['status'] = 'migrated';
                    $result['message'] = "{$downloadResult['downloaded']}/{$result['available_photos']} photos téléchargées";
                } else {
                    $result['status'] = 'errors';
                    $result['message'] = 'Échec téléchargement';
                }
            } else {
                $result['status'] = 'migrated';
                $result['message'] = "{$result['available_photos']} photos peuvent être téléchargées";
                $result['photos_downloaded'] = $result['available_photos'];
            }

        } catch (\Exception $e) {
            $result['status'] = 'errors';
            $result['message'] = 'Erreur: ' . $e->getMessage();
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    private function downloadPhotosDetailed($equipment, $formData, string $agency, array $availablePhotos): array
    {
        $result = [
            'downloaded' => 0,
            'errors' => []
        ];

        $raisonSociale = explode('\\', $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale();
        $raisonSocialeClean = $this->cleanFileName($raisonSociale);
        $anneeVisite = date('Y', strtotime($equipment->getDateEnregistrement()));
        $typeVisite = $equipment->getVisite();
        $codeEquipement = $equipment->getNumeroEquipement();

        foreach ($availablePhotos as $photoType => $photoName) {
            try {
                // Gérer les photos multiples (séparées par des virgules)
                $photoNames = str_contains($photoName, ', ') ? explode(', ', $photoName) : [$photoName];
                
                foreach ($photoNames as $index => $singlePhotoName) {
                    $singlePhotoName = trim($singlePhotoName);
                    if (empty($singlePhotoName)) continue;

                    $filename = count($photoNames) > 1 
                        ? $codeEquipement . '_' . $photoType . '_' . ($index + 1)
                        : $codeEquipement . '_' . $photoType;

                    // Vérifier si existe déjà
                    if ($this->imageStorageService->imageExists($agency, $raisonSocialeClean, $anneeVisite, $typeVisite, $filename)) {
                        continue; // Déjà présente
                    }

                    // Télécharger depuis l'API Kizeo
                    $imageContent = $this->downloadFromKizeoApi($formData->getFormId(), $formData->getDataId(), $singlePhotoName);
                    
                    if ($imageContent) {
                        try {
                            // Sauvegarder localement
                            $savedPath = $this->imageStorageService->storeImage(
                                $agency,
                                $raisonSocialeClean,
                                $anneeVisite,
                                $typeVisite,
                                $filename,
                                $imageContent
                            );
                            
                            $result['downloaded']++;
                            
                        } catch (\Exception $e) {
                            $result['errors'][] = "Erreur sauvegarde {$filename}: " . $e->getMessage();
                        }
                    } else {
                        $result['errors'][] = "Erreur téléchargement {$singlePhotoName}";
                    }
                }

            } catch (\Exception $e) {
                $result['errors'][] = "Erreur traitement {$photoType}: " . $e->getMessage();
            }
        }

        return $result;
    }

    private function downloadFromKizeoApi(string $formId, string $dataId, string $photoName): ?string
    {
        try {
            $response = $this->client->request(
                'GET',
                "https://forms.kizeo.com/rest/v3/forms/{$formId}/data/{$dataId}/medias/{$photoName}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                    'timeout' => 30
                ]
            );

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $imageContent = $response->getContent();
            return !empty($imageContent) ? $imageContent : null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function getAvailablePhotos($formData): array
    {
        $photos = [];

        $photoMappings = [
            'compte_rendu' => $formData->getPhotoCompteRendu(),
            'environnement' => $formData->getPhotoEnvironnementEquipement1(),
            'plaque' => $formData->getPhotoPlaque(),
            'etiquette_somafi' => $formData->getPhotoEtiquetteSomafi(),
            'generale' => $formData->getPhoto2(),
            'moteur' => $formData->getPhotoMoteur(),
            'carte' => $formData->getPhotoCarte(),
            'choc' => $formData->getPhotoChoc(),
            'rail' => $formData->getPhotoRail()
        ];

        foreach ($photoMappings as $type => $photoName) {
            if (!empty($photoName)) {
                $photos[$type] = $photoName;
            }
        }

        return $photos;
    }

    private function isAlreadyMigrated($equipment, string $agency, array $availablePhotos): bool
    {
        $raisonSociale = explode('\\', $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale();
        $raisonSocialeClean = $this->cleanFileName($raisonSociale);
        $anneeVisite = date('Y', strtotime($equipment->getDateEnregistrement()));
        $typeVisite = $equipment->getVisite();
        $codeEquipement = $equipment->getNumeroEquipement();

        // Vérifier si au moins une photo existe localement
        foreach (array_keys($availablePhotos) as $photoType) {
            $filename = $codeEquipement . '_' . $photoType;
            if ($this->imageStorageService->imageExists($agency, $raisonSocialeClean, $anneeVisite, $typeVisite, $filename)) {
                return true;
            }
        }

        return false;
    }

    private function cleanFileName(string $name): string
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $name);
        $cleaned = preg_replace('/_+/', '_', $cleaned);
        return trim($cleaned, '_');
    }

    private function displayDetailedResults(SymfonyStyle $io, array $results, bool $dryRun): void
    {
        $io->section('📊 Résultats détaillés');
        
        $io->definitionList(
            ['Équipements traités' => $results['processed']],
            ['Migrés avec succès' => $results['migrated']],
            ['Ignorés' => $results['skipped']],
            ['Erreurs' => $results['errors']],
            ['Photos téléchargées' => $results['photos_downloaded']]
        );

        // Afficher les erreurs détaillées
        $errorsFound = false;
        foreach ($results['details'] as $detail) {
            if (!empty($detail['errors'])) {
                if (!$errorsFound) {
                    $io->section('❌ Erreurs détaillées');
                    $errorsFound = true;
                }
                $io->writeln("🔧 {$detail['equipment_id']}:");
                foreach ($detail['errors'] as $error) {
                    $io->writeln("  • {$error}");
                }
            }
        }

        $successRate = $results['processed'] > 0 
            ? round(($results['migrated'] / $results['processed']) * 100, 1) 
            : 0;

        $io->writeln("📈 Taux de succès: {$successRate}%");
    }
}

/**
 * UTILISATION DE LA VERSION CORRIGÉE :
 * 
 * # Test avec un seul équipement
 * php bin/console app:migrate-photos-fixed S140 --single=RAP01
 * 
 * # Migration de plusieurs équipements
 * php bin/console app:migrate-photos-fixed S140 --batch-size=5
 * 
 * # Mode simulation
 * php bin/console app:migrate-photos-fixed S140 --dry-run --batch-size=5
 * 
 * Cette version corrigée offre:
 * - Debug détaillé pour chaque équipement
 * - Comptage précis des photos téléchargées
 * - Gestion d'erreurs améliorée
 * - Rapport détaillé des résultats
 */