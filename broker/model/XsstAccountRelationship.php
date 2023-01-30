<?php

namespace Imee\Package\Broker\Model;

use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class XsstAccountRelationship extends \XsstBaseModel
{
    use MysqlCollectionTrait;

    protected static $primaryKey = "uid";


}