<?php

namespace Tms\Bundle\ModelIOBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;

class ImportEntityCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tms:modelio:import-entity')
            ->setDescription('Import entity based on serialized data')
            ->addArgument('entityClassName', InputArgument::REQUIRED, 'The entity class name to import')
            ->addArgument('entityData', InputArgument::REQUIRED, 'The entity data serialized to import')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The data format')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allow to import object.
Here is some examples:

<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json</info>

The default format is json.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClassName = $input->getArgument('entityClassName');
        $entityData      = $input->getArgument('entityData');
        $format          = $input->getOption('format') ?
            $input->getOption('format') :
            'json'
        ;

        $importer = $this->getContainer()->get('tms_model_io.importer.entity');

        try {
            $entity = $importer->import(
                $entityClassName,
                $entityData,
                $format
            );

            $output->writeln(sprintf('<info>Entity %s imported</info>', $entity->getId()));

            return $entity->getId();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>The import failed: %s</error>', $e->getMessage()));

            return -1;
        }
    }
}
