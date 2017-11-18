<?php if (!defined('THINK_PATH')) exit();?> <!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>南苑计协维修系统</title>
		<link rel="stylesheet" type="text/css" href="/repair_system/Public/css/bootstrap.min.css">
		<link rel="stylesheet" href="/repair_system/Public/css/indexFrame.css">
		<style type="text/css">
			ul li {
				list-style: none;
			}
			.list-group-item {
				border:none;
				border-bottom:1px solid #eee;
				border-top:1px solid #eee;
			}
			.row{
				margin-left: 0;
				margin-right: 0;
			}
		</style>
	</head>
	<body>
		<div align="left" style="margin-left: 20px;margin-bottom:20px">
			<h3>所有订单状态</h3>
		</div>
		<?php if(is_array($admins)): $i = 0; $__LIST__ = $admins;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$admin): $mod = ($i % 2 );++$i;?><h4>
				<?php echo ($admin['name']); ?>
			</h4>
			<div>
				<?php if(is_array($ato)): $i = 0; $__LIST__ = $ato;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ador): $mod = ($i % 2 );++$i; if($ador['admin_id'] == $admin['id']): if(is_array($order_detail)): $i = 0; $__LIST__ = $order_detail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$order): $mod = ($i % 2 );++$i;?><ul>
								<?php if($order['id'] == $ador['order_id']): ?><li>
										<strong>类型：</strong><?php echo ($order['service_type']); ?>
									</li>
									<li>
										<strong>内容：</strong><?php echo ($order['content']); ?>
									</li>
									<li>
										<strong>状态：</strong><?php echo ($ador['isend'] == '1'?'已完成':'未完成'); ?>
									</li>
									<?php if(is_array($judges)): foreach($judges as $key=>$judge): if($judge['order_id'] == $order['id']): ?><li>
												<strong>评价：</strong><?php echo ($judge['content']); ?>
											</li><?php endif; endforeach; endif; ?>
									<hr><?php endif; ?>
							</ul><?php endforeach; endif; else: echo "" ;endif; endif; endforeach; endif; else: echo "" ;endif; ?>
			</div><?php endforeach; endif; else: echo "" ;endif; ?>

		
		<script type="text/javascript" src="/repair_system/Public/js/jquery-2.2.4.min.js"></script>
		<script type="text/javascript" src="/repair_system/Public/js/bootstrap.min.js"></script>
	</body>
</html>