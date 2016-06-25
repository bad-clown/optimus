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
	<table class="table table-striped table-bordered" id="order">
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
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<ul class="pagination">
		<!-- <li class="prev">
			<a href="/user/admin/index?page=1" data-page="0">«</a>
		</li>
		<li>
			<a href="/user/admin/index?page=1" data-page="0">1</a>
		</li>
		<li class="active">
			<a href="/user/admin/index?page=2" data-page="1">2</a>
		</li>
		<li class="next disabled">
			<span>»</span>
		</li> -->
	</ul>
</div>
<style type="text/css">
.overlay{position: fixed;top: 0;left: 0;width: 100%;height: 100%;background: #000;opacity: .5;z-index: 9;display: none;}
.popup{position: fixed;top: 50px;left: 50%;width: 700px;height: 350px;background: #fff;z-index: 10;padding: 30px;margin-left: -350px;display: none;}
.popup .table{margin-bottom: 0;}
.popup .close-btn{position: absolute;right: 10px;top: 10px;}
</style>
<div class="details-pop popup">
	<a href="javascrip:;" class="glyphicon glyphicon-remove close-btn"></a>
	<div class="grid-view">
		<div style="height: 308px;overflow-y: auto;overflow-x:hidden;">
			<table class="table table-striped table-bordered" id="orderDetails">
				<thead>
					<tr>
						<th>提货地址</th>
						<th>卸货地址</th>
						<th>数量</th>
						<th>分类</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="overlay"></div>
<?php $this->beginBlock("bottomcode");  ?>
<script type="text/javascript">
$(function() {
	var actPage = 1;
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
	var PageTotal = {
		init : function(d) {
			this.current = actPage, 	//当前页
			this.pageCount = 10, 		//每页显示的数据量
			this.total = d.pageCnt, 	//总共的页码
			this.first = 1, 			//首页
			this.last = 0, 				//尾页
			this.pre = 0, 				//上一页
			this.next = 0, 				//下一页
			this.getData(this.current, this.total)
		},
		getData: function(n, t) {
			$(".pagination").empty();
			if (n == null) {n = 1;}
			this.current = n;
			this.page();
		},
		getPages: function() {
			this.last = this.total;
			this.pre = this.current - 1 <= 0 ? 1 : (this.current - 1);
			this.next = this.current + 1 >= this.total ? this.total : (this.current + 1);
		},
		page: function() {
			$(".pagination").empty();
			var x = 4;
			this.getPages();

			console.log()

			if(this.total > x) {
				var index = this.current <= Math.ceil(x / 2) ? 1 : (this.current) >= this.total - Math.ceil(x / 2) ? this.total - x : (this.current - Math.ceil(x / 2));

				var end = this.current <= Math.ceil(x / 2) ? (x + 1) : (this.current + Math.ceil(x / 2)) >= this.total ? this.total : (this.current + Math.ceil(x / 2));
			}
			else {
				var index = 1;

				var end = this.total;
			}
			if (this.current > 1) {
				$(".pagination").append("<li class='prev'><a href='javascript:;' data-page='"+(this.current - 1)+"'>«</a></li>");
			}
			else if(this.current == 1){
				$(".pagination").append("<li class='prev disabled'><span>«</span></li>");
			}

			for (var i = index; i <= end; i++) {
				if (i == this.current) {
					$(".pagination").append("<li class='active'><a href='javascript:;' data-page='"+(this.current)+"'>"+i+"</a></li>");
				} else {
					$(".pagination").append("<li><a href='javascript:;' data-page='"+i+"'>"+i+"</a></li>");
				}
			}

			if (this.current < end) {
				$(".pagination").append("<li class='next'><a href='javascript:;' data-page='"+(this.current + 1)+"'>»</a></li>");
			}
			else if(this.current == end){
				$(".pagination").append("<li class='next disabled'><span>»</span></li>");
			}
			
		}
	};

	function getData() {
		
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
		$.ajax({
			type : "GET",
			url : "<?= $Path;?>/sched/order-web/processed-list-data",
			data : {
				page : actPage
			},
			dataType : "json",
			success : function(data) {
				PageTotal.init(data)
				var c = $('#order').find('tbody');
				c.empty();
				$.each(data.list, function(i,o) {
					var t = FormatTime(o.deliverTime);
					var h = '<tr><td>'+o.orderNo+'</td><td>'+t+'</td><td>'+status[o.status]+'</td><td>'+o.provinceFrom+o.cityFrom+o.districtFrom+'</td><td>'+o.provinceTo+o.cityTo+o.districtTo+'</td><td><a href="javascript:;" class="orderDetails" data-key="'+o.orderNo+'">'+o.goodsCnt+'件</a></td><td>'+o.totalWeight+'</td><td>'+o.pickupDrop+'</td></tr>';
					c.append(h)
				})
			}
		})
	}
	getData()

	$(document).on('click', '.orderDetails', function() {
		var k = $(this).data('key');
		$.ajax({
			type : "GET",
			url : "<?= $Path;?>/sched/order-web/goods-detail?orderNo="+k,
			dataType : "json",
			success : function(data) {
				var c = $('#orderDetails').find('tbody');
				c.empty();
				$.each(data, function(i, o) {
					var h = '<tr><td>'+ o.addressFrom +'</td><td>'+ o.addressTo +'</td><td>'+ o.count + o.category["unit"]+'</td><td>'+ o.category["name"] +'</td></tr>';

					c.append(h)
					$('.details-pop:eq(0)').show();
					$('.overlay:eq(0)').show();
				})
			}
		})
	})

	$(document).on("click", '.pagination a', function() {
		actPage = $(this).data("page");
		getData()
	})

	$('.close-btn').on('click', function() {
		$('#orderDetails').find('tbody').empty()
		$(this).parents('.popup').hide();
		$('.overlay:eq(0)').hide();
	})
})
</script>
<?php $this->endBlock();  ?>
