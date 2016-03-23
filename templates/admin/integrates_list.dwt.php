<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.integrate_list.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="form-div">
	<div class="alert alert-info" ><!-- {$lang.user_help} --></div>
</div>

<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>	
</div>
<div class="row-fluid">
	<div class="span12">
		<form method="post"  name="listForm" >
			<table class="table" id="smpl_tbl">
				<thead>
					<tr>
						<th class="w100">{t}名称{/t}</th>
						<th>{t}描述{/t}</th>
					</tr>
				</thead>
				<tbody>
					<!-- {foreach from=$integrate_list item=integrate} -->
					<tr{if $integrate.activate eq 1} class="info left_border"{/if}>
						<td class="first-cell">{$integrate.format_name}</td>
						<td class="first-cell">{$integrate.format_description}
							<br/>
						<!-- {if $integrate.activate eq 1} -->
							<a class="cursor_pointer data-pjax" id="setup" href='{url path="user/admin_integrate/setup" args="code={$integrate.code}"}'>{t}设置{/t}</a>
						<!-- {else} -->
							<a class="install cursor_pointer" href='{url path="user/admin_integrate/activate" args="code={$integrate.code}"}' >{t}启用{/t}</a>
						<!-- {/if} -->
						</td>
					</tr>
					<!-- {foreachelse} -->
					<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
					<!-- {/foreach} -->
				</tbody>
			</table>
		</form>
	</div>
</div>
<!-- {/block} -->