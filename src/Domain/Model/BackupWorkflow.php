<?php
namespace Greenfieldr\SurfBackups\Domain\Model;

use TYPO3\Surf\Exception\InvalidConfigurationException;
use Greenfieldr\SurfBackups\Domain\Model\Backup;

/**
 * A backup workflow
 *
 */
class BackupWorkflow extends \TYPO3\Surf\Domain\Model\SimpleWorkflow
{
    /**
     * As it does not make sense to have a rollback procedure for backups, we'll simply disable this feature.
     * @ToDo It might be an advisable entry point for more specific logging or mail notifications. To be discussed...
     *
     * @var bool
     */
    protected $enableRollback = false;

    /**
     * Order of stages that will be executed
     *
     * @var array
     */
    protected $stages = array(
        // Initialize directories etc. (first time backup)
        'initialize',
        // Collect files to backup based on configuration
        'collectFiles',
        // Create database dump as SQL file based on configuration
        'createDatabaseDump',
        // Local preparation of backup package which need to be transferred
        'package',
        // Transfer of application assets to the node
        'transfer',
        // Finalizes backup
        'finalize',
    );

    /**
     * Sequentially execute the stages for each node, so first all nodes will go through the initialize stage and
     * then the next stage will be executed until the final stage is reached and the workflow is finished.
     *
     * A rollback will be done for all nodes as long as the stage switch was not completed and the feature is enabled.
     *
     * @param \TYPO3\Surf\Domain\Model\Deployment $backup
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function run(\TYPO3\Surf\Domain\Model\Deployment $backup)
    {
        parent::run($backup);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Backup workflow';
    }
}
