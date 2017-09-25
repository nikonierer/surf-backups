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
class CreateDirectoriesTask
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
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        $this->createWorkspacePath($node, $deployment, $application);
        $this->createCacheFolder($node, $deployment, $application);
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
     * @param Node $node
     * @param Deployment $deployment
     * @param \Greenfieldr\SurfBackups\Domain\Model\Application $application
     */
    protected function createWorkspacePath($node, $deployment, $application)
    {
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        $command = 'mkdir -p ' . $deployment->getBackupWorkspacesPath($application);
        $this->shell->executeOrSimulate($command, $node, $deployment);
    }

    /**
     * @param Node $node
     * @param Deployment $deployment
     * @param \Greenfieldr\SurfBackups\Domain\Model\Application $application
     */
    protected function createCacheFolder($node, $deployment, $application)
    {
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        $command =
            'mkdir -p ' . \TYPO3\Flow\Utility\Files::getNormalizedPath(
                $deployment->getBackupWorkspacesPath($application)
            ) . 'transfer/cache';
        $this->shell->executeOrSimulate($command, $node, $deployment);
    }

}
