<?php
/**
 * Created by PhpStorm.
 * User: cuent
 * Date: 11/1/2018
 * Time: 20:19
 */
namespace ecollect\Core\Model;

use Magento\Framework\Model\AbstractModel;

class Item
    extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected function _construct()
    {
        $this->_init(\ecollect\Core\Model\ResourceModel\Item::class);
    }

}