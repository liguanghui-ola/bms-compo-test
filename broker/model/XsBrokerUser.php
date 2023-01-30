<?php

namespace Imee\Package\Broker\Model;

use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class XsBrokerUser extends \XsBaseModel
{
    use MysqlCollectionTrait;
    const    STATE_PASS = 1;// 审核通过

}