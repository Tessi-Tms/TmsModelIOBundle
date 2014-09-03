<?php

namespace Tms\Bundle\ModelIOBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;

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
            ->addOption('om', null, InputOption::VALUE_REQUIRED, 'The specified object manager to use for this command')
            ->addOption('em', null, InputOption::VALUE_NONE, 'The entity manager to use for this command')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'The entity manager to use for this command')
            ->addOption('dm', null, InputOption::VALUE_NONE, 'The document manager to use for this command')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allow to import object.
Here is some examples:

<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json --om=default</info>
which is equivalent to:
<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json --em</info>

you could also use document manager:
<info>php app/console %command.name% CLASSNAME {JSON_DATA} --format=json --dm</info>

options <info>--dm --em --om</info> are exclusive, they can't be used together.

default format is json and default object manager is default entity manager.
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

        $sum = ($input->getOption('dm')? 1: 0) + ($input->getOption('em')? 1: 0) + ($input->getOption('om')? 1: 0);

        if($sum !== 0 && $sum !== 1) {
            throw new \Exception("<error>options <info>--dm --em --om</info> are exclusive, they can't be used together</error>", 1);
        }

        $config = 'doctrine.orm.entity_manager';
        if ($input->getOption('dm')) {
            $config = 'doctrine.odm.mongodb.document_manager';
        }

        $objectManager = $this->getContainer()->get($config);

        if ($input->getOption('om')) {
            $objectManager = $this->getContainer()->get('doctrine')->getManager();
            DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('om'));
        }

        $importer = $this->getContainer()->get('tms_model_io.importer');

        try {
            $output->writeln('<info>Start object import</info>');
            $object = $importer->import(
                $objectClassName,
                $objectData,
                $format
            );

            $objectManager->persist($object);
            $objectManager->flush();
            $output->writeln('<info>Object imported</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>The import failed: %s</error>', $e->getMessage()));
        }
    }
}
