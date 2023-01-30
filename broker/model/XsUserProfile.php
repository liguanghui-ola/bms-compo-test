<?php

namespace Imee\Package\Broker\Model;

use Imee\Package\Broker\Model\Traits\MysqlCollectionTrait;

class XsUserProfile extends \XsBaseModel
{
    use MysqlCollectionTrait;
    protected static $primaryKey = "uid";

}