<?php

namespace Imee\Compo\Broker\Model;

use Imee\Compo\Broker\Model\Traits\MysqlCollectionTrait;

class XsUserIdcard extends \XsBaseModel
{
    use MysqlCollectionTrait;

    const  STATE_FAIL = 2;// 审核不通过

}