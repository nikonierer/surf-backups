<?php
namespace Greenfieldr\SurfBackups\Command;

/**
 * Surf backup command
 */
class BackupCommand extends \TYPO3\Surf\Command\DeployCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('backup')
            ->addArgument(
                'deploymentName',
                \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
                'The deployment name'
            )
            ->addOption(
                'configurationPath',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            );
    }
}