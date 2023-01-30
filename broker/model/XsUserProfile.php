<?php

namespace Imee\Compo\Broker\Model;

use Imee\Compo\Broker\Model\Traits\MysqlCollectionTrait;

class XsUserProfile extends \XsBaseModel
{
    use MysqlCollectionTrait;
    protected static $primaryKey = "uid";

}