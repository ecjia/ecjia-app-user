<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.user_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" ><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>


<div class="row-fluid">
	<div class="choose_list" >
		<form method="post" action="{url path='user/admin/info'}" name="searchForm" data-id="{$user.user_id}">
			<input type="text" name="keywords" value="{$smarty.get.keywords}" placeholder="请输入ID或会员名称或邮箱"/> 
			<button class="btn" type="submit">{t}查看{/t}</button>
		</form>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<form action="{$form_action}" method="post" name="theForm" id="theForm" data-url='{url path="orders/admin/operate_post" args="order_id={$order.order_id}"}'  data-pjax-url='{url path="orders/admin/info" args="order_id={$order.order_id}"}' data-list-url="{url path='orders/admin/init'}" data-remove-url="{$remove_action}">
			<div id="accordion2" class="foldable-list">
				<div class="accordion-group">
					<div class="accordion-heading">
						<div class="accordion-toggle acc-in" data-toggle="collapse" data-target="#telescopic1">
							<strong>{t}会员信息{/t}</strong>
							<a target="_blank" href='{url path="user/admin/edit" args="id={$user.user_id}"}'>{t}编辑{/t}</a>
						</div>
					</div>
					<div class="accordion-body in collapse" id="telescopic1">
						<table class="table table-oddtd m_b0">
							<tbody class="first-td-no-leftbd">
								<tr>
									<td><div align="right"><strong>{t}会员名称{/t}</strong></div></td>
									<td>{$user.user_name}</td>
									<td><div align="right"><strong>{t}会员邮箱{/t}</strong></div></td>
									<td>{$user.email}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}会员性别{/t}</strong></div></td>
									<td>{if $user.sex eq 1 }男{elseif $user.sex eq 2}女{else}保密{/if}</td>
									<td><div align="right"><strong>{t}出生日期{/t}</strong></div></td>
									<td>{if $user.birthday neq '0000-00-00'}{$user.birthday}{else}{t}1970-01-01 00:00:00{/t}{/if}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}会员等级{/t}</strong></div></td>
									<td>{if !$user.user_rank }非特殊等级{else}{$user.user_rank}{/if}</td>
									<td><div align="right"><strong>{t}信用额度{/t}</strong></div></td>
									<td>{$user.credit_line}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}注册时间{/t}</strong></div></td>
									<td>{$user.reg_time}</td>
									<td><div align="right"><strong>{t}推荐人{/t}</strong></div></td>
									<td>{$user.parent_username}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}QQ{/t}</strong></div></td>
									<td>{$user.qq}</td>
									<td><div align="right"><strong>{t}MSN{/t}</strong></div></td>
									<td>{$user.msn}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}手机{/t}</strong></div></td>
									<td>{$user.mobile_phone}</td>
									<td><div align="right"><strong>{t}家庭电话{/t}</strong></div></td>
									<td>{$user.home_phone}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}办公电话{/t}</strong></div></td>
									<td>{$user.office_phone}</td>
									<td><div align="right"><strong>{t}邮箱验证{/t}</strong></div></td>
									<td class="ecjiafc-f00">{$user.is_validated}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}最后登录时间{/t}</strong></div></td>
									<td>{$user.last_time}</td>
									<td><div align="right"><strong>{t}最后登录IP{/t}</strong></div></td>
									<td>{$user.last_ip}</td>
								</tr>
							</tbody>
						</table>
					</div>	
				</div>
				<div class="accordion-group">
					<div class="accordion-heading">
						<div class="accordion-toggle acc-in" data-toggle="collapse" data-target="#telescopic2">
							<strong>{t}用户资金{/t}</strong>
							<a target="_blank" href='{url path="user/admin_account_log/init" args="user_id={$user.user_id}"}'>{t}编辑{/t}</a>
						</div>
					</div>
					<div class="accordion-body in collapse" id="telescopic2">
						<table class="table table-oddtd m_b0">
							<tbody class="first-td-no-leftbd">
								<tr>
									<td><div align="right"><strong>{t}可用资金{/t}</strong></div></td>
									<td>{$user.formated_user_money}</td>
									<td><div align="right"><strong>{t}冻结资金{/t}</strong></div></td>
									<td>{$user.formated_frozen_money}</td>
								</tr>
								<tr>
									<td><div align="right"><strong>{t}等级积分{/t}</strong></div></td>
									<td>{$user.rank_points}</td>
									<td><div align="right"><strong>{t}消费积分{/t}</strong></div></td>
									<td>{$user.pay_points}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="accordion-group">
					<div class="accordion-heading">
						<div class="accordion-toggle acc-in" data-toggle="collapse" data-target="#telescopic3">
							<strong>{t}收货地址{/t}</strong>
							<a target="_blank" href='{url path="user/admin/address_list" args="id={$user.user_id}"}'>{t}更多{/t}</a>
						</div>
					</div>
					<div class="accordion-body in collapse" id="telescopic3">
						<table class="table table-oddtd  table-striped  m_b0">
							<tbody class="first-td-no-leftbd ">
								<!-- {foreach from=$address_list item=item} -->
								<tr class="{if $item.default_address}info{/if}">
									<td><div align="right"><strong>{$item.consignee} {if $item.default_address}{t}(默认地址){/t}{/if}</strong></div></td>
									<td colspan="3">
										{if $item.tel}{t}电话：{/t}{$item.tel}<br/>{/if}
										{if $item.mobile}{t}手机：{/t}{$item.mobile}<br/>{/if}
										{if $item.zipcode}{t}邮编：{/t}{$item.zipcode}{/if}
									</td>
									<td>
										{$item.province_name}&nbsp;{$item.city_name}&nbsp;{$item.district_name}&nbsp;&nbsp;{$item.address}
									</td>
								</tr>
								<!-- {foreachelse} -->
								<tr>
									<td class="no-records" colspan="4">该用户暂无收货地址！</td>
								</tr>
								<!-- {/foreach} -->
							</tbody>
						</table>
					</div>
				</div>
				<div class="accordion-group">
					<div class="accordion-heading">
						<div class="accordion-toggle acc-in" data-toggle="collapse" data-target="#telescopic4">
							<strong>{t}会员订单{/t}</strong>
							<a target="_blank" href='{url path="orders/admin/init" args="user_id={$user.user_id}"}'>{t}更多{/t}</a>
						</div>
					</div>
					<div class="accordion-body in collapse" id="telescopic4">
						<table class="table table-striped table_vam  m_b0">
							<thead class="ecjiaf-bt">
								<tr >
									<th>订单号</th>
									<th>下单时间</th>
									<th>收货人</th>
									<th>总金额</th>
									<th>订单状态</th>
								</tr>
							</thead>
							<tbody>
							<!-- {foreach from=$order_list item=order} -->
								<tr>
									<td><a target="_blank" href='{url path="orders/admin/info" args="order_id={$order.order_id}"}' title="查看订单">{$order.order_sn}</a></td>
									<td>{$order.add_time}</td>
									<td valign="top" align="left">
										{if $order.consignee}
										<i class="fontello-icon-user ecjiafc-999"></i>：{$order.consignee}<br>
										{/if}
										{if $order.tel}
										<i class="fontello-icon-phone ecjiafc-999"></i>：{$order.tel} <br>
										{/if}
									</td>
									<td valign="top" nowrap="nowrap" align="right">{$order.order_amount}</td>
									<td valign="top" nowrap="nowrap" align="center">{$order.status}</td>
								</tr>
							<!-- {foreachelse} -->
							<tr><td class="no-records" colspan="10">该会员暂无订单信息</td></tr>
							<!-- {/foreach} -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- {/block} -->