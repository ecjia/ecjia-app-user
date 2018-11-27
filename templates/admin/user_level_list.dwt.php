<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	var data = '{$data}';
	var stats = '{$stats}';
	ecjia.admin.user_level.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->

<div class="alert alert-info">
	<a class="close" data-dismiss="alert">×</a>
	<strong>{lang key='orders::statistic.tips'}</strong>统计会员排名前30的订单总数以及下单总金额对比。
</div>

<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
	</h3>
</div>

<div class="row-fluid row-fluid-stats">
	<div class="span12">
		<div class="tabbable">
			<form class="form-horizontal">
				<div class="tab-content">
					<div class="tab-pane active">
						<div class="tab-pane-change t_c m_b10">
							<a class="btn {if $stats eq 'order_money' || !$stats}btn-gebo{/if} data-pjax" href="{RC_Uri::url('user/admin_level/init')}&stats=order_money{if $smarty.get.keywords}&keywords={$smarty.get.keywords}{/if}">下单总金额</a>
							<a class="btn {if $stats eq 'order_count'}btn-gebo{/if} m_l10 data-pjax" href="{RC_Uri::url('user/admin_level/init')}&stats=order_count{if $smarty.get.keywords}&keywords={$smarty.get.keywords}{/if}">下单总数</a>
						</div>
						<div class="user_level">
							<div id="user_level">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div>
	<h3 class="heading">
		{$ur_here}
	</h3>
</div>

<div class="row-fluid batch">
	<form action="{RC_Uri::url('user/admin_level/init')}{if $smarty.get.sort_by}&sort_by={$smarty.get.sort_by}{/if}{if $smarty.get.sort_order}&sort_order={$smarty.get.sort_order}{/if}"
	    name="searchForm" method="post">
		<div class="choose_list f_r">
			<input type="text" name="keywords" value="{$smarty.get.keywords}" placeholder="请输入会员名称关键字" />
			<button class="btn search-btn" type="button">搜索</button>
		</div>
	</form>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<table class="table table-striped table-hide-edit">
				<thead>
					<tr data-sorthref='{RC_Uri::url("user/admin_level/init", "{if $smarty.get.keywords}&keywords={$smarty.get.keywords}{/if}")}'>
						<th class="w180">会员名称</th>
						<th data-toggle="sortbyDesc" data-sortby="avaliable_money">可用资金</th>
						<th data-toggle="sortbyDesc" data-sortby="integral">积分</th>
						<th data-toggle="sortbyDesc" data-sortby="order_count">下单总数</th>
						<th data-toggle="sortbyDesc" data-sortby="order_money">下单总金额</th>
						<th data-toggle="sortbyDesc" data-sortby="level" class="w100">会员排行</th>
					</tr>
				</thead>
				<tbody>
					<!-- {foreach from=$list.item key=key item=val} -->
					<tr>
						<td class="hide-edit-area">
							{$val.user_name}
							<div class="edit-list">
								<a class="data-pjax" href='{url path="" args="store_id={$val.user_id}"}'>查看详情</a>
							</div>
						</td>
						<td>{$val.formated_avaliable_money}</td>
						<td>{$val.integral}</td>
						<td>{$val.order_count}</td>
						<td>{$val.formated_order_money}</td>
						<td>{$val.level}</td>
					</tr>
					<!-- {foreachelse}-->
					<tr>
						<td class="no-records" colspan="6">{lang key='system::system.no_records'}</td>
					</tr>
					<!-- {/foreach} -->
				</tbody>
			</table>
			<!-- {$list.page} -->
		</div>
	</div>
</div>
<!-- {/block} -->