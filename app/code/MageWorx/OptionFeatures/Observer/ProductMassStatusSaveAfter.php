<?php
namespace MageWorx\OptionFeatures\Observer;
use Magento\Framework\Event\ObserverInterface;
class ProductMassStatusSaveAfter implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productIds = $observer->getProductids();
        if (count($productIds) > 0) {
        	foreach ($productIds as $val) {
        		$pidArray = array($val);
	        	$_product = $_objectManager->get('\Magento\Catalog\Model\ProductRepository')->getById($val);
	        	$action = $_objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Action');
		        $customOptions = $_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($_product);
		        $fast_ship = 5;
		        $custom_order = 7;
		        foreach ($customOptions as $customOption) {
		            $values = $customOption->getValues();
		            foreach ($values as $value) {
		                if($value->getData('is_stocktab')) {
		                    $fast_ship = 4;
		                }
		                if($value->getData('is_customtab')) {
		                    $custom_order = 6;
		                }
		                if($fast_ship==4 && $custom_order==6) {
		                    break;
		                }
		            }
		        }
		        // $_product->setFastShip($fast_ship);
		        // $_product->setCustomOrder($custom_order);
		        // $_product->save();
		        $action->updateAttributes([$_product->getId()], ['fast_ship' => strval($fast_ship)], 0);
		        $action->updateAttributes([$_product->getId()], ['custom_order' => strval($custom_order)], 0);
	        }	
        }

        return $this;
    }   
}