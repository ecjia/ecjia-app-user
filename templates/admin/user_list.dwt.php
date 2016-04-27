<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.user_list.init();
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

	<div class="row-fluid batch" >
		<form method="post" action="{$search_action}" name="searchForm">
			<div class="btn-group f_l m_r5">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="fontello-icon-cog"></i>{t}批量操作{/t}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}" data-msg="删除会员将清除该会员的所有信息。您确定要这么做吗？" data-noSelectMsg="请先选中要删除的用户！" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-trash"></i>{t}删除会员{/t}</a></li>
				</ul>
			</div>

			<select class="w120" name="rank" id="select-rank">
				<option value="0">{$lang.all_option}</option>
				<!--{foreach from=$user_ranks item=item}-->
				<option value="{$item.rank_id}" {if $smarty.get.rank eq $item.rank_id} selected="selected" {/if}>{$item.rank_name}</option>
				<!-- {/foreach} -->
			</select>
			<a class="btn m_l5 screen-btn">{t}筛选{/t}</a>
			<div class="top_right f_r" >
				<input type="text" name="keywords" value="{$smarty.get.keywords}" placeholder="请输入会员名称或者邮箱关键字"/> 
				<button class="btn m_l5" type="submit">{t}搜索会员{/t}</button>
			</div>
		</form>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<form method="post" action="{$form_action}" name="listForm" data-pjax-url="{url path='user/admin/init'}">
				<div class="row-fluid">
					<table class="table table-striped smpl_tbl table-hide-edit">
						<thead>
							<tr>
								<th class="table_checkbox"><input type="checkbox" data-toggle="selectall" data-children=".checkbox"/></th>
								<!-- <th class="w50" >{t}头像{/t}</th> -->
								<th>{t}名称{/t}</th>
								<th>{$lang.email}</th>
								<th>{$lang.user_money}</th>
								<th>{$lang.frozen_money}</th>
								<th>{$lang.rank_points}</th>
								<th>{$lang.pay_points}</th>
								<th colspan="2">{$lang.reg_date}</th>
								
							</tr>
						</thead>
						<tbody>
							<!--{foreach from=$user_list.user_list item=user}-->
							<tr>
								<td class="center-td">
									<input class="checkbox" type="checkbox" name="checkboxes[]"  value="{$user.user_id}" />
								</td>
								<!-- <td class="thumbimg">
									<a class="data-pjax" href='{url path="user/admin/info" args="id={$user.user_id}"}'>
										<img class="thumbnail" alt="{t}用户头像{/t}" src="{RC_Uri::admin_url('statics/images/nopic.png')}" >
									</a>
								</td> -->
								<td class="hide-edit-area">
									<!-- {if $user.user_name} -->
									{$user.user_name}
									<!-- {/if} -->
									<br/>
									<div class="edit-list">
										<a class="data-pjax" href='{url path="user/admin/info" args="id={$user.user_id}"}'>{t}详细信息{/t}</a>&nbsp;|&nbsp; 
										<a class="data-pjax" href='{url path="user/admin/address_list" args="id={$user.user_id}"}' title="{$lang.address_list}">{$lang.address_list}</a>&nbsp;|&nbsp;
										<a target="_blank" href='{url path="orders/admin/init" args="user_id={$user.user_id}"}' title="{$lang.view_order}">{$lang.view_order}</a>&nbsp;|&nbsp;
										<a target="_blank" href='{url path="user/admin_account_log/init" args="user_id={$user.user_id}"}' title="{$lang.view_deposit}">{$lang.view_deposit}</a>&nbsp;|&nbsp;
										<a class="data-pjax" href='{url path="user/admin/edit" args="id={$user.user_id}"}' title="{$lang.edit}">{$lang.edit}</a>&nbsp;|&nbsp; 
										<a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg='{t name="{$user.user_name}"}您确定要删除会员[ %1 ]吗？{/t}' href='{url path="user/admin/remove" args="id={$user.user_id}"}' title="{t}删除{/t}">{t}删除{/t}</a>
									</div>
								</td>
								<td>
									<!-- {if $user.email} -->
									<span class="cursor_pointer" data-trigger="editable" data-url="{url path='user/admin/edit_email'}" data-name="email" data-pk="{$user.user_id}" data-title="编辑邮箱地址">{$user.email}</span><span class="ecjiafc-f00">{if $user.is_validated}{t} (已验证) {/t}{/if}</span>
									<!-- {/if} -->
								</td>
								<td>{$user.user_money}</td>
								<td>{$user.frozen_money}</td>
								<td>{$user.rank_points}</td>
								<td>{$user.pay_points}</td>
								<td>{if $user.reg_time}{$user.reg_time}{else}{t}1970-01-01 00:00:00{/t}{/if}</td>
							</tr>
							<!--{foreachelse}-->
							<tr><td class="no-records" colspan="11">{$lang.no_records}</td></tr>
							<!--{/foreach} -->
						</tbody>
					</table>
					<!-- {$user_list.page} -->
				</div>
			</form>
		</div>
	</div>
<!-- {/block} -->