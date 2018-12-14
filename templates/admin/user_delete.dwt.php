<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.user_list.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->

<style>
	.ecjia-delete-user .controls.p_t4 {
		padding-top: 4px;
	}

	.ecjia-delete-user .ecjiafc-red.ecjiaf-fs3 {
		margin: 0 5px;
	}

	.ecjia-delete-user .form-horizontal .control-label {
		text-align: left;
	}

	.ecjia-delete-user .form-horizontal .control-group {
		padding-left: 10px;
		padding-right: 10px;
	}

	.ecjia-delete-user .form-horizontal .controls-info {
		color: #595959;
		display: inline-block;
	}
</style>

<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>


<div class="row-fluid ecjia-delete-user">
	<div class="span12">
		<div class="form-horizontal">
			{if $user_log_empty}
			<div class="alert alert-warning">
				<a class="close" data-dismiss="alert">×</a>
				<strong>
					<p>温馨提示</p>
				</strong>
				<p>当前账户没有关联数据，您可以直接删除此会员账户。</p>
			</div>
			{else}
				<!-- {foreach from=$handles item=val} -->
				<div class="control-group formSep">
					<label class="control-label">{$val->getName()}</label>
					<div class="controls p_t4">
						{$val->handlePrintData()}
						{if $val->handleCanRemove()}
						<span class="controls-info-right f_r">
							<a class="btn btn-gebo" data-toggle="ajaxremove" data-msg="您确定要这么做吗？" href="{RC_Uri::url('user/admin/remove')}&id={$user.user_id}&handle={$val->getCode()}">删除数据</a>
						</span>
						{/if}
					</div>
				</div>
				<!-- {/foreach} -->

			<div class="control-group formSep">
				<label class="control-label">账户余额</label>
				<div class="controls p_t4">
					<span class="controls-info w200">账户内可用余额<span class="ecjiafc-red ecjiaf-fs3">¥1200.00</span></span>
					<span class="controls-info"><a href="javascript:;" target="__blank">查看全部>>></a></span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户红包</label>
				<div class="controls p_t4">
					<span class="controls-info">账户内可用红包<span class="ecjiafc-red ecjiaf-fs3">8</span>个</span>
					<span class="controls-info"></span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户收藏商品</label>
				<div class="controls p_t4">
					<span class="controls-info">账户共收藏<span class="ecjiafc-red ecjiaf-fs3">12</span>件商品</span>
					<span class="controls-info"></span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">第三方账号关联</label>
				<div class="controls p_t4">
					<span class="controls-info  w200">已关联<span class="ecjiafc-red ecjiaf-fs3">QQ、微信</span></span>
					<span class="controls-info"><a href="javascript:;" target="__blank">查看全部>>></a></span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户日志</label>
				<div class="controls p_t4">
					<span class="controls-info">与账号有关的所有日志记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户微信粉丝账号</label>
				<div class="controls p_t4">
					<span class="controls-info">账户关注微信公众号对应的粉丝账号</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户发送消息记录</label>
				<div class="controls p_t4">
					<span class="controls-info">账户微信公众号上发送的所有消息记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户抽奖记录</label>
				<div class="controls p_t4">
					<span class="controls-info">账户参与抽奖活动有关的所有记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">微信客服消息记录</label>
				<div class="controls p_t4">
					<span class="controls-info">账户微信公众号上给客服发送的所有消息记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">用户充值/提现记录</label>
				<div class="controls p_t4">
					<span class="controls-info">账户充值、提现有关的所有记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户收藏店铺</label>
				<div class="controls p_t4">
					<span class="controls-info">账户共收藏<span class="ecjiafc-red ecjiaf-fs3">12</span>家店铺</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">账户发票信息</label>
				<div class="controls p_t4">
					<span class="controls-info">账号添加的发票信息</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">会员积分记录</label>
				<div class="controls p_t4">
					<span class="controls-info">账户积分所有的变动记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">会员父集ID</label>
				<div class="controls p_t4">
					<span class="controls-info">会员推荐注册时绑定的会员父级ID</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			<div class="control-group formSep">
				<label class="control-label">会员邀请记录</label>
				<div class="controls p_t4">
					<span class="controls-info">会员推荐邀请的所有记录</span>
					<span class="controls-info-right f_r"><a class="btn btn-gebo">删除数据</a></span>
				</div>
			</div>
			{/if}

			<div class="control-group">
				{if $delete_all}
				<a class="btn">一键删除所有</a>
				{/if}

				<a class="btn btn-gebo" data-toggle="ajaxremove" data-msg="当前账户还有关联数据没有删除，请删除完关联数据后，再删除会员" href="{RC_Uri::url('user/admin/remove')}&id={$user.user_id}">删除会员</a>

				<div class="help-block">
					<p>注：一键删除：点击后，会将以上所有有关当前账号的数据全部删除，一旦删除后将不可恢复。</p>
					<p>删除会员：点击后，将当前会员账号彻底删除。</p>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- {/block} -->