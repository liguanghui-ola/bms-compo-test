<?php
namespace Imee\Package\Broker\Model;

use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class XsstRiskMoneyGiftOp extends \ConfigBaseModel
{
    const  STATE_NORMAL = 1; // 正常状态
    const  STATE_EXCEPTION = 2; // 异常状态
    use MysqlCollectionTrait;

}