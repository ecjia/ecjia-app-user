<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 *  获取指定用户的收藏商品列表
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 *
 * @return  array   $arr
 */
function EM_get_collection_goods($user_id, $num = 10, $start = 1, $rec_id = 0) {
    $where = array('c.user_id'=>$user_id);
    if ($rec_id) {
    	$where = array_merge($where,array('c.rec_id'=>array('lt'=>$rec_id)));
    } 
    
	$dbview = RC_Model::model('user/collect_goods_viewmodel');
	$res = $dbview->join(array('goods','member_price'))->where($where)->order(array('c.rec_id' => 'desc'))->limit(($start - 1) * $num , $num)->select();

    $goods_list = array();

    if (!empty($res)) {
        foreach ($res as $row) {
            if ($row['promote_price'] > 0) {
                RC_Loader::load_app_func('goods','goods');
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }
        
            $goods_list[$row['goods_id']]['rec_id']        = $row['rec_id'];
            $goods_list[$row['goods_id']]['is_attention']  = $row['is_attention'];
            $goods_list[$row['goods_id']]['goods_id']      = $row['goods_id'];
            $goods_list[$row['goods_id']]['goods_name']    = $row['goods_name'];
            $goods_list[$row['goods_id']]['market_price']  = $row['market_price'] > 0 ? price_format($row['market_price']) : '';
            $goods_list[$row['goods_id']]['shop_price']    = $row['shop_price'] > 0 ? price_format($row['shop_price']) : __('免费');
            $goods_list[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
            $goods_list[$row['goods_id']]['url']           = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
            $goods_list[$row['goods_id']]['original_img']  = $row['original_img'];
            $goods_list[$row['goods_id']]['goods_thumb']   = $row['goods_thumb'];
            $goods_list[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
            $goods_list[$row['goods_id']]['goods_type']    = $row['goods_type'];
            $goods_list[$row['goods_id']]['goods_img']     = $row['goods_img'];
            $goods_list[$row['goods_id']]['click_count']   = $row['click_count'];
            
            $goods_list[$row['goods_id']]['unformatted_shop_price'] = $row['shop_price'];
            $goods_list[$row['goods_id']]['unformatted_promote_price'] = $promote_price;
        }
    }
    return $goods_list;
}


// end