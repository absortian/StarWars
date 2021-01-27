<?php
namespace App\Command;

use App\Service\APIService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportStarWarsCommand extends Command{

    public function __construct(APIService $APIService)
    {
        parent::__construct();
        $this->APIService = $APIService;
    }

    protected function configure(){
        $this
            ->setName('starwars:import')
            ->setDescription('Comando que importa datos de Star Wars');
    }

    protected function execute(InputInterface $input, OutputInterface $output){

		$output->writeln('Iniciando importación.');

        $this->APIService->importAllMovies();
        
        $output->writeln('Fin importación.');		

    }

}