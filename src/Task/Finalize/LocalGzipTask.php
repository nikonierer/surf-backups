<?php
namespace Greenfieldr\SurfBackups\Task\Finalize;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to Gzip a transferred backup
 */
class LocalGzipTask
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

        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        $releasePath = $deployment->getBackupReleasePath($application) . '/';
        $command =
            "cd {$releasePath} && tar -zcvf {$node->getName()}.tar.gz ./{$node->getName()} && " .
            "rm -rf ./{$node->getName()}";

        $this->shell->executeOrSimulate($command, $localhost, $deployment);
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

}
