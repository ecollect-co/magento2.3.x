<?php
namespace ecollect\Core\Block\Standard;

/**
 * Block to checkout failure page
 *
 * Class FailureRedirect
 *
 * @package ecollect\Core\Block\Standard
 */
class FailureRedirect
    extends Failure
{
    /**
     * Set template in constructor method
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('standard/failure_lightbox_redirect.phtml');
    }

}