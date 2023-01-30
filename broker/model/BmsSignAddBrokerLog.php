<?php
namespace Imee\Compo\Broker\Model;
use Imee\Compo\Broker\Model\Traits\MysqlCollectionTrait;

class BmsSignAddBrokerLog extends \BmsBaseModel
{
    const STATUS_AGREE = 1;// 同意
    use MysqlCollectionTrait;

}