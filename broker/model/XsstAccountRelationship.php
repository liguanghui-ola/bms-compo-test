<?php

namespace Imee\Compo\Broker\Model;

use Imee\Compo\Broker\Model\Traits\MysqlCollectionTrait;

class XsstAccountRelationship extends \XsstBaseModel
{
    use MysqlCollectionTrait;

    protected static $primaryKey = "uid";


}