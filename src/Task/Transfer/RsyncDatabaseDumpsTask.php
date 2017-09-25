<?php
namespace Greenfieldr\SurfBackups\Task\Transfer;

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A rsync transfer task
 *
 * Copies the collected backup package to the backup destination
 */
class RsyncDatabaseDumpsTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        $packagePath = $deployment->getBackupWorkspacesTransferCachePath($application);
        $releasePath = $deployment->getBackupReleasePath($application);

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        $command =
            'mkdir -p ' . $deployment->getBackupReleasePath($application) . ' && ' .
            'mkdir -p ' . $deployment->getLocalWorkspacePath($application);
        $this->shell->executeOrSimulate($command, $localhost, $deployment);

        $username = $node->hasOption('username') ? $node->getOption('username') . '@' : '';
        $hostname = $node->getHostname();
        $port = $node->hasOption('port') ? ' -p ' . escapeshellarg($node->getOption('port')) : '';
        $key = $node->hasOption('privateKeyFile') ? ' -i ' . escapeshellarg($node->getOption('privateKeyFile')) : '';
        $quietFlag = (isset($options['verbose']) && $options['verbose']) ? '' : '-q';
        $rshFlag = '--rsh="ssh' . $port . $key . '" ';

        $packagePath = "{$username}{$hostname}:{$packagePath}";
        $command =
            "rsync {$quietFlag} --compress {$rshFlag} " .
            escapeshellarg($packagePath) . '*.sql* ' . escapeshellarg($deployment->getLocalWorkspacePath($application));

        $this->shell->executeOrSimulate($command, $localhost, $deployment);

        $command =
            strtr("cp -RPp " . $deployment->getLocalWorkspacePath($application) . "/*.sql* $releasePath", "\t\n", '  ');

        $this->shell->executeOrSimulate($command, $localhost, $deployment);

        if ($node->hasOption('password')) {
            $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths(array(
                dirname(dirname(dirname(__DIR__))),
                'Resources',
                'Private/Scripts/PasswordSshLogin.expect'
            ));
            if (\Phar::running() !== '') {
                $passwordSshLoginScriptContents = file_get_contents($passwordSshLoginScriptPathAndFilename);
                $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths(array(
                    $deployment->getTemporaryPath(),
                    'PasswordSshLogin.expect'
                ));
                file_put_contents($passwordSshLoginScriptPathAndFilename, $passwordSshLoginScriptContents);
            }
            $command = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename),
                escapeshellarg($node->getOption('password')), $command);

            $this->shell->executeOrSimulate($command, $localhost, $deployment);
        }

        if (isset($passwordSshLoginScriptPathAndFilename) && \Phar::running() !== '') {
            unlink($passwordSshLoginScriptPathAndFilename);
        }

    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $releasePath = $deployment->getApplicationReleasePath($application);
        $this->shell->execute('rm -Rf ' . $releasePath, $node, $deployment, true);
    }
}
