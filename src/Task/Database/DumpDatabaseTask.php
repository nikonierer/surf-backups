<?php
namespace Greenfieldr\SurfBackups\Task\Database;

use Symfony\Component\Process\ProcessBuilder;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;

/**
 * This task dumps a complete database into a file
 */
class DumpDatabaseTask extends Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * @var array
     */
    protected $requiredOptions = array('databaseHost', 'databaseUsername', 'databasePassword', 'databaseName');

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
        /** @var \Greenfieldr\SurfBackups\Domain\Model\Application $application */
        $this->assertRequiredOptionsExist($options);

        // Cleanup old database dumps in cache folder
        $command =
            "rm -f {$deployment->getBackupWorkspacesTransferCachePath($application)}*.sql*";
        $this->shell->executeOrSimulate($command, $node, $deployment);

        $gzip =
            (isset($options['databaseGzipCompression']) && $options['databaseGzipCompression'] === true) ?
                ['command' => '| gzip -c ', 'extension' => '.gzip'] : ['command' => '', 'extension' => ''];

        $filename = $options['databaseName'].'.sql';
        $filePath = $deployment->getBackupWorkspacesTransferCachePath($application);
        $port = isset($options['databasePort']) ? $options['databasePort'] : '';
        $command = "mysqldump " .
            "-h{$options['databaseHost']} " .
            "-u{$options['databaseUsername']} " .
            "-p{$options['databasePassword']} " .
            "{$port}" .
            "{$options['databaseName']} -r " .
            "{$filePath}{$filename}";
        $this->shell->executeOrSimulate($command, $node, $deployment);

        if (isset($options['databaseGzipCompression']) && $options['databaseGzipCompression'] === true) {
            $command = "gzip -c {$filePath}{$filename} > {$filePath}{$filename}.gz && rm -f {$filePath}{$filename}";
            $this->shell->executeOrSimulate($command, $node, $deployment);
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
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function assertRequiredOptionsExist(array $options)
    {
        foreach ($this->requiredOptions as $optionName) {
            if (!isset($options[$optionName])) {
                throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Required option "%s" is not set!', $optionName), 1405592631);
            }
        }
    }
}
