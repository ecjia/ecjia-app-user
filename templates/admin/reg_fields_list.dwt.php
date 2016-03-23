<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div>
	<table class="table table-striped" data-pjax-url='{url path="user/admin_reg_fields/init"}'>
		<thead>
			<tr>
				<th>{$lang.field_name}</th>
				<th>{$lang.field_order}</th>
				<th>{$lang.field_display}</th>
				<th>{$lang.field_need}</th>
				<th>{$lang.handler}</th>
			</tr>
		</thead>
		<tbody>
			<!-- {foreach from=$reg_fields item=field} -->
			<tr>
				<td class="first-cell" >
					<!-- {if $field.reg_field_name} -->
					<span class="cursor_pointer" data-trigger="editable" data-url='{url path="user/admin_reg_fields/edit_name"}' data-name="field_name" data-pk="{$field.id}" data-title="{t}编辑会员注册项名称{/t}">{$field.reg_field_name}</span>
					<!-- {/if} -->
				</td>
				<td align="center">
					<span class="cursor_pointer" data-trigger="editable" data-url='{url path="user/admin_reg_fields/edit_order"}' data-name="dis_order" data-pk="{$field.id}" data-title="{t}编辑排序{/t}">{$field.dis_order}</span>
				</td>
				<td align="center">
					<i class="cursor_pointer {if $field.display}fontello-icon-ok{else}fontello-icon-cancel{/if}" data-trigger="toggleState" data-url="{url path='user/admin_reg_fields/toggle_dis'}" data-id="{$field.id}" title="点击切换状态"></i>
				</td>
				<td align="center">
					<i class="cursor_pointer {if $field.is_need}fontello-icon-ok{else}fontello-icon-cancel{/if}" data-trigger="toggleState" data-url="{url path='user/admin_reg_fields/toggle_need'}" data-id="{$field.id}" title="点击切换状态"></i>
				</td>
				<td align="right">
					<a href='{url path="user/admin_reg_fields/edit" args="id={$field.id}"}' title="{$lang.edit}" class="data-pjax no-underline"><i class="fontello-icon-edit"></i></a>
					<!-- {if $field.type eq 0}  -->
					<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg='{t name="{$field.reg_field_name}"}您确定要删除会员注册项[ %1 ]吗？{/t}' href='{url path="user/admin_reg_fields/remove" args="id={$field.id}"}' title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
					<!-- {/if} -->
				</td>
			</tr>
			<!-- {/foreach} -->
		</tbody>
	</table>
</div>

<!-- {/block} -->