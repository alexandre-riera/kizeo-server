<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FormRepository;

#[AsCommand(
    name: 'app:fix-raison-sociale-visite',
    description: 'Corriger les champs raison_sociale_visite manquants dans la table Form'
)]
class FixRaisonSocialeVisiteCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormRepository $formRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Corriger les champs raison_sociale_visite manquants')
            ->addArgument('agency', InputArgument::REQUIRED, 'Code agence (S10, S40, etc.)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agency = $input->getArgument('agency');
        
        $io->title("🔧 Correction des champs raison_sociale_visite manquants pour {$agency}");
        
        try {
            $results = $this->formRepository->fixMissingRaisonSocialeVisite($agency);
            
            $io->success("✅ Correction terminée !");
            $io->table(['Métrique', 'Nombre'], [
                ['Entrées corrigées', $results['fixed']],
                ['Erreurs rencontrées', $results['errors']]
            ]);
            
            if ($results['fixed'] > 0) {
                $io->note("Tu peux maintenant relancer le script migrate_kizeo_forms.sh pour que les photos soient correctement liées.");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error("❌ Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}