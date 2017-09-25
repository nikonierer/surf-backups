<?php
$workflow = new \Greenfieldr\SurfBackups\Domain\Model\BackupWorkflow();
$node = new \Greenfieldr\SurfBackups\Domain\Model\Node('example.com');
$node->setHostname('example.com');
$node->setOption('username', 'sshuser');

$node->setOption('databaseHost', 'localhost');
$node->setOption('databaseUsername', 'dbuser');
$node->setOption('databasePassword', 'dbpassword');
$node->setOption('databaseName', 'dbname');
$node->setOption('databaseGzipCompression', true);

$application = new \Greenfieldr\SurfBackups\Domain\Model\Application('typo3');
// Represents backup folder excluding application name and release directory
$application->setBackupBasePath('/path/to/backup/directory');
$application->setBackupSourcePath('/path/to/document-root/');
$application->addNode($node);

/** @var \Greenfieldr\SurfBackups\Domain\Model\Backup $deployment */
$deployment->setWorkflow($workflow);
$deployment->addApplication($application);
$deployment->setBackupWorkspacesPath('.surf');