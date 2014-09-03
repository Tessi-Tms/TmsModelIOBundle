<?php

namespace Tms\Bundle\ModelIOBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportObjectCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tms-modelio:import')
            ->setDescription('Import object based on serialized data')
            ->addArgument('objectClassName', InputArgument::REQUIRED, 'The object class name to import')
            ->addArgument('objectData', InputArgument::REQUIRED, 'The object data serialized to import')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The data format')
            ->addOption('objectManager', null, InputOption::VALUE_REQUIRED, 'The objectManager')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allow to import object.
Here is an example:

<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json </info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectClassName = $input->getArgument('objectClassName');
        $objectData      = $input->getArgument('objectData');
        $format          = $input->getOption('format') ?
            $input->getOption('format') :
            'json'
        ;

        $objectManager = $input->getOption('objectManager') ?
            $this->getContainer()->get($input->getOption('objectManager'))->getManager() :
            $this->getContainer()->get('doctrine')->getManager() 
        ;

        $importer = $this->getContainer()->get('tms_model_io.importer');

        try {
            $output->writeln('<info>Start object import</info>');
            $object = $importer->import(
                $objectClassName,
                $objectData,
                $format
            );
            var_dump($object);
            $objectManager->persist($object);
            $objectManager->flush();
            $output->writeln('<info>Object imported</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>The import failed: %s</error>', $e->getMessage()));
        }
    }
}
