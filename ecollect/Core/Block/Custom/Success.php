<?php
namespace ecollect\Core\Block\Custom;

/**
 * Class Success
 *
 * @package ecollect\Core\Block\Custom
 */
class Success
    extends \ecollect\Core\Block\AbstractSuccess
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('custom/success.phtml');
    }

}