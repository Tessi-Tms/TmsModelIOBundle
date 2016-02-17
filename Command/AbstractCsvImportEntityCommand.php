<?php

namespace Tms\Bundle\ModelIOBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Util\Inflector;

abstract class AbstractCsvImportEntityCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $defaultFilePath = 'undefined';

    /**
     * @var string
     */
    protected $filePath = null;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(sprintf('tms-import:entity:%s', $this->getCommandName()))
            ->setDescription(sprintf('Import entity %s', $this->getClassName()))
            ->addArgument('filePath', InputArgument::REQUIRED, 'The file path to use')
            ->addOption('with-header', 'w', InputOption::VALUE_NONE, 'Add this option if the CSV file contains a header')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

Here is an example:
<info>php app/console %command.name% filePathToImport</info>

To prevent CSV header import:
<info>php app/console %command.name% filePathToImport [-w|--with-header]</info>

(Digifid) File path to use: $this->defaultFilePath
EOT
            )
        ;
    }

    /**
     * Get command name based on the entity class name
     *
     * @return string
     */
    protected function getCommandName()
    {
        $exploded = explode('\\', $this->getClassName());
        $tablized = Inflector::tableize(array_pop($exploded));

        return str_replace('_', '-', $tablized);
    }

    /**
     * Get Importer service
     *
     * @return Tms\Bundle\ModelIOBundle\Import\EntityImporter
     */
    protected function getEntityImporter()
    {
        return $this->getContainer()->get('tms_model_io.importer.entity');
    }

    /**
     * Get the entity repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this
            ->getEntityImporter()
            ->getEntityManager()
            ->getRepository($this->getClassName())
        ;
    }

    /**
     * Load data
     *
     * @param  boolean $hasHeader
     * @return array
     */
    protected function loadData($hasHeader = true)
    {
        $rows = array();
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 5000, ";", '"')) !== FALSE) {
                $row = $this->createMappedRowData($data);
                if (null !== $row) {
                    $rows[] = $row;
                }
            }

            fclose($handle);
        }

        // Remove the first row if hasHeader is true
        if ($hasHeader) {
            unset($rows[0]);
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $hasHeader = $input->getOption('with-header');
        $this->filePath  = $input->getArgument('filePath');

        $countImported = 0;

        $output->writeln(sprintf(
            '<comment>Start %s import</comment>',
            $this->getClassName()
        ));

        $rows = $this->loadData($hasHeader);
        foreach ($rows as $i => $row) {
            try {
                // Check if the object already exist
                $this->checkExistingRow($row);

                // Create the Object
                $entity = $this
                    ->getEntityImporter()
                    ->createObject(
                        $this->getClassName(),
                        json_encode($row)
                    )
                ;

                // Pre persist action
                $this->prePersist($row, $entity);

                // Persist and flush
                $this
                    ->getEntityImporter()
                    ->persist($entity)
                    ->flush()
                ;

                // Post persist action
                $this->postPersist($row, $entity);

                $output->writeln(sprintf(
                    '<info>l%d > %s imported: created with id [%d]</info>',
                    $i + 1,
                    $this->getClassName(),
                    $entity->getId()
                ));

                $this->getEntityImporter()->clear();
                $countImported++;
            } catch (\Exception $e) {
                $output->writeln(sprintf(
                    '<error>l%d > %s not imported: %s</error>',
                    $i + 1,
                    $this->getClassName(),
                    $e->getMessage()
                ));

                continue;
            }
        }

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;

        $output->writeln(sprintf(
            '<comment>%d/%d %s imported [%d sec]</comment>',
            $countImported,
            count($rows),
            $this->getClassName(),
            $time
        ));
    }

    /**
     * Pre persist
     *
     * @param array  $row
     * @param object $entity
     * @throw \Exception If an error occured
     */
    protected function prePersist($row, & $entity)
    {
        return true;
    }

    /**
     * Post persist
     *
     * @param array  $row
     * @param object $entity
     * @throw \Exception If an error occured
     */
    protected function postPersist($row, & $entity)
    {
        return true;
    }

    /**
     * Create mapped row data
     *
     * @param  array $data A row data
     * @return array
     */
    abstract protected function createMappedRowData(array $data);

    /**
     * Check if the row was already imported
     *
     * @param array $row A mapped data
     * @throw \Exception If entity exist
     */
    abstract protected function checkExistingRow(array $row);

    /**
     * Get the entity ClassName to import
     *
     * @return strung
     */
    abstract public function getClassName();
}