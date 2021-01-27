<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\PrestashopConnectorService;
use PDO;

class ImportStarWarsCommand extends Command{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(){
        $this
            ->setName('starwars:import')
            ->setDescription('Comando que importa datos de Star Wars');
    }

    protected function execute(InputInterface $input, OutputInterface $output){

		$output->writeln('Iniciando importación.');

        
        $output->writeln('Fin test.');		

    }

}