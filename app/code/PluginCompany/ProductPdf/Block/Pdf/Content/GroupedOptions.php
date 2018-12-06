<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ProductPdf\Block\Pdf\Content;

use Magento\Catalog\Model\Product;
use PluginCompany\ProductPdf\Block\Pdf\Content;

class GroupedOptions extends Content
{
    protected $_template = 'PluginCompany_ProductPdf::pdf/content/options/grouped.phtml';

    public function getAssociatedProducts()
    {
        return $this->getProduct()->getTypeInstance(true)
            ->getAssociatedProducts($this->getProduct());
    }

    public function getFullImageUrl(Product $item)
    {
        $item->getResource()->load($item, $item->getEntityId());
        return $this->getFullProductImageUrl($item->getImage());
    }

    public function getFormattedItemPrice($item)
    {
        return $this->formatCurrency(
            $this->getItemPrice($item)
        );
    }

    private function getItemPrice($item)
    {
        return $item->getPrice();
    }

}

