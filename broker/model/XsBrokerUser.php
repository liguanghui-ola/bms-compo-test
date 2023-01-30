<?php

namespace Imee\Compo\Broker\Model;

use Imee\Compo\Broker\Model\Traits\MysqlCollectionTrait;

class XsBrokerUser extends \XsBaseModel
{
    use MysqlCollectionTrait;
    const    STATE_PASS = 1;// 审核通过

}