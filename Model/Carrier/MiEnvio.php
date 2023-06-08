<?php
namespace DavidDelgado\GoogleCoordinates\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class MiEnvio extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'simpleshipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    private StoreManagerInterface $storeManager;

    /**
     * Shipping constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    public function getConfigValue($field, $storeId = null)
    {
        if (!isset($storeId) || $storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            'carriers/simpleshipping/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    private function getShippingPrice($weight, $distance)
    {
        $configRangeDistance1 = $this->getConfigValue('distance_range_1');
        $configPriceDistance1 = $this->getConfigValue('price_distance_range_1');
        $configRangeDistance2 = $this->getConfigValue('distance_range_2');
        $configPriceDistance2 = $this->getConfigValue('price_distance_range_2');
        $configRangeDistance3 = $this->getConfigValue('distance_range_3');
        $configPriceDistance3 = $this->getConfigValue('price_distance_range_3');
        $configRangeWeight1 = $this->getConfigValue('weight_range_1');
        $configPriceWeight1 = $this->getConfigValue('price_weight_range_1');
        $configRangeWeight2 = $this->getConfigValue('weight_range_2');
        $configPriceWeight2 = $this->getConfigValue('price_weight_range_2');
        $configRangeWeight3 = $this->getConfigValue('weight_range_3');
        $configPriceWeight3 = $this->getConfigValue('price_weight_range_3');
        

        $rangeDistanceParts1 = explode('-', $configRangeDistance1);
        $minDistance1 = isset($rangeDistanceParts1[0]) ? intval($rangeDistanceParts1[0]) : 0;
        $maxDistance1 = isset($rangeDistanceParts1[1]) ? intval($rangeDistanceParts1[1]) : 0;

        $rangeDistanceParts2 = explode('-', $configRangeDistance2);
        $minDistance2 = isset($rangeDistanceParts2[0]) ? intval($rangeDistanceParts2[0]) : 0;
        $maxDistance2 = isset($rangeDistanceParts2[1]) ? intval($rangeDistanceParts2[1]) : 0;

        $rangeDistanceParts3 = explode('-', $configRangeDistance3);
        $minDistance3 = isset($rangeDistanceParts3[0]) ? intval($rangeDistanceParts3[0]) : 0;
        $maxDistance3 = isset($rangeDistanceParts3[1]) ? intval($rangeDistanceParts3[1]) : null;

        $rangeWeightParts1 = explode('-', $configRangeWeight1);
        $minWeight1 = isset($rangeWeightParts1[0]) ? intval($rangeWeightParts1[0]) : 0;
        $maxWeight1 = isset($rangeWeightParts1[1]) ? intval($rangeWeightParts1[1]) : 0;

        $rangeWeightParts2 = explode('-', $configRangeWeight2);
        $minWeight2 = isset($rangeWeightParts2[0]) ? intval($rangeWeightParts2[0]) : 0;
        $maxWeight2 = isset($rangeWeightParts2[1]) ? intval($rangeWeightParts2[1]) : 0;

        $rangeWeightParts3 = explode('-', $configRangeWeight3);
        $minWeight3 = isset($rangeWeightParts3[0]) ? intval($rangeWeightParts3[0]) : 0;
        $maxWeight3 = isset($rangeWeightParts3[1]) ? intval($rangeWeightParts3[1]) : null;

        $configPriceDistance1 = floatval($configPriceDistance1);
        $configPriceDistance2 = floatval($configPriceDistance2);
        $configPriceDistance3 = floatval($configPriceDistance3);
        $configPriceWeight1 = floatval($configPriceWeight1);
        $configPriceWeight2 = floatval($configPriceWeight2);
        $configPriceWeight3 = floatval($configPriceWeight3);

        $price = 0;

        // Verificar el peso y asignar el precio correspondiente
        if ($weight >= $minWeight1 && $weight <= $maxWeight1) {
            $price += $configPriceWeight1;
        } elseif ($weight >= $minWeight2 && $weight <= $maxWeight2) {
            $price += $configPriceWeight2;
        } elseif ($weight >= $minWeight3 && ($maxWeight3 === null || $weight <= $maxWeight3)) {
            $price += $configPriceWeight3;
        }

        // Verificar la distancia y asignar el precio correspondiente
        if ($distance >= $minDistance1 && $distance <= $maxDistance1) {
            $price += $configPriceDistance1;
        } elseif ($distance >= $minDistance2 && $distance <= $maxDistance2) {
            $price += $configPriceDistance2;
        } elseif ($distance >= $minDistance3 && ($maxDistance3 === null || $distance <= $maxDistance3)) {
            $price += $configPriceDistance3;
        }

        return $price;
    }


    /**
     * @param string $userData
     */
    public function setUserData($userData)
    {
        $this->session->setUserData($userData);
    }

    /**
     * @return string|null
     */
    public function getUserData()
    {
        return $this->session->getUserData();
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        $weight = 0;
        $items = $request->getAllItems();
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty());
        }

        $userData = $this->getUserData();
        $distance = floatval($userData['distance'][0]);

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getShippingPrice($weight, $distance);

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}