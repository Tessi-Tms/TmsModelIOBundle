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
            ->setDescription('Import object based on serialized data')
            ->addArgument('objectClassName', InputArgument::REQUIRED, 'The object class name to import')
            ->addArgument('objectData', InputArgument::REQUIRED, 'The object data serialized to import')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The data format')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command', 'default')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allow to import object.
Here is some examples:

<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json --em=default</info>
which is equivalent to:
<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json</info>

you could also specify the entity manager:
<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json --em=my_manager</info>

default format is json and default entity manager is the default.
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

        $importer = $this->getContainer()->get('tms_model_io.importer.entity');

        try {
            $entity = $importer->import(
                $objectClassName,
                $objectData,
                $format
            );

            $importer->flush();
            $output->writeln(sprintf('<info>Entity %s imported</info>', $entity->getId()));

            return $entity->getId();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>The import failed: %s</error>', $e->getMessage()));

            return -1;
        }
    }
}
