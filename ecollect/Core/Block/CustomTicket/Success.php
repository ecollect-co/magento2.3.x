<?php
namespace ecollect\Core\Block\CustomTicket;

/**
 * Class Success
 *
 * @package ecollect\Core\Block\CustomTicket
 */

class Success
    extends \ecollect\Core\Block\AbstractSuccess
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('custom_ticket/success.phtml');
    }

}