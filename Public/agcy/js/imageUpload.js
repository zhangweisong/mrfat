/**
 * 移动端图片上传
 */
$.fn.extend({
	_opt: {
		limitSize: 3,
		usestyle:false
	},
	imageUpload: function(options) {
		var _this = this,
			styles = {
				"width": "25px",
				"height": "20px",
				"float": "left",
				"margin-right": "10px",
				"background-size": "100%"
			};
		_this._opt = $.extend(_this._opt, options);
		if(_this._opt.usestyle){
			$(this).css(styles);
		}
		try{
			$(_this._opt.imgTar).on('change', function(e) {
				var file  = e.target.files[0];
				if(Math.ceil(file.size/1024/1024) > _this._opt.limitSize) {
					console.error('文件太大');
					return;
				}
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (f) {
                	_this.upload(f.target.result);
                };
			});
		} catch(e) {
			console.log(e);
		}
	},
	upload: function(data) {
		$("#loading").fadeIn(); //加载
		var _this = this, filed = _this._opt.uploadField;
		$.ajax({
			url: _this._opt.uploadUrl,
			type: 'post',
			data: $.extend(_this._opt.data, {filed: data}),
			cache: false
		})
		.then(function(res) {
			var src = _this._opt.uploadSuccess(res);
			if(src) {
			    _this.insertImage(src);
			} else {
				_this._opt.uploadError(res);
			}
		});
		//$("#loading").fadeOut();//加载完毕 
	},
	insertImage: function(img){
		var _this = this;
		$('#'+_this._opt.uploadField).val(img);
	}
});




$.fn.extend({
	_opt2: {
		limitSize: 3,
		usestyle:false
	},
	imageUpload2: function(options) {
		var _this2 = this,
			styles = {
				"width": "25px",
				"height": "20px",
				"float": "left",
				"margin-right": "10px",
				"background-size": "100%"
			};
		_this2._opt2 = $.extend(_this2._opt2, options);
		if(_this2._opt2.usestyle){
			$(this).css(styles);
		}
		try{
			$(_this2._opt2.imgTar).on('change', function(e) {
				var file  = e.target.files[0];
				if(Math.ceil(file.size/1024/1024) > _this2._opt2.limitSize) {
					console.error('文件太大');
					return;
				}
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (f) {
                	_this2.upload2(f.target.result);
                };
			});
		} catch(e) {
			console.log(e);
		}
	},
	upload2: function(data) {
		$("#loading").fadeIn(); //加载
		var _this2 = this, filed = _this2._opt2.uploadField;
		$.ajax({
			url: _this2._opt2.uploadUrl,
			type: 'post',
			data: $.extend(_this2._opt2.data, {filed: data}),
			cache: false
		})
		.then(function(res) {
			var src = _this2._opt2.uploadSuccess(res);
			if(src) {
			    _this2.insertImage2(src);
			} else {
				_this2._opt2.uploadError(res);
			}
		});
		//$("#loading").fadeOut();//加载完毕 
	},
	insertImage2: function(img){
		var _this2 = this;
		$('#'+_this2._opt2.uploadField).val(img);
	}
});

