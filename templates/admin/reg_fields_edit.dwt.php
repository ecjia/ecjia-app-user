<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.user_reg_fields.init();
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
<div class="row-fluid edit-page">
	<div class="span12">
		<form  class="form-horizontal"  name="theForm" action="{$form_action}" method="post" data-edit-url='{url path="user/admin_reg_fields/edit"}'>
			<fieldset>
				<div class="control-group formSep">
					<label class="control-label">{$lang.reg_field_name}：</label>
					<div class="controls">
						<input name="reg_field_name" type="text" value="{$reg_field.reg_field_name}"/>
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{$lang.field_order}：</label>
					<div class="controls">
						<input name="reg_field_order" type="text" value="{$reg_field.reg_field_order}" />
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<!-- 是否显示 -->
				<div class="control-group formSep ">
					<label class="control-label">{$lang.field_display}：</label>
					<div class="controls chk_radio">
						<input type="radio"  name="reg_field_display" value="1" {if $reg_field.reg_field_display eq 1}checked='checked'{/if}/><span>{$lang.yes}</span>
						<input type="radio"  name="reg_field_display" value="0" {if $reg_field.reg_field_display eq 0}checked='checked'{/if}/><span>{$lang.no}</span>
					</div>
				</div>
				<!-- 是否必填 -->
				<div class="control-group formSep ">
					<label class="control-label">{$lang.field_need}：</label>
					<div class="controls chk_radio">
						<input type="radio"  name="reg_field_need" value="1" {if $reg_field.reg_field_need eq 1}checked='checked'{/if}/><span>{$lang.yes}</span>
						<input type="radio"  name="reg_field_need" value="0" {if $reg_field.reg_field_need eq 0}checked='checked'{/if}/><span>{$lang.no}</span>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
						<input type="hidden" name="id" value="{$reg_field.reg_field_id}" />
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!-- {/block} -->