// JavaScript Document

;(function(app, $) {
	app.integrate_list = {
		init : function() {
			$('#smpl_tbl').dataTable({
				sDom: "<'row page'<'span6'<'dt_actions'>l><'span6'f>r>t<'row page pagination'<'span6'i><'span6'p>>",
				aaSorting: [[ 1, "asc" ]],

				bPaginate : true,
				sPaginationType: "bootstrap",
				oLanguage : {
					oPaginate: {
						sFirst : '首页',
						sLast : '尾页',
						sPrevious : '上一页',
						sNext : '下一页',
					},
					sInfo : "共_TOTAL_条记录 第_START_ 到 第_END_条",
					sInfoEmpty : "共0条记录 第0 到 第0条",
					sZeroRecords : "没有找到任何记录",
				},
				aoColumns : [
				{ sType : "string" },
				{ bSortable : false }
				]
			});

			app.integrate_list.install_user();
			// app.integrate_list.click();
			// app.integrate_list.confirm();
		},

		install_user : function() {
			$('.install').on('click', function(e) {
				e.preventDefault();
				var $this = $(this),
					href = $this.attr('href');
				$.get(href, '', function(data) {
					ecjia.admin.showmessage(data);
				});
			});
		},
		// click : function() {
		// 	$("#setup").on('click', function(e) {
		// 		e.preventDefault();
		// 		url = $(this).attr('data-ajax-url');
		// 		$.get(url,'',function(data) {
		// 			if (data.state == "error") {
		// 				ecjia.admin.showmessage(data);
		// 			} else {
		// 				ecjia.pjax(url);
		// 			}
		// 		});
		// 	});
		// },
		// confirm : function() {
		// 	$('.install').on('click' , function(e) {
		// 		e.preventDefault();
		// 		smoke.confirm('你确定要安装该会员数据整合插件吗？' , function(e){
		// 			if (e) {
		// 				var url = $('.install').attr('href');
		// 				ecjia.pjax(url);
		// 				// var url = $('.install').attr('data-confirm-url');
		// 				// $.get(url,'',function(data) {
		// 				// 	if (data.state == "error") {
		// 				// 		ecjia.admin.showmessage(data);
		// 				// 	} else {
		// 						// ecjia.pjax(url);
		// 				// 	}
		// 				// });
		// 			}	
		// 		}, {ok:'确定', cancel:'取消'});
		// 	});
		// }
	};
	
	app.integrate_setup = {
		init : function() {
			app.integrate_setup.check_config();
			// app.integrate_setup.change_connect();
			// app.integrate_setup.confirm();
			// app.integrate_setup.submit();
		},

		check_config : function() {
			var $this = $("form[name='setupForm'] , form[name='Form1']");
			var option = {
				rules:{
					 'cfg[uc_id]'			: {required : 	true},
					 'cfg[uc_key]'			: {required : 	true},
					 'cfg[uc_url]'			: {required : 	true},
					 'cfg[uc_ip]'			: {required : 	true},
					 
					 'cfg[db_host]' 		: {required : 	true},
					 'cfg[db_user]' 		: {required : 	true},
					 'cfg[db_name]' 		: {required : 	true},
					'cfg[integrate_url]' 	: {url : 		true},
					'cfg[cookie_prefix]' 	: {required : 	true}
				    },
				 messages:{
					 'cfg[uc_id]' 		: {required : 	"请输入UCenter 应用 ID！"},
					 'cfg[uc_key]' 		: {required : 	"请输入UCenter 通信密钥！"},
					 'cfg[uc_url]' 		: {required : 	"请输入UCenter IP 地址！"},
					 'cfg[uc_ip]' 		: {required : 	"请输入UCenter 连接方式！"},
					 
					 'cfg[db_host]' 		: {required : 	"请输入服务器主机名！"},
					 'cfg[db_user]' 		: {required : 	"请输入数据库帐号！"},
					 'cfg[db_name]' 		: {required : 	"请输入数据库密码！"},
					'cfg[integrate_url]' 	: {url : 		"请输入完整URL！"},
					'cfg[cookie_prefix]' 	: {required : 	"请输入COOKIE前缀！"}
				    },	
				submitHandler:function() {
					$this.ajaxSubmit({
						dataType:"json",
						success:function(data) {
							ecjia.admin.showmessage(data);
						}
					});
				}
			}
			var options = $.extend(ecjia.admin.defaultOptions.validate, option);
			$this.validate(options);

			// $('form[name="Form1"]').on('submit', function(e) {
			// 	e.preventDefault();
			// 	var $this = $(this);
			// 	$this.ajaxSubmit({
			// 		dataType:"json",
			// 		success:function(data){
			// 			ecjia.admin.showmessage(data);
			// 		}
			// 	});
			// });
		},

		// change_connect : function() {
		// 	$('[name="cfg[uc_connect]"]').on('change', function() {
		// 		var $this = $(this);
		// 		$this.val() == 'mysql' ? $('#uc_db').removeClass('hide') : $('#uc_db').addClass('hide');
		// 	});
		// },
// 		confirm : function() {
// 			smoke.confirm('你确定要直接保存配置信息吗？',function(e){
// 				if (e) {
// 					$("form[name='setupForm']").ajaxSubmit({
// 						dataType:"json",
// 						success:function(data){
// 								// if (data.state == "success") {
// //										var url = $("form[name='setupForm']").attr('data-edit-url');
// //										ecjia.pjax(url + "&id=" + data.max_id, function(){
// 									// ecjia.admin.showmessage(data);
// //										});
// 								// } else {
// 									ecjia.admin.showmessage(data);
// 								// }	
// 							}
// 						});
// 				}	
// 			}, {ok:'确定', cancel:'取消'});	
// 		},
// 		submit : function() {
// 			var $this = $("form[name='setupForm']");
// 			var option = {
// 				rules:{
// 					 'cfg[db_host]' 		: {required : true},
// 					 'cfg[db_user]' 		: {required : true},
// 					 'cfg[db_name]' 		: {required : true},
// 					'cfg[integrate_url]' 	: {required : true},
// 					'cfg[cookie_prefix]' 	: {required : true}
// 				    },
// 				 messages:{
// 					 'cfg[db_host]' 		: {required : "请输入服务器主机名！"},
// 					 'cfg[db_user]' 		: {required : "请输入数据库帐号！"},
// 					 'cfg[db_name]' 		: {required : "请输入数据库密码！"},
// 					'cfg[integrate_url]' 	: {required : "请输入完整URL！"},
// 					'cfg[cookie_prefix]' 	: {required : "请输入COOKIE前缀！"}
// 				    },	
// 				submitHandler:function() {
// 					$this.ajaxSubmit({
// 						dataType:"json",
// 						success:function(data) {
// 							ecjia.admin.showmessage(data);
// 						}
// 					});
// 				}
// 			}
// 			var options = $.extend(ecjia.admin.defaultOptions.validate, option);
// 			$this.validate(options);
// 		},
		
	};
	
	app.integrate_install = {
			init : function() {
				app.integrate_install.submit();
			},
			submit : function() {
				var $this = $('form[name="Form2"]');
				var option = {
					submitHandler : function() {
						$this.ajaxSubmit({
							dataType : "json",
							success : function(data) {
								ecjia.admin.showmessage(data);
							}
						});
					}
				}
				var options = $.extend(ecjia.admin.defaultOptions.validate, option);
				$this.validate(options);
			}
		};

})(ecjia.admin, jQuery);


// end
