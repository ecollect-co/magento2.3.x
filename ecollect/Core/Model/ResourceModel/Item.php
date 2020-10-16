<?php
/**
 * Created by PhpStorm.
 * User: cuent
 * Date: 11/1/2018
 * Time: 20:09
 */

namespace ecollect\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item
    extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('Core_Item','id');
    }
}