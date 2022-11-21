<?php
namespace Imee\Compo\Test\Model;

class XsBrokerUser extends \XsBaseModel
{


    public static function has($bid, $uid)
    {
        return self::findFirst(array(
            "bid=:bid: and uid=:uid:",
            "bind" => array("bid" => $bid, "uid" => $uid)
        ));
    }

    public static function hasAny($uid, $isMaster = false)
    {
        $cond = [
            'uid=:uid:',
            'bind' => ['uid' => $uid]
        ];
        if($isMaster) return self::useMaster()->findFirst($cond);
        return self::findFirst($cond);
    }

    public static function hasJoined($uid)
    {
        return self::findFirst(array(
            "uid=:uid: and deleted = 0 and state=1",
            "bind" => array("uid" => $uid)
        ));
    }

    public static function getValueByUid($uid)
    {
        return self::findFirst(array(
            "uid=:uid:",
            "bind" => array("uid" => $uid)
        ));
    }

    public static function insertRows($bid, $uid, $state = 0)
    {
        $has = self::has($bid, $uid);
        if ($has) return true;

        $rec = new XsBrokerUser();
        $rec->bid = $bid;
        $rec->uid = $uid;
        $rec->deleted = 0;
        $rec->dateline = time();
        $rec->state = $state;
        $rec->save();
        return true;
    }

    public static function isPackCal($uid)
    {
        $buser = self::hasAny($uid);

        return $buser && $buser->pack_cal > 0;
    }

    public static function isCorpCal($uid)
    {
        $buser = self::hasAny($uid);

        return $buser && $buser->corp_cal > 0;
    }
}