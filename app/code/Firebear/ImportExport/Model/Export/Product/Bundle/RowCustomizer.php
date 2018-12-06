<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export\Product\Bundle;

use \Magento\Catalog\Model\Product\Type\AbstractType;

class RowCustomizer extends \Magento\BundleImportExport\Model\Export\RowCustomizer
{

    /**
     * Mapping for shipment type
     *
     * @var array
     */
    private $shipmentTypeMapping = [
        AbstractType::SHIPMENT_TOGETHER => 'Together',
        AbstractType::SHIPMENT_SEPARATELY => 'Separately',
    ];

    private $shipmentTypeColumn = 'bundle_shipment_type';

    /**
     * Retrieve bundle type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getTypeValue($type)
    {
        return isset($this->typeMapping[$type]) ? __($this->typeMapping[$type]) : __(self::VALUE_DYNAMIC);
    }

    protected function getPriceViewValue($type)
    {
        return isset($this->priceViewMapping[$type]) ? __($this->priceViewMapping[$type]) : __(self::VALUE_PRICE_RANGE);
    }

    protected function getPriceTypeValue($type)
    {
        return isset($this->priceTypeMapping[$type]) ? __($this->priceTypeMapping[$type]) : null;
    }

    private function getShipmentTypeValue($type)
    {
        return isset($this->shipmentTypeMapping[$type]) ? __($this->shipmentTypeMapping[$type]) : null;
    }
}
