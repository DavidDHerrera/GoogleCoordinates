<?php
namespace DavidDelgado\GoogleCoordinates\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session as CheckoutSession;

class CoordinatesMSI extends Template
{
    protected $resource;
    protected $connection;
    protected $checkoutSession;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function getInventoryData($sku)
    {
        $query = "SELECT isi.*, cpe.*, source.latitude, source.longitude
                  FROM inventory_source_item AS isi
                  JOIN catalog_product_entity AS cpe ON isi.sku = cpe.sku
                  JOIN inventory_source AS source ON isi.source_code = source.source_code
                  WHERE cpe.sku = :sku";

        $bind = ['sku' => $sku];
        $result = $this->connection->fetchAll($query, $bind);

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'sku' => $row['sku'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
            ];
        }

        return $data;
    }

    public function getInventoryDataFromCheckout()
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();

        $data = [];
        foreach ($items as $item) {
            $sku = $item->getSku(); // Obtiene el SKU del producto
            $inventoryData = $this->getInventoryData($sku);

            if (!empty($inventoryData)) {
                $data = array_merge($data, $inventoryData);
            }
        }

        return $data;
    }


    public function getCoordinatesData()
    {
        return $this->getInventoryDataFromCheckout();
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
}
