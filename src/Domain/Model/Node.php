<?php
namespace Greenfieldr\SurfBackups\Domain\Model;


/**
 * A Node
 *
 */
class Node extends \TYPO3\Surf\Domain\Model\Node
{

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

}
