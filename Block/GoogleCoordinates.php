<?php
namespace DavidDelgado\GoogleCoordinates\Block;

use Magento\Framework\View\Element\Template;

class GoogleCoordinates extends Template
{
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getApiKey()
    {
        return $this->scopeConfig->getValue('google_coordinates/general/apikey');
    }
}
