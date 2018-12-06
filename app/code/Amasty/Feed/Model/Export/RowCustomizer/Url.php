<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Config\Source\Path;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Url implements RowCustomizerInterface
{
    protected $_storeManager;

    protected $_urlRewrites;

    protected $_url;

    protected $_storeId;

    protected $_rowCategories;

    protected $_export;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Feed\Model\Export\Product $export,
        \Magento\Framework\Url $url, //always get frontend url
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_url = $url;
        $this->_export = $export;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(\Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE)) {
            $this->_storeId = $collection->getStoreId();
            $select = $collection->getConnection()
                ->select()
                ->from(['u' => $collection->getTable('url_rewrite')], ['u.entity_id', 'u.request_path', 'u.metadata'])
                ->where('u.store_id = ?', $this->_storeId)
                ->where('u.is_autogenerated = 1')
                ->where('u.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->where('u.entity_id IN(?)', $productIds);

            foreach ($collection->getConnection()->fetchAll($select) as $row) {

                $metadata = $this->serializer->unserialize($row['metadata']);

                $categoryId = is_array($metadata) && isset($metadata['category_id']) ?
                    $metadata['category_id'] : null;

                if (!isset($row['entity_id'])) {
                    $this->_urlRewrites[$row['entity_id']] = [];
                }

                $this->_urlRewrites[$row['entity_id']][intval($categoryId)] = $row['request_path'];
            }

            $multiRowData = $this->_export->getMultiRowData();
            $this->_rowCategories = $multiRowData['rowCategories'];
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        if ($this->_urlRewrites && isset($this->_urlRewrites[$productId])) {
            $urlRewrites = $this->_urlRewrites[$productId];

            $pathMode = $this->scopeConfig->getValue(
                'amasty_feed/general/category_path',
                ScopeInterface::SCOPE_STORE
            );

            if (count($urlRewrites) > 1 && $pathMode != Path::USE_DEFAULT) {
                $categoryRewrites = array_slice($urlRewrites, 1);

                if ($pathMode == Path::USE_SHORTEST) {
                    uasort(
                        $categoryRewrites,
                        function ($a, $b) {
                            return strlen($a) > strlen($b) ? 1 : -1;
                        }
                    );
                } else {
                    uasort(
                        $categoryRewrites,
                        function ($a, $b) {
                            return strlen($a) < strlen($b) ? 1 : -1;
                        }
                    );
                }

                $urlWithCategory = reset($categoryRewrites);
            } else {
                $categories = isset($this->_rowCategories[$productId]) ? $this->_rowCategories[$productId] : [];
                $lastCategoryId = count($categories) > 0 ? end($categories) : null;
                $urlWithCategory =
                    isset($urlRewrites[$lastCategoryId]) ? $urlRewrites[$lastCategoryId] : end($urlRewrites);
            }

            $routeParamsShort =
                [
                    '_direct' => isset($urlRewrites[0]) ? $urlRewrites[0] : end($urlRewrites),
                    '_nosid' => true,
                    '_query' => array_merge($this->_export->getUtmParams(), ['___store' => null]),
                    '_scope_to_url' => true, //as in  \Magento\Store\Model\Store::getUrl()
                ];

            $routeParamsWithCategory =
                [
                    '_direct' => $urlWithCategory,
                    '_nosid' => true,
                    '_query' => array_merge($this->_export->getUtmParams(), ['___store' => null]),
                    '_scope_to_url' => true, //as in  \Magento\Store\Model\Store::getUrl()
                ];

            //if the production mode + config cache is enabled. Store manager returns current website url, instead of one have been set.
            //taking url directly from URL model.
            $this->_url->setScope($this->_storeManager->getStore($this->_storeId));
            $customData[\Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE] = [
                'short' => $this->_url->getUrl('', $routeParamsShort),
                'with_category' => $this->_url->getUrl('', $routeParamsWithCategory),
            ];
        } elseif ($product = $this->productRepository->getbyId($productId)) {
            $categories = $product->getCategoryIds();
            $lastCategoryId = count($categories) > 0 ? end($categories) : null;
            $routeParamsShort = [
                '_nosid' => true,
                '_query' => array_merge($this->_export->getUtmParams(), ['___store' => null]),
                '_scope_to_url' => true,
                'id' => $product->getId(),
                's' => $product->getUrlKey()
            ];

            $routeParamsWithCategory = array_merge($routeParamsShort, ['category' => $lastCategoryId]);

            $customData[\Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE] = [
                'short' => $this->_url->getUrl('catalog/product/view', $routeParamsShort),
                'with_category' => $this->_url->getUrl('catalog/product/view', $routeParamsWithCategory)
            ];
        } else {
            $customData[\Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE] = [
                'short' => null,
                'with_category' => null
            ];
        }

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
