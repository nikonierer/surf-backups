<?php
namespace Greenfieldr\SurfBackups\Task\Finalize;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A symlink task for switching over the current directory to the new backup
 *
 */
class SymlinkTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Executes this task
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

        $releaseIdentifier = $deployment->getReleaseIdentifier();
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        $releasesPath =
            $application->getBackupBasePath() . '/' .
            $deployment->getName() . '/' .
            $application->getName() . '/releases/';
        $this->shell->executeOrSimulate('cd ' . $releasesPath . ' && rm -f ./current && ln -s ./' . $releaseIdentifier . ' ./current',
            $localhost, $deployment);
    }
}