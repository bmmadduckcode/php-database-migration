<?php
/**
 * User: aguidet
 * Date: 27/02/15
 * Time: 17:41
 */

namespace Migrate\Command;

use Migrate\Config\ConfigLocator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddEnvCommand extends AbstractEnvCommand
{

    protected function configure()
    {
        $this
            ->setName('migrate:addenv')
            ->setDescription('Initialise an environment to work with php db migrate')
            ->addArgument(
                'envname',
                InputArgument::REQUIRED,
                'Name of the new environment, default: dev'
            )->addArgument(
                'driver',
                InputArgument::REQUIRED,
                'PDO driver'
            )->addArgument(
                'dbname',
                InputArgument::REQUIRED,
                'Database name (or the database file location)'
            )->addArgument(
                'dbhost',
                InputArgument::REQUIRED,
                'Database host'
            )->addArgument(
                'dbport',
                InputArgument::REQUIRED,
                'Database port'
            )->addArgument(
                'dbusername',
                InputArgument::REQUIRED,
                'Database username'
            )->addArgument(
                'dbpassword',
                InputArgument::REQUIRED,
                'Database password'
            )->addArgument(
                'format',
                InputArgument::OPTIONAL,
                'Environment file format: (yml, json or php), default: yml'
            )->addArgument(
                'dbcharset',
                InputArgument::OPTIONAL,
                'Database charset (if needed)'
            )->addArgument(
                'changelogtable',
                InputArgument::OPTIONAL,
                'Changelog table, default: changelog'
            )->addArgument(
                'defaulteditor',
                InputArgument::OPTIONAL,
                'Text editor to use by default, default: vim'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getArgument('format');
        $envName = $input->getArgument('envname');
        $driver = $input->getArgument('driver');
        $dbName = $input->getArgument('dbname');
        $dbHost = $input->getArgument('dbhost');
        $dbPort = $input->getArgument('dbport');
        $dbUserName = $input->getArgument('dbusername');
        $dbUserPassword = $input->getArgument('dbpassword');
        $dbCharset = $input->getArgument('dbcharset');
        $changelogTable = $input->getArgument('changelogtable');
        $defaultEditor = $input->getArgument('defaulteditor');

        $supportedFormats = array_keys(ConfigLocator::$SUPPORTED_PARSERS);

        if (is_null($format)) {
            $format = 'yml';
        }

        if (!in_array($format, $supportedFormats)) {
            throw new \RuntimeException(sprintf('Invalid file format: %s', $format));
        }

        // init directories
        if (! file_exists($this->getMainDir())) {
            mkdir($this->getMainDir());
        }

        if (! file_exists($this->getEnvironmentDir())) {
            mkdir($this->getEnvironmentDir());
        }

        if (! file_exists($this->getMigrationDir())) {
            mkdir($this->getMigrationDir());
        }

        $drivers = pdo_drivers();

        if (!in_array($driver, $drivers)) {
            throw new \RuntimeException(sprintf('Invalid driver format: %s', $driver));
        }

        $envConfigFile = $this->getEnvironmentDir() . '/' . $envName . '.' . $format;
        if (file_exists($envConfigFile)) {
            throw new \InvalidArgumentException("environment [$envName] is already defined!");
        }

        $confTemplate = file_get_contents(__DIR__ . '/../../templates/env.' . $format . '.tpl');
        $confTemplate = str_replace('{DRIVER}', $driver, $confTemplate);
        $confTemplate = str_replace('{HOST}', $dbHost, $confTemplate);
        $confTemplate = str_replace('{PORT}', $dbPort, $confTemplate);
        $confTemplate = str_replace('{USERNAME}', $dbUserName, $confTemplate);
        $confTemplate = str_replace('{PASSWORD}', $dbUserPassword, $confTemplate);
        $confTemplate = str_replace('{DATABASE}', $dbName, $confTemplate);
        $confTemplate = str_replace('{CHARSET}', $dbCharset, $confTemplate);
        $confTemplate = str_replace('{CHANGELOG}', $changelogTable, $confTemplate);
        $confTemplate = str_replace('{EDITOR}', $defaultEditor, $confTemplate);

        file_put_contents($envConfigFile, $confTemplate);
    }
}
