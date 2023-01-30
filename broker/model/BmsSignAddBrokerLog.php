<?php
namespace Imee\Package\Broker\Model;
use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class BmsSignAddBrokerLog extends \BmsBaseModel
{
    const STATUS_AGREE = 1;// 同意
    use MysqlCollectionTrait;

}