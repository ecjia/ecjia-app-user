<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.account_list.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}" ><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid batch">
	<form action="{$form_action}" name="searchForm" method="post">
		<div class="wspan12">
			<div class="top_right f_r">
				<input class="w150" type="text" name="keywords" value="{$list.filter.keywords}" placeholder="请输入会员名称"/>
				<button class="btn m_l5" type="submit">{$lang.button_search}</button>
			</div>
		</div>
		
		<div class="btn-group f_l m_t10">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fontello-icon-cog"></i>{t}批量操作{/t}
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$batch_action}" data-msg="已完成的申请无法被删除，你确定要删除选中的列表吗？" data-noSelectMsg="请先选中要操作的项！" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-trash"></i>{t}批量删除{/t}</a></li>
			</ul>
		</div>
		
		<div class="choose_list f_r m_t10">
			<select class="w80" name="process_type">
			<option value="-1" >{$lang.process_type}</option>
			<option value="0" {if $list.filter.process_type eq 0} selected="selected" {/if}>{$lang.surplus_type_0}</option>
			<option value="1" {if $smarty.get.process_type eq 1} selected="selected" {/if} >{$lang.surplus_type_1}</option>
			</select>
			<select class="w120" name="payment">
				<option value="">{$lang.pay_mothed}</option>
				<!-- {foreach from=$payment item=item} -->
				<option value="{$item.pay_name}" {if $list.filter.payment eq $item.pay_name} selected="selected" {/if}>{$item.pay_name}</option> 
				<!-- {/foreach} -->
			</select>
			<select class="w80" name="is_paid">
				<option value="-1">{$lang.status}</option>
				<option value="0" {if $list.filter.is_paid eq 0} selected="selected" {/if}>{$lang.unconfirm}</option>
				<option value="1" {if $smarty.get.is_paid eq 1} selected="selected" {/if}>{$lang.confirm}</option>
				<option value="2" {if $smarty.get.is_paid eq 2} selected="selected" {/if}>{$lang.cancel}</option>
			</select>
			<input class="date f_l w150" name="start_date" type="text" value="{$smarty.get.start_date}" placeholder="开始时间">
			<span class="f_l">至</span>
			<input class="date f_l w150" name="end_date" type="text" value="{$smarty.get.end_date}" placeholder="结束时间">
			
			<button class="btn select-button" type="button">{t}筛选{/t}</button>
		</div>
		
	</form>
</div>
<div class="row-fluid">
	<div class="span12">
		<table class="table table-striped" id="smpl_tbl">
			<thead>
				<tr>
					<th class="table_checkbox"><input type="checkbox" data-toggle="selectall" data-children=".checkbox"/></th>
					<th>{$lang.user_id}</th>
					<th>{$lang.surplus_amount}</th>
					<th>{$lang.process_type}</th>
					<th>{$lang.pay_mothed}</th>
					<th>{$lang.status}</th>
					<th>{$lang.add_date}</th>
					<th>{$lang.handler}</th>
				</tr>
			</thead>
			<tbody>
				<!-- {foreach from=$list.list item=item}-->
				<tr>
					<td class="center-td">
						<!-- {if $item.is_paid neq 1} -->
						<input class="checkbox" type="checkbox" name="checkboxes[]"  value="{$item.id}" />
						<!-- {else} -->
						<input type="checkbox" value="{$item.id}" disabled="disabled" />
						<!-- {/if} -->
					</td>
					<td>{if $item.user_name}{$item.user_name}{else}{$lang.no_user}{/if}</td>
					<td align="right">{$item.surplus_amount}</td>
					<td align="center">{$item.process_type_name}</td>
					<td>{if $item.payment}{$item.payment}{/if}</td>
					<td align="center">{if $item.is_paid eq 1}{$lang.confirm}{elseif $item.is_paid eq 0}{$lang.unconfirm}{else}{$lang.cancel}{/if}</td>
					<td align="center">{$item.add_date}</td>
					<td align="center">
						<!-- {if $item.is_paid eq 1} -->
						<a class="data-pjax no-underline" href='{url path="user/admin_account/edit" args="id={$item.id}"}' title="{t}编辑{/t}"><i class="fontello-icon-edit"></i></a>
						<!-- {else} -->
						<a class="data-pjax no-underline" href='{url path="user/admin_account/check" args="id={$item.id}"}' title="{$lang.check}" ><i class="fontello-icon-doc-text"></i></a>
						<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要删除会员[{$item.user_name}]的充值提现记录吗？{/t}" href='{url path="user/admin_account/remove" args="id={$item.id}"}' title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
						<!-- {/if} -->
					</td>
				</tr>
				<!-- {foreachelse}-->
				<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
				<!-- {/foreach} -->
			</tbody>
		</table>
		<!-- {$list.page} -->
	</div>
</div>
<!-- {/block} -->