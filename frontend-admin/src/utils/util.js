//  暴露
export default {
	timestampToTime(cjsj, short) {
		let len = cjsj.toString().length;
		var date
		if (len == 13) {
			date = new Date(cjsj) //时间戳为10位需*1000，时间戳为13位的话不需乘1000
		} else {
			date = new Date(cjsj * 1000) //时间戳为10位需*1000，时间戳为13位的话不需乘1000
		}
		var YY = date.getFullYear() + '-';
		var MM = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
		var DD = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
		var hh = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
		var mm = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
		var ss = (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
		if (!short) {
			return YY + MM + DD + " " + hh + mm + ss;
		} else {
			return YY + MM + DD;
		}

	},
	getLastDate(date, day) {
		let dd = new Date(date);
		dd.setDate(dd.getDate() + day);
		var y = dd.getFullYear();
		var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
		var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
		return y + "-" + m + "-" + d;
	},
	//计算时间差
	intervalTime(startTime,endTime) {
		 let stime = new Date(startTime.replace(/-/g, "/"));
		 let etime = new Date(endTime.replace(/-/g, "/"));
	     let date1 = Date.parse(new Date(stime))/1000; //开始时间
	     let date2 = Date.parse(new Date(etime))/1000; //开始时间
	    let date3 =  (date2- date1); //时间差的秒数
		// console.log('startTime',startTime)
		// console.log('endTime',endTime)
		// console.log('stime',stime)
		// console.log('etime',etime)
	    //计算出相差天数
	    var days = Math.floor(date3 / (24 * 3600));
	    //计算出小时数
	    var leave1 =date3 -  days*24*3600; //计算天数后剩余的秒数
	    var hours = Math.floor(leave1 / 3600);
	    //计算相差分钟数
	    var leave2 =date3 - days*24*3600 - hours*3600; //计算小时数后剩余的秒数
	    var minutes = Math.floor(leave2 / 60);	
	    var seconds =date3 - days*24*3600 - hours*3600 - minutes*60;
		let time = ''
		if(days>0){
			time = days + "天 "
		}
		if(hours>0){
			time = time + hours + "小时 " 
		}
		time = time + minutes + "分 "+ seconds + "秒 "
		// console.log(time)
	    return time
	}
};
