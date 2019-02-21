<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * ECJIA 管理中心帐户变动记录语言文件
 */
return array(
    /* 菜单 */
    'add_account'        => '调节会员帐户',
    'account_list'       => '会员帐户变动明细',

    /* 列表页 */
    'user_not_exist'     => '该用户不存在',
    'all_account'        => '所有帐户',
    'label_user_name'    => '当前会员：',
    'label_user_money'   => '可用资金帐户：',
    'label_frozen_money' => '冻结资金帐户：',
    'label_rank_points'  => '成长值帐户：',
    'label_pay_points'   => '消费积分帐户：',
    'label_change_desc'  => '帐户变动原因：',

    'change_time'  => '帐户变动时间',
    'change_desc'  => '帐户变动原因',
    'user_money'   => '可用资金帐户',
    'frozen_money' => '冻结资金帐户',
    'rank_points'  => '成长值帐户',
    'pay_points'   => '消费积分帐户',

    'add'                   => '增加',
    'subtract'              => '减少',
    'current_value'         => '当前值：',
    'no_account_change'     => '没有帐户变动',
    'log_account_change_ok' => '记录帐户变动成功',

    'js_languages' => array(
        'no_change_desc'          => '请输入帐户变动原因',
        'user_money_not_number'   => '可用资金不是数值',
        'frozen_money_not_number' => '冻结资金不是数值',
        'rank_points_not_int'     => '成长值不是整数',
        'pay_points_not_int'      => '消费积分不是整数'
    ),

    'user_list'           => '会员列表',
    'account_change_desc' => '会员账户变动明细',
    'user_money_error'    => '可用资金账户填写有误',
    'frozen_money_error'  => '冻结资金账户填写有误',
    'rank_points_error'   => '成长值账户填写有误',
    'pay_points_error'    => '消费积分账户填写有误',
    'null_msg'            => '没有修改任何记录',

    'usermoney'   => '可用资金',
    'frozenmoney' => '，冻结资金',
    'rankpoints'  => '，成长值',
    'paypoints'   => '，消费积分',
    'change_desc' => '帐户变动原因是：',

    'account_desc'     => '会员账户信息',
    'filter'           => '筛选',
    'list_change_desc' => '账号变动原因',
);

// end