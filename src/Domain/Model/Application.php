<?php
namespace Greenfieldr\SurfBackups\Domain\Model;

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A backup application
 *
 */
class Application extends \TYPO3\Surf\Domain\Model\Application
{
    /**
     * @var string
     */
    protected $backupBasePath;

    /**
     * @var string
     */
    protected $backupDirectory = 'backups';

    /**
     * @var string
     */
    protected $backupSourcePath;

    /**
     * Backup application specific options
     *
     *   backupFilesystem: Backup filesystem?
     *
     *     Boolean, Default: true

     *   backupDatabase: Backup database?
     *
     *     Boolean, Default: true
     *
     *   transferMethod: How to transfer the package to the backup server
     *
     *     rsync, ftp, scp, Default: rsync
     *
     * @var array
     */
    protected $options = array(
        'backupFilesystem' => true,
        'backupDatabase' => true,
        'transferMethod' => 'rsync',
        'gzipBackup' => true
    );

    /**
     * Application constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * Register tasks for the backup application
     *
     * Initialize stage:
     *   - Create directories for release structure
     *
     * Update stage:
     *   - Perform Git checkout (and pass on sha1 / tag or branch option from application to the task)
     *
     * Switch stage:
     *   - Symlink the current and previous release
     *
     * Cleanup stage:
     *   - Clean up old releases
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        $this->registerTasksForInitializeStage($workflow);

        if ($this->hasOption('backupFilesystem') && $this->getOption('backupFilesystem') === true) {
            $this->registerTasksForFilesystemBackup($workflow);
        }
        if ($this->hasOption('backupDatabase') && $this->getOption('backupDatabase') === true) {
            $this->registerTasksForDatabaseBackup($workflow);
        }
        if ($this->hasOption('transferMethod')) {
            $this->registerTasksForTransferMethod($workflow, $this->getOption('transferMethod'));
        }
        if ($this->hasOption('gzipBackup')) {
            $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Finalize\\LocalGzipTask', 'finalize', $this);
        }

        $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Finalize\\CleanupTransferCache', 'finalize', $this);
        $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Finalize\\SymlinkTask', 'finalize', $this);
    }

    /**
     * @param Workflow $workflow
     */
    protected function registerTasksForInitializeStage(Workflow $workflow)
    {
        $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Filesystem\\CreateDirectoriesTask', 'initialize', $this);
    }

    /**
     * @param Workflow $workflow
     */
    protected function registerTasksForFilesystemBackup(Workflow $workflow)
    {
    }

    /**
     * @param Workflow $workflow
     */
    protected function registerTasksForDatabaseBackup(Workflow $workflow)
    {

        $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Database\\DumpDatabaseTask', 'createDatabaseDump');
    }

    /**
     * @param Workflow $workflow
     * @param string $transferMethod
     * @return void
     */
    protected function registerTasksForTransferMethod(Workflow $workflow, $transferMethod)
    {
        switch ($transferMethod) {
            case 'git':
                // TODO
                break;
            case 'rsync':
                $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Transfer\\DirectRsyncTask', 'transfer', $this);
                $workflow->addTask('Greenfieldr\\SurfBackups\\Task\\Transfer\\RsyncDatabaseDumpsTask', 'transfer', $this);
                break;
            case 'scp':
                // TODO
                break;
            case 'ftp':
                // TODO
                break;
        }
    }

    /**
     * @return string
     */
    public function getBackupBasePath()
    {
        return $this->backupBasePath;
    }

    /**
     * @param string $backupBasePath
     */
    public function setBackupBasePath($backupBasePath)
    {
        $this->backupBasePath = rtrim($backupBasePath, '/');
    }

    /**
     * @return string
     */
    public function getBackupSourcePath()
    {
        return $this->backupSourcePath;
    }

    /**
     * @param string $backupSourcePath
     */
    public function setBackupSourcePath($backupSourcePath)
    {
        $this->backupSourcePath = rtrim($backupSourcePath, '/') . '/';
    }

    /**
     * @return string
     */
    public function getBackupDirectory()
    {
        return $this->backupDirectory;
    }

    /**
     * @param string $backupDirectory
     */
    public function setBackupDirectory($backupDirectory)
    {
        $this->backupDirectory = $backupDirectory;
    }

    /**
     * Extends original method to handle backup base path
     *
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        switch ($key) {
            case 'backupBasePath':
                return $this->getBackupBasePath();
            default:
                return parent::getOption($key);
        }
    }

    /**
     * Returns path to the directory with backups
     *
     * @return string Path to the backup directory
     */
    public function getBackupPath()
    {
        return rtrim($this->getBackupBasePath() . '/' . $this->getBackupDirectory(), '/');
    }
}