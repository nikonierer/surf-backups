<?php
namespace Greenfieldr\SurfBackups\Task\Filesystem;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to collect files for backup
 *
 * Copies the files to backup in the remote workspace folder
 */
class CollectFilesTask
    extends \TYPO3\Surf\Domain\Model\Task
    implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
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
        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');
        $quietFlag = (isset($options['verbose']) && $options['verbose']) ? '' : '-q';
        $rsyncExecutable =
            ($node->hasOption('rsyncExecutable')) ?
                $node->getOption('rsyncExecutable') : 'rsync';

        $rsyncExcludes = isset($options['rsyncExcludes']) ? $options['rsyncExcludes'] : array();
        $excludeFlags = $this->getExcludeFlags($rsyncExcludes);

        $rsyncFlags =
            isset($options['rsyncFlags']) ?
                $options['rsyncFlags'] :
                '--recursive --times --perms --links --delete --delete-excluded' . $excludeFlags;

        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        $relativeFlag = (substr($deployment->getBackupWorkspacesPath($application), 0, 1) !== '/') ? '-R' : '';

        $backupSourcePath = $application->getBackupSourcePath();
        $command = "{$rsyncExecutable} {$quietFlag} {$relativeFlag} --compress {$rsyncFlags} " .
            escapeshellarg(rtrim($backupSourcePath, '/') . '/') . ' ' . escapeshellarg($deployment->getBackupWorkspacesTransferCachePath($application));

        $this->shell->executeOrSimulate($command, $node, $deployment);

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
    }

    /**
     * Generates the --exclude flags for a given array of exclude patterns
     *
     * Example: ['foo', '/bar'] => --exclude 'foo' --exclude '/bar'
     *
     * @param array $rsyncExcludes An array of patterns to be excluded
     * @return string
     */
    protected function getExcludeFlags($rsyncExcludes)
    {
        return array_reduce($rsyncExcludes, function ($excludeOptions, $pattern) {
            return $excludeOptions . ' --exclude ' . escapeshellarg($pattern);
        }, '');
    }

}
