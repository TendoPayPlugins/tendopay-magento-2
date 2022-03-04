<?php
namespace TendoPay\TendopayPayment\Block\Catalog;

use \Magento\Catalog\Block\Product\View;

class Product extends View {

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return parent::getProduct();
    }
}
