<?php
namespace Greenfieldr\SurfBackups\Domain\Model;

/**
 * A backup model
 *
 */
class Backup extends \TYPO3\Surf\Domain\Model\Deployment
{
    /**
     * @var string
     */
    protected $backupWorkspacesPath;

    /**
     * Backup constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * Run this deployment
     *
     * @return void
     */
    public function deploy()
    {
        $this->logger->notice('Backing up ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->workflow->run($this);
    }
    /**
     * @param Application $application
     * @return string
     */
    public function getBackupWorkspacesPath(Application $application)
    {
        return $this->backupWorkspacesPath . '/' . $this->getName() . '/' . $application->getName();
    }

    /**
     * @param string $backupWorkspacesPath
     */
    public function setBackupWorkspacesPath($backupWorkspacesPath)
    {
        $this->backupWorkspacesPath = rtrim($backupWorkspacesPath, '/');
    }

    /**
     *
     * @param Application $application
     * @return string
     */
    public function getBackupReleasePath(Application $application)
    {
        return
            $application->getBackupBasePath() . '/' .
            $this->getName() . '/' .
            $application->getName() .
            $application->getReleasesPath() . '/' .
            $this->getReleaseIdentifier();
    }

    /**
     * @param Application $application
     * @return string
     */
    public function getLocalWorkspacePath(Application $application)
    {
        return
            $application->getBackupBasePath() . '/' .
            $this->getName() . '/' .
            $application->getName() .
            $application->getReleasesPath() . '/transfer/cache/';
    }

    /**
     * @param Application $application
     * @return string
     */
    public function getBackupWorkspacesTransferCachePath(Application $application)
    {
        return $this->backupWorkspacesPath . '/' . $this->getName() . '/' . $application->getName() . '/transfer/cache/';
    }
}