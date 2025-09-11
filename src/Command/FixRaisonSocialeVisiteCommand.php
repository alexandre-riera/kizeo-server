<?php
// src/Command/FixRaisonSocialeVisiteCommand.php

namespace App\Command;

use App\Entity\Form;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-raison-sociale-visite',
    description: 'Corrige les champs raison_sociale_visite manquants dans la table Form'
)]
class FixRaisonSocialeVisiteCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('agence', InputArgument::REQUIRED, 'Code de l\'agence (ex: S140)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agenceCode = $input->getArgument('agence');

        $io->title("Correction des champs raison_sociale_visite pour l'agence {$agenceCode}");

        try {
            // Récupérer le repository de l'agence
            $agencyClass = "App\\Entity\\Equipement{$agenceCode}";
            if (!class_exists($agencyClass)) {
                $io->error("Classe d'équipement non trouvée : {$agencyClass}");
                return Command::FAILURE;
            }

            $repository = $this->entityManager->getRepository($agencyClass);
            
            // Récupérer les Forms avec raison_sociale_visite manquant
            $qb = $this->entityManager->getRepository(Form::class)->createQueryBuilder('f')
                ->where('f.raison_sociale_visite IS NULL OR f.raison_sociale_visite = :empty')
                ->andWhere('f.code_equipement IS NOT NULL')
                ->setParameter('empty', '');

            $formsToFix = $qb->getQuery()->getResult();
            $totalForms = count($formsToFix);

            if ($totalForms === 0) {
                $io->success('Aucune correction nécessaire !');
                return Command::SUCCESS;
            }

            $io->progressStart($totalForms);

            $fixed = 0;
            $errors = 0;

            foreach ($formsToFix as $form) {
                try {
                    $codeEquipement = $form->getCodeEquipement();
                    
                    // Trouver l'équipement correspondant
                    $equipment = $repository->findOneBy(['numero_equipement ' => $codeEquipement]);
                    
                    if ($equipment) {
                        $raisonSocialeVisite = $equipment->getRaisonSociale() . "\\" . $equipment->getVisite();
                        $form->setRaisonSocialeVisite($raisonSocialeVisite);
                        
                        $this->entityManager->persist($form);
                        $fixed++;
                    } else {
                        $errors++;
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    $io->warning("Erreur pour équipement {$form->getCodeEquipement()}: " . $e->getMessage());
                }
                
                $io->progressAdvance();
            }

            $this->entityManager->flush();
            $io->progressFinish();

            $io->success([
                "Correction terminée !",
                "Enregistrements corrigés : {$fixed}",
                "Erreurs : {$errors}",
                "Total traité : {$totalForms}"
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("Erreur globale : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}