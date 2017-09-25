<?php
namespace Greenfieldr\SurfBackups\Integration;

/**
 * Class Factory
 */
class Factory extends \TYPO3\Surf\Integration\Factory
{

    /**
     * Create the necessary commands
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function createCommands()
    {
        return array(
            new \Greenfieldr\SurfBackups\Command\BackupCommand(),
        );
    }

    /**
     * Get a deployment object by deployment name
     *
     * Looks up the deployment in directory ./.surf/[deploymentName].php
     *
     * The script has access to a deployment object as "$deployment". This could change
     * in the future.
     *
     * @param string $deploymentName
     * @param string $path
     * @return \TYPO3\Surf\Domain\Model\Deployment
     * @throws \RuntimeException
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function createDeployment($deploymentName, $path = null)
    {
        $deploymentConfigurationPath = $this->getDeploymentsBasePath($path);
        $workspacesBasePath = $this->getWorkspacesBasePath();

        if (empty($deploymentName)) {
            $deploymentNames = $this->getDeploymentNames($path);
            if (count($deploymentNames) !== 1) {
                throw new \TYPO3\Surf\Exception\InvalidConfigurationException('No deployment name given!', 1451865016);
            }
            $deploymentName = array_pop($deploymentNames);
        }

        $deploymentPathAndFilename = \TYPO3\Flow\Utility\Files::concatenatePaths(
            array($deploymentConfigurationPath, $deploymentName . '.php')
        );
        if (file_exists($deploymentPathAndFilename)) {
            $deployment = new \Greenfieldr\SurfBackups\Domain\Model\Backup($deploymentName);
            $deployment->setDeploymentBasePath($deploymentConfigurationPath);
            $deployment->setWorkspacesBasePath($workspacesBasePath);
            $tempPath = \TYPO3\Flow\Utility\Files::concatenatePaths(array($workspacesBasePath, $deploymentName));
            $this->ensureDirectoryExists($tempPath);
            $deployment->setTemporaryPath($tempPath);
            /** @noinspection PhpIncludeInspection */
            require($deploymentPathAndFilename);
        } else {
            $this->createLogger()->error(sprintf("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
            $deployment = new \TYPO3\Surf\Domain\Model\FailedDeployment();
        }
        return $deployment;
    }
}
