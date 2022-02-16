<?php
namespace TendoPay\TendopayPayment\Block\Catalog;

use \Magento\Framework\View\Element\Template;

class Catrgory extends Template {

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $parentBlock = $this->getParentBlock();
        $product = $parentBlock ? $parentBlock->getProduct() : null;
        return $product;
    }
}
