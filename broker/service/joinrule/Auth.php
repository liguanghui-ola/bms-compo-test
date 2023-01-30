<?php

namespace Imee\Compo\Broker\Service\JoinRule;

use Imee\Compo\Broker\Model\XsUserIdcard;
use Imee\Compo\Broker\Model\XsUserMobile;
use Imee\Compo\Broker\Model\XsUserProfile;
use Imee\Compo\Broker\Model\XsUserSafeMobile;
use Imee\Compo\Broker\Model\XsUserSettings;
use Imee\Compo\Broker\Service\RuleInterface;


/**
 * 认证大神才能加入:身份证 ，手机号
 */
class Auth implements RuleInterface
{
    const  MSG = "认证大神才能加入公会，请去APP-我的-认证中心-申请大神 进行认证";

    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $settings = XsUserSettings::findOne([
            ["uid", "=", $uid],
            ["agreement_version", ">=", 1],
        ]);
        if (empty($settings)) {
            return [false, self::MSG];
        }
        $hasBindMobile = XsUserMobile::findOneByWhere([["uid", "=", $uid]]);
        if (empty($hasBindMobile)) {
            return [false, self::MSG];
        }
        $idCard = $this->idCard($uid);
        if (empty($idCard)) {
            return [false, self::MSG];
        }
        return [true, ""];
    }

    //
    public function idCard($uid)
    {
        $appId = 0;
        $userInfo = XsUserProfile::findOne($uid);
        if (!empty($userInfo)) {
            $appId = $userInfo["app_id"];
        }
        $where = [
            ["uid", "=", $uid],
            ["state", ">", XsUserIdcard::STATE_FAIL],
        ];
        $idCard = XsUserIdcard::findOneByWhere($where);
        if (empty($idCard)) {
            $safeMobile = XsUserSafeMobile::findOneByWhere([["uid", "=", $uid]]);
            if (!empty($safeMobile)) {
                $mobileList = XsUserSafeMobile::findOneByWhere([
                    ["app_id", "=", $appId],
                    ["mobile", "=", $safeMobile["mobile"]]
                ]);
                $uids = array_column($mobileList, "uid");
                if (!empty($uids)) {
                    $idCard = XsUserIdcard::findOneByWhere([
                        ["uid", "in", $uids],
                        ["state", ">", XsUserIdcard::STATE_FAIL],
                    ]);
                }
            }
        }
        return $idCard;
    }


}