<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:compile-latex',
    description: 'Add a short description for your command',
)]
class CompileLatexCommand extends Command
{

    public function __construct(
        private KernelInterface $kernel
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Numero annexe')
            ->addArgument('arg2', InputArgument::OPTIONAL, 'fichier latex')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $arg2 = $input->getArgument('arg2');

        $chemin = $this->kernel->getProjectDir().'/public/pdf/'.$arg1.'/';
        $text = shell_exec('pdflatex -output-directory ' . $chemin.' '.$arg2);
        //$io->note($text);
        return Command::SUCCESS;
    }
}
