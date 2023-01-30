<?php

namespace Imee\Package\Broker\Model;

use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class XsUserIdcard extends \XsBaseModel
{
    use MysqlCollectionTrait;

    const  STATE_FAIL = 2;// 审核不通过

}