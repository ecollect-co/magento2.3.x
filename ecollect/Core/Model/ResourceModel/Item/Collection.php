<?php
/**
 * Created by PhpStorm.
 * User: cuent
 * Date: 11/1/2018
 * Time: 20:24
 */

namespace ecollect\Core\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use ecollect\Core\Model\Item;
use ecollect\Core\Model\ResourceModel\Item as ItemResource;

class Collection
    extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(Item::class,ItemResource::class );
    }
}