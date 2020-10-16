<?php
namespace ecollect\Core\Block\Standard;

/**
 * Block to checkout failure page
 *
 * Class Failure
 *
 * @package ecollect\Core\Block\Standard
 */
class Failure
    extends \Magento\Framework\View\Element\Template
{
    /**
     * Set template in constructor method
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('standard/failure.phtml');
    }

    public function getErrorMessage()
    {
        return \ecollect\Core\Model\Api\Exception::GENERIC_API_EXCEPTION_MESSAGE;
    }

    public function getUrlHome()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}