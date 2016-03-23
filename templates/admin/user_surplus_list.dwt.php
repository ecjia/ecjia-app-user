<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.user_surplus.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}" ><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid">
	<form name="searchForm" action="{$form_action}" method="post">
		<div class="choose_list span12">
			<input class="date f_l w230" name="start_date" type="text" value="{$smarty.get.start_date}" placeholder="开始时间">
			<span class="f_l">至</span>
			<input class="date f_l w230" name="end_date" type="text" value="{$smarty.get.end_date}" placeholder="结束时间">
			<button class="btn select-button" type="button">{t}筛选{/t}</button>
		</div>
		<div class="top_right f_r m_t_30">
			<input type="text" name="keywords" placeholder="请输入会员名称或订单号" value="{$order_list.filter.keywords}"/>
			<button class="btn m_l5" type="submit">{$lang.button_search}</button>
		</div>
	</form>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<table class="table table-striped smpl_tbl">
				<thead>
					<tr>
						<th>{$lang.username}</th>
						<th>{$lang.order_sn}</th>
						<th>{$lang.surplus}</th>
						<th>{$lang.integral_money}</th>
						<th>{$lang.add_time}</th>
						<th>{$lang.handler}</th>
					</tr>
				</thead>
				<tbody>
					<!-- {foreach from=$order_list.order_list item=order} -->
					<tr align="center">
						<td class="first-cell">{if $order.user_name}{$order.user_name}{else}{t}匿名会员{/t}{/if}</td>
						<td>{$order.order_sn}</td>
						<td>{$order.surplus}</td>
						<td>{$order.integral_money}</td>
						<td align="center">{$order.add_time}</td>
						<td align="center">
							<a target="_blank" href='{url path="orders/admin/info" args="order_id={$order.order_id}"}' title="{$lang.view_order}" >{$lang.view}</a>
						</td>
					</tr>
					<!-- {foreachelse} -->
					<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
					<!-- {/foreach} -->
				</tbody>
			</table>
			<!-- {$order_list.page} -->
		</div>
	</div>
</div>
<!-- {/block} -->