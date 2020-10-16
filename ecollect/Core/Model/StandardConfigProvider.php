<?php

namespace ecollect\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Return configs to Standard Method
 *
 * Class StandardConfigProvider
 *
 * @package ecollect\Core\Model
 */
class StandardConfigProvider
    implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstance;

    /**
     * @var string
     */
    protected $methodCode = Standard\Payment::CODE;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    protected $_scopeConfig;

    protected $_coreHelper;
    protected $_productMetaData;

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ecollect\Core\Helper\Data $coreHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_assetRepo = $assetRepo;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreHelper = $coreHelper;
        $this->_productMetaData = $productMetadata;
    }

    /**
     * Return standard configs
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        //var_dump($this->methodInstance->isAvailable());
        //echo "standar.php:";
        if ($this->methodInstance->isAvailable()) {
            //$this->methodInstance->isAvailable = true;
            $config = [
                'payment' => [
                    $this->methodCode => [
                        'estado'        => $this->methodInstance->isAvailable(),
                        'actionUrl'        => $this->methodInstance->getActionUrl(),
                        //'bannerUrl'        => $this->methodInstance->getConfigData('banner_checkout'),
                        'type_checkout'    => $this->methodInstance->getConfigData('type_checkout'),
                        'logoUrl'          => $this->getImageUrl('logo.png'),
                        'analytics_key'    => $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        'platform_version' => $this->_productMetaData->getVersion(),
                        'module_version'   => $this->_coreHelper->getModuleVersion()

                    ],
                ],
            ];
            if ($this->methodInstance->getConfigData('type_checkout') == 'iframe') {
                $config['payment'][$this->methodCode]['iframe_height'] = $this->methodInstance->getConfigData('iframe_height');
            }
        }

        return $config;
    }

    /**
     * Return image url
     *
     * @return string|null
     */
    public function getImageUrl($imageName)
    {
        $url = $this->_assetRepo->getUrl(
            "ecollect_Core::images/" . $imageName
        );

        return $url;
    }
}