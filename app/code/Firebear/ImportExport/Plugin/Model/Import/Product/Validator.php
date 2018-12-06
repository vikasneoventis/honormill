<?php
/**
 * Copyright © 2017 Firebear Studio GmbH. All rights reserved.
 */

namespace Firebear\ImportExport\Plugin\Model\Import\Product;

use Firebear\ImportExport\Model\Import;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator as BaseValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Registry;

/**
 * Class Validator
 * Rewrite this class to allow import attribute values on the fly.
 *
 * @package Firebear\ImportExport\Plugin\Model\Import\Product
 */
class Validator extends BaseValidator
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AttributeFactory
     */
    protected $prodAttrFac;

    protected $types = ['multiselect', 'select'];

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Validator constructor.
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param AttributeFactory $prodAttrFac
     * @param Registry $registry
     * @param array $validators
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        AttributeFactory $prodAttrFac,
        Registry $registry,
        $validators = []
    ) {
        parent::__construct($string, $validators);
        $this->scopeConfig = $scopeConfig;
        $this->prodAttrFac = $prodAttrFac;
        $this->registry = $registry;
    }

    /**
     * Rewrite method which allow create attributes & values on the fly
     *
     * @param BaseValidator $subject
     * @param callable $proceed
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundIsAttributeValid(
        BaseValidator $subject,
        callable $proceed,
        $attrCode,
        array $attrParams,
        array $rowData
    ) {
        $createValuesAllowed = (bool)$this->scopeConfig->getValue(
            Import::CREATE_ATTRIBUTES_CONF_PATH,
            ScopeInterface::SCOPE_STORE
        );
        if ($createValuesAllowed) {
            if (in_array($attrParams['type'], $this->types)) {
                $attribute = $this->prodAttrFac->create();
                $attribute->load($attrParams['id']);
                $values = explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData[$attrCode]);
                foreach ($values as $value) {
                    if ($createValuesAllowed && $attribute->getIsUserDefined()) {
                        $attrParams['options'][strtolower($value)] = $value;
                    }
                }
                if ($createValuesAllowed) {
                    $newData = [$attrCode => $attrParams['options']];
                    if ($reg = $this->registry->registry('firebear_create_attr')) {
                        if (isset($reg[$attrCode])) {
                            $newData[$attrCode] = array_merge($newData[$attrCode], $reg[$attrCode]);
                        }
                    }

                    $this->registry->unregister('firebear_create_attr');
                    $this->registry->register('firebear_create_attr', $newData);
                }
            }
        }

        return $proceed($attrCode, $attrParams, $rowData);
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function getNewAttributes(array $rowData)
    {
        $array = [];
        foreach ($rowData as $key => $data) {
            if (preg_match('/^(attribute\|).+/', $key)) {
                $columnData = explode('|', $key);
                foreach ($columnData as $field) {
                    $field = explode(':', $field);
                    if ($field[0] == 'attribute_code') {
                        $array[] = $field[1];
                    }
                }
            }
        }

        return $array;
    }
}
