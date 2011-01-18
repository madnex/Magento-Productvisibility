<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mage
 * @package   Mage_Catalog
 * @copyright 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_Productvisibility Producttrigger
 * 
 * @category  Catalog
 * @package   Netresearch_Productvisibility
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_Productvisibility_Helper_Product extends Mage_Core_Helper_Abstract
{
    /** @var Mage_Catalog_Model_Product */
    protected $_product  = null;
    
    /** @var array */
    protected $_websites = array();
    
    /**
     * if product is enabled in any website
     * 
     * @param Mage_Catalog_Model_Product $product Product to get websites of
     * 
     * @return boolean
     */
    public function getWebsites($product)
    {
        $this->initProduct($product);
        return $this->_websites;
    }
    
    /**
     * get websites where the product is enabled in
     * 
     * @param Mage_Catalog_Model_Product $product Product to get websites of
     * 
     * @return array (int website_id => string website_name)
     */
    public function initProduct($product)
    {
        if (is_null($this->_product) or $this->_product != $product) {
            $this->_product = $product;
            if ($websiteIds = $product->getWebsiteIds()) {
                foreach ($websiteIds as $websiteId) {
                    $website = Mage::app()->getWebsite($websiteId);
                    $this->_websites[$websiteId] = $website->getName();
                }
            }
        }
    }
    
    /**
     * check if product is up-to-date in price index
     * 
     * @param Mage_Catalog_Model_Product $product Product to get websites of
     * 
     * @return boolean
     */
    public function isUpToDateInPriceIndex($product)
    {
        /**
         * @var Zend_Db_Adapter_Pdo_Abstract
         */
        $connection = Mage::getModel('core/resource')
            ->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
        $query = 'select * from catalog_product_index_price'
            . ' where customer_group_id=0 and website_id=1'
            . ' and entity_id = ' . $product->getId();
        $result = $connection->fetchRow($query);
        if (false == $result) {
            return false;
        }
        if ($product->getTaxClassId() != $result['tax_class_id']) {
            return false;
        }
        if ($product->getPrice() != $result['price']) {
            return false;
        }
        if ($product->getFinalPrice() != $result['final_price']) {
            return false;
        }
        if (!is_null($product->getMinimalPrice())
            and $product->getMinimalPrice() != $result['min_price']
        ) {
            return false;
        }
        if ($product->getTierPrice() != $result['tier_price']) {
            return false;
        }
        return true;
    }
    
    /**
     * check if product is up-to-date in price index
     * 
     * @param Mage_Catalog_Model_Product $product Product to get websites of
     * 
     * @return boolean
     */
    public function isUpToDateInStockIndex($product)
    {
        /**
         * @var Zend_Db_Adapter_Pdo_Abstract
         */
        $connection = Mage::getModel('core/resource')
            ->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
        $query = 'select * from cataloginventory_stock_status_idx'
            . ' where website_id=1 and stock_id=1'
            . ' and product_id = ' . $product->getId();
        $result = $connection->fetchRow($query);
        if (false == $result) {
            return false;
        }
        if ($product->getStockItem()->getQty() != $result['qty']) {
            return false;
        }
        if ($product->isInStock() != $result['stock_status']) {
            return false;
        }
        return true;
    }
}