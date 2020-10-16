<?php
namespace ecollect\Core\Controller\Standard;

/**
 * Class Failure
 *
 * @package ecollect\Core\Controller\Standard
 */
class FailureRedirect
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {

        $this->_scopeConfig = $scopeConfig;

        parent::__construct(
            $context
        );

    }

    /**
     * Execute Failure action
     */
    public function execute()
    {
        if (!$this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_USE_SUCCESSPAGE_MP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
            $this->_view->loadLayout(['default', 'checkout_onepage_failure']);
        } else{
            $this->_view->loadLayout(['default', 'ecollect_standard_failure_lightbox_redirect']);
        }
        $this->_view->renderLayout();
    }

}