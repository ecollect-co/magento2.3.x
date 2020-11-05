<?php
namespace ecollect\Core\Block\Standard;

/**
 * Block to checkout success page
 *
 * Class Success
 *
 * @package ecollect\Core\Block\Standard
 */
class Success extends \ecollect\Core\Block\AbstractSuccess
{
    /**
     * Set template in constructor method
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('standard/success.phtml');
    }

}