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
 * Author: dyr
 * Date: 2016-08-23
 */

namespace Home\Model;

use Think\Model\RelationModel;

/**
 * Class UserAddressModel
 * @package Home\Model
 */
class PickupModel extends RelationModel
{
    protected $tableName = 'pick_up';
    protected $_validate = array(
        array('pickup_name','require','自提点名称必须！'),
        array('pickup_address','require','自提点地址必须！'),
        array('pickup_phone','require','号码必须！'),
        array('pickup_contact','require','联系人必须！'),
        array('province_id','require','省必须选择！'),
        array('city_id','require','市必须选择！'),
        array('district_id','require','区/镇人必须！'),
    );

    /**
     * 根据省市区获取单个自提点
     * @param $province_id
     * @param $city_id
     * @param $district_id
     * @return mixed
     */
    public function getPickupItemByPCD($province_id,$city_id,$district_id)
    {
        $model = M('');
        $db_prefix = C('DB_PREFIX');
        $pickup_where = array('province_id' => $province_id, 'city_id' => $city_id, 'district_id' => $district_id);
        $pickup_list = $model
            ->field('p.*,r1.name AS province_name,r2.name AS city_name,r3.name AS district_name')
            ->table($db_prefix . 'pick_up p')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r1 ON r1.id = p.province_id')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r2 ON r2.id = p.city_id')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r3 ON r3.id = p.district_id')
            ->where($pickup_where)
            ->find();
        return $pickup_list;
    }
    /**
     * 根据省市区获取多个自提点
     * @param $province_id
     * @param $city_id
     * @param $district_id
     * @return mixed
     */
    public function getPickupListByPCD($province_id,$city_id,$district_id)
    {
        $model = M('');
        $db_prefix = C('DB_PREFIX');
        $pickup_where = array('province_id' => $province_id, 'city_id' => $city_id, 'district_id' => $district_id);
        $pickup_list = $model
            ->field('p.*,r1.name AS province_name,r2.name AS city_name,r3.name AS district_name')
            ->table($db_prefix . 'pick_up p')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r1 ON r1.id = p.province_id')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r2 ON r2.id = p.city_id')
            ->join('LEFT JOIN ' . $db_prefix . 'region AS r3 ON r3.id = p.district_id')
            ->where($pickup_where)
            ->select();
        return $pickup_list;
    }
}