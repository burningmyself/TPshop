<?php

/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: 当燃
 * Date: 2015-09-09
 */
 

namespace Admin\Logic;

use Think\Model\RelationModel;

class UserLogic extends RelationModel
{    
    
    /**
     * 获取指定用户信息
     * @param $uid int 用户UID
     * @param bool $relation 是否关联查询
     *
     * @return mixed 找到返回数组
     */
    public function detail($uid, $relation = true)
    {
        $user = D('User')->where(array('user_id' => $uid))->relation($relation)->find();
        return $user;
    }
    
    /**
     * 改变用户信息
     * @param int $uid
     * @param array $data
     * @return array
     */
    public function update($uid = 0, $data = array())
    {
        $db_res = D('User')->where(array("user_id" => $uid))->data($data)->save();
        if ($db_res) {
            return array(1, "用户信息修改成功");
        } else {
            return array(0, "用户信息修改失败");
        }
    }
    
    
    /**
     * 添加用户
     * @param $user
     * @return array
     */
    public function addUser($user)
    {
        if ($new_user_id = D('User')->add($user)) {
    
            $role = array(
                'role_id' => $user['user_level'],
                'user_id' => $new_user_id
            );
            if (D('Role_users')->add($role)) {
                return array(1, '添加成功！', U('Admin/Access/index'));
            } else {
                return array(0, '添加用户权限失败！', U('Admin/Access/index'));
            }
        } else {
            return array(0, '添加用户失败！', U('Admin/Access/index'));
        }
    }
    
    /**
     * 改变用户密码
     * @param $uid
     * @param $oldPassword
     * @param $newPassword
     * @return string
     */
    public function changePassword($uid, $oldPassword, $newPassword)
    {
    
        $user = $this->detail($uid);
        if ($user['user_pass'] != encrypt($oldPassword)) {
            return array(0, "原用户密码不正确");
        }
        $data['user_id'] = $uid;
        $data['user_pass'] = encrypt($newPassword);
    
        if (D('User')->where(array("user_id" => $uid))->data($data)->save()) {
            return array(1, "密码修改成功", U("Admin/login/logout"));
        } else {
            return array(0, "密码修改失败");
        }
    
    }
    
    
    /**
     * 生成新的Hash
     * @param $authInfo
     * @return string
     */
    public function genHash(&$authInfo)
    {
        $User = D('User', 'Logic');    
        $condition['user_id'] = $authInfo['user_id'];
        $session_code = encrypt($authInfo['user_id'] . $authInfo['user_pass'] . time());
        $User->where($condition)->setField('user_session', $session_code);
    
        return $session_code;
    }
    
    public function getAuth($role_id)
    {
    	return $role_id;
    }
}