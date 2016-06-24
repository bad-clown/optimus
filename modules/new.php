<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use app\components\I18n;
use app\modules\admin\models\Job;
use app\modules\admin\models\Dictionary;
use app\modules\admin\logic\DictionaryLogic;
$Path = \Yii::$app->request->hostInfo;

?>

<div class="grid-view">
	<table class="table table-striped table-bordered" id="newOrder">
		<thead>
			<tr>
				<th>订单号</th>
				<th>提货时间</th>
				<th>状态</th>
				<th>起点</th>
				<th>终点</th>
				<th>总件数</th>
				<th>总吨数</th>
				<th>几装几卸</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

<?php $this->beginBlock("bottomcode");  ?>
<script type="text/javascript">
$(function() {
	function Digit(n) {
		return n < 10 ? "0"+n : n;
	}
	function FormatTime(n) {
		var nS= new Date(parseInt(n) * 1000),
			year=Digit(nS.getFullYear()),
			month=Digit(nS.getMonth()+1),
			date=Digit(nS.getDate()),
			hour=Digit(nS.getHours()),
			minute=Digit(nS.getMinutes());
		return year+"年"+month+"月"+date+"日 "+hour+":"+minute;
	}

	function getData() {
		$.ajax({
			type : "GET",
			url : "<?= $Path;?>/sched/order/new",
			dataType : "json",
			success : function(data) {
				// console.log(data)
				if(data.code == "0") {
					var c = $('#newOrder').find('tbody');
					c.empty();
					var status = {
						100 : "新发布",
						200 : "待确认",
						300 : "待派车",
						400 : "待提货",
						500 : "在途中",
						600 : "已送达",
						700 : "已完成",
						800 : "已拒绝",
						900 : "已过期",
						1000 : "已失效"
					};
					$.each(data.data, function(i,o) {
						var t = FormatTime(o.deliverTime);
						var h = '<tr><td>'+o.orderNo+'</td><td>'+t+'</td><td>'+status[o.status]+'</td><td>'+o.provinceFrom+o.cityFrom+o.districtFrom+'</td><td>'+o.provinceTo+o.cityTo+o.districtTo+'</td><td>'+o.goodsCnt+'</td><td>'+o.totalWeight+'</td><td>'+o.pickupDrop+'</td><td><a class="btn btn-xs btn-primary btn-block j-publish" href="javascript:;" data-key="'+o._id+'" title="">发布</a></td></tr>';
						c.append(h)
					})
				}
			}
		})
	}
	getData()

	$(document).on('click', '.j-publish',function() {
		var k = $(this).data('key');

		$.ajax({
			type : "GET",
			url : "<?= $Path;?>/sched/order/publish?orderId="+k,
			success : function(data) {
				// console.log(data);
				if(data.code == "0") {
					alert('发布成功！');
					getData()
				}
			}
		})
	})
})
</script>
<?php $this->endBlock();  ?>
