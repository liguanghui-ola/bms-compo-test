<?php

namespace Imee\Package\Broker\Service\Banban\JoinRule;


use Imee\Package\Broker\Model\XsBrokerUser;
use Imee\Package\Broker\Model\XssChatroomPackageUser;
use Imee\Package\Broker\Model\XsstAccountRelationship;
use Imee\Package\Broker\Model\XsstChannelReception;
use Imee\Package\Broker\Model\XsUserPopularity;
use Imee\Package\Broker\Model\XsUserProfile;
use Imee\Package\Broker\Service\RuleInterface;
use Imee\Service\Helper;

/**
 * 伴伴入会需对应app无gs账号
 */
class HasGs implements RuleInterface
{

    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $userInfo = XsUserProfile::findOne($uid);
        if (empty($userInfo)) {
            return [false, "用户信息异常"];
        }
        $appId = $userInfo["app_id"];
        if ($appId == 1 && $this->_isSmallAccount($uid, $appId, 2)) {
            return [false, '不支持加入公会'];
        }
        return [true, ""];
    }

    //通过大小号表判断
    private function _isSmallAccount($uid, $appId, $notAppId)
    {
        $bigAccount = XsstAccountRelationship::findOne($uid);
        if (!$bigAccount) return false;
        $gid1 = $bigAccount['gid1'];
        $allAccount = XsstAccountRelationship::getListByWhere([
            ["gid1", "=", $gid1],
            ["uid", "!=", $uid]
        ], "uid");
        if (empty($allAccount)) return false;
        $users = array_values(array_column($allAccount, 'uid'));
        foreach (array_chunk($users, 200) as $userArr) {
            if (empty($userArr)) continue;
            $where = [
                ["uid", "in", $userArr]
            ];
            $rows = XsUserProfile::getListByWhere($where, "uid,app_id");
            $r = $this->_checkRowsIsSmall($rows, $appId, $notAppId);
            if ($r) return true;
        }

        return false;
    }

    private function _checkRowsIsSmall($rows, $appId, $notAppId)
    {
        if (empty($rows)) return false;
        $uids = [];
        foreach ($rows as $row) {
            if ($row['app_id'] == $appId) continue;
            if ($notAppId > 0 && $row['app_id'] != $notAppId) continue;
            if (in_array($row['uid'], $uids)) continue;
            if ($this->_checkIsGs($row['uid'])) return true;
            $uids[] = $row['uid'];
        }
        return false;
    }

    private function _checkIsGs($uid)
    {
        if (!$uid) return false;
        // 先确认是gs
        $hasGs = false;
        // 接待人员管理表
        $isReception = XsstChannelReception::findOneByWhere([
            ["uid", "=", $uid], ["deletetype", "=", 0]
        ]);
        if (!empty($isReception)) $hasGs = true;

        if (!$hasGs) {
            $hasJoinBroker = XsBrokerUser::findOneByWhere([
                ["uid", "=", $uid], ["deleted", "=", 0],
                ["state", "=", XsBrokerUser::STATE_PASS]
            ]);
            if (!empty($hasJoinBroker)) $hasGs = true;
        }
        // 不是gs返回
        if (!$hasGs) return false;

        // (无用户信息/被封禁) = 不符合
//        $uinfo = Helper::fetchOne("select u.uid, u.deleted, p.popularity from xs_user_profile as u left join xs_user_popularity as p on (u.uid = p.uid) where u.uid = {$uid}", null, \XsBaseModel::SCHEMA_READ);
        //if (empty($uinfo) || $uinfo['deleted'] > 2) return true;
        //去掉封禁注销逻辑
        $uInfo = XsUserPopularity::findOneByWhere([["uid", "=", $uid]]);
        if (empty($uInfo)) return true;
        $bftime = strtotime('-61 day');
        $bfnum = 2500 * 100;
        if ($uInfo['popularity'] < 10000 * 100) {
            $bftime = strtotime('-8 day');
            $bfnum = 1;
        } else if ($uInfo['popularity'] < 100000 * 100) {
            $bftime = strtotime('-31 day');
            $bfnum = 500 * 100;
        }
        // 规定期限内收入大于阈值 = 不符合
        #$incoms = max(0, intval(Helper::fetchColumn("select sum(income) as smoney from xss_chatroom_package_user where uid = {$uid} and day > {$bftime}", 'xssdb')));
        $condition = [
            ["uid", "=", $uid],
            ["day", ">", $bftime],
        ];
        $sumArr = XssChatroomPackageUser::getListByWhere($condition, " sum(income) as smoney");
        if (!empty($sumArr) && $sumArr[0]["smoney"] >= $bfnum) {
            return true;
        }
        return false;
    }

}