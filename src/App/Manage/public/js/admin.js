$.extend({
    parseQuery: function (query) {
        query = query.substring(1);
        var params = [];
        if (query) {
            var paramSegments = query.split('&');
            var segment = [];
            for (var i in paramSegments) {
                segment = paramSegments[i].split('=');
                if (segment.length == 1) {
                    params.push(segment[0]);
                } else if (segment.length == 2) {
                    params[segment[0]] = segment[1];
                } else if (segment.length > 2) {
                    var key = segment.shift();
                    params[key] = segment.join('=');
                }
            }
        }
        return params;
    },

    buildQuery: function (params) {
        var query = '';
        if (params) {
            var segments = [];
            for (var i in params) {
                if (!isNaN(i)) {
                    segments.push(params[i]);
                } else {
                    segments.push(i +'='+ params[i]);
                }
            }
            query = segments.join('&');
        }
        return query ? '?'+ query : '';
    },

    myAjax: function (method, url, params, options) {
        options = options || {};
        var defaults = {
            dataType: 'json',
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        alert('操作成功');
                        location.reload();
                    } else if (resp.meta.msg != undefined && resp.meta.msg) {
                        alert(resp.meta.msg);
                    } else {
                        alert('操作失败');
                    }
                } else {
                    alert('返回结果异常');
                }
            },
            error: function (resp) {
                alert('请求失败');
            }
        };
        var opts = $.extend(defaults, options);
        if (method) {
            opts.type = method;
        }
        if (url) {
            opts.url = url;
        }
        if (params) {
            opts.data = params;
        }
        $.ajax(opts);
    },

    myAjaxGet: function (url, params, options) {
        $.myAjax('get', url, params, options);
    },

    myAjaxPost: function (url, params, options) {
        $.myAjax('post', url, params, options);
    }
});

;(function ($) {
    $.fn.pager = function () {
        var $this = $(this);
        var total = $this.data('total');
        var itemCount = $this.data('item-count');
        var defaultPageSize = $this.data('page-size');
        if (defaultPageSize == undefined || defaultPageSize <= 0) {
            defaultPageSize = 20;
        }
        var isSimplePager = $this.data('simple-pager') != undefined;
        var displayPageNum = $this.data('display-page');
        if (displayPageNum == undefined ||displayPageNum <= 0) {
            displayPageNum = 5;
        } else {
            displayPageNum = parseInt(displayPageNum);
        }
        var html = '';
        var params = $.parseQuery(location.search);
        var pageSize = params['count'] == undefined || params['count'] <= 0 ? defaultPageSize : parseInt(params['count']);
        var maxPage = total != undefined && total > 0 ? Math.ceil(total / pageSize) : 0;
        var curPage = params['page'] == undefined  || params['page'] <= 0 ? 1 : parseInt(params['page']);
        if (maxPage > 0 && curPage > maxPage) {
            curPage = maxPage;
        }

        //prev
        if (curPage <= 1) {
            params['page'] = 1;
        } else {
            params['page'] = curPage - 1;
        }
        var prevLink = location.pathname + $.buildQuery(params);

        //next
        if (curPage <= 1) {
            params['page'] = 2;
        } else {
            var nextPage = curPage + 1;
            if (maxPage > 0 && nextPage > maxPage) {
                nextPage = maxPage;
            }
            params['page'] = nextPage;
        }
        var nextLink = location.pathname + $.buildQuery(params);

        var disabledPrevPage = curPage == 1;
        var disabledNextPage = itemCount == 0;
        if (isSimplePager) {
            //仅有上一页/下一页的简单分页条
            html = '<ul class="pager mt-pager">' +
                    '<li'+ (disabledPrevPage ? ' class="disabled"' : '') +'>' +
                        '<a'+ (disabledPrevPage ? '' : ' href="'+ prevLink +'"') +'>上一页</a>' +
                    '</li>' +
                    '<li'+ (disabledNextPage ? ' class="disabled"' : '') +'>' +
                        '<a'+ (disabledNextPage ? '' : ' href="'+ nextLink +'"') +'>下一页</a>' +
                    '</li>' +
                '</ul>';
        } else {
            var interval = Math.ceil(displayPageNum / 2) - 1;

            //计算左边界
            var startPage = curPage - interval;
            if (startPage < 1) {
                startPage = 1;
            }
            //计算右边界
            var endPage = startPage + displayPageNum - 1;
            if (endPage > maxPage) {
                endPage = maxPage;
                if (startPage > 1) {
                    //重新计算左边界
                    startPage = endPage - displayPageNum + 1;
                    if (startPage < 1) {
                        startPage = 1;
                    }
                }
            }
            if (!disabledNextPage) {
                disabledNextPage = curPage == maxPage;
            }

            html = '<ul class="pager mt-pager">' +
                    '<li'+ (disabledPrevPage ? ' class="disabled"' : '') +'>' +
                        '<a'+ (disabledPrevPage ? '' : ' href="'+ prevLink +'"') +'>上一页</a>' +
                    '</li>';
            if (startPage > 1) {
                //首页
                params['page'] = 1;
                html += '<li><a href="'+ location.pathname + $.buildQuery(params) +'">1</a></li>';
                if (startPage > 2) {
                    html += '<li><a>...</a></li>';
                }
            }
            for (var i = startPage; i <= endPage; i++) {
                params['page'] = i;
                html += '<li'+ (i == curPage ? ' class="active"' : '') +'><a href="'+ location.pathname + $.buildQuery(params) +'">'+ i +'</a></li>';
            }
            if (endPage < maxPage) {
                //尾页
                if (endPage < maxPage - 1) {
                    html += '<li><a>...</a></li>';
                }
                params['page'] = maxPage;
                html += '<li><a href="'+ location.pathname + $.buildQuery(params) +'">'+ maxPage +'</a></li>';
            }
            html += '<li'+ (disabledNextPage ? ' class="disabled"' : '') +'>' +
                        '<a'+ (disabledNextPage ? '' : ' href="'+ nextLink +'"') +'>下一页</a>' +
                    '</li>' +
                '</ul>';
        }
        $this.html(html);
    };

    $.fn.myAjaxForm = function (options) {
        var defaults = {
            dataType: 'json',
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        alert('操作成功');
                        location.reload();
                    } else if (resp.meta.msg != undefined && resp.meta.msg) {
                        alert(resp.meta.msg);
                    } else {
                        alert('操作失败');
                    }
                } else {
                    alert('返回结果异常');
                }
            },
            error: function (resp) {
                alert('请求失败');
            }
        };
        var opts = $.extend(defaults, options);
        $(this).ajaxForm(opts);
    };
})(jQuery);


$.extend($.validator.messages, {
    required: "这是必填字段",
    remote: "请修正此字段",
    email: "请输入有效的电子邮件地址",
    url: "请输入有效的网址",
    date: "请输入有效的日期",
    dateISO: "请输入有效的日期 (YYYY-MM-DD)",
    number: "请输入有效的数字",
    digits: "只能输入数字",
    creditcard: "请输入有效的信用卡号码",
    equalTo: "两次输入值不同",
    extension: "请输入有效的后缀",
    maxlength: $.validator.format("最多可以输入 {0} 个字符"),
    minlength: $.validator.format("最少要输入 {0} 个字符"),
    rangelength: $.validator.format("请输入长度在 {0} 到 {1} 之间的字符串"),
    range: $.validator.format("请输入范围在 {0} 到 {1} 之间的数值"),
    max: $.validator.format("请输入不大于 {0} 的数值"),
    min: $.validator.format("请输入不小于 {0} 的数值")
});

// jquery.validate 添加自定义方法
// 金钱
$.validator.addMethod("money", function(value, element) {
    var money = /^\d+(\.\d{0,2})?$/;
    return money.test(value);
}, "请填写正确金额");
// 日期
$.validator.addMethod("Y-m-d H:i", function(value, element) {
    var formate = /^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}$/;
    return formate.test(value);
}, "日期格式错误");
//密码
$.validator.addMethod("password_formate", function(value, element) {
    if(!value){return true;}
    var reg=/^[0-9A-Za-z_]{6,15}$/;
    if(!reg.test(value)){return false;}
    if(/^\d{6,15}$/.test(value)){return false;}
    if(/^[a-zA-Z]{6,15}$/.test(value)){return false;}
    if(/^_{6,15}$/.test(value)){return false;}
    return true;
}, "6-15位数字、字母、下划线组成");
//手机号码
$.validator.addMethod("mobile_formate", function(value, element) {
    var formate = /^1[23456789]\d{9}$/;
    return formate.test(value);
}, "手机格式错误");
//参数
$.validator.addMethod("request_param", function(value, element) {
    var formate=/^[A-Za-z]+[A-Za-z0-9_]*$/;
    return formate.test(value);
}, "格式错误:数字、字母、下划线组成,只能以字母开头");
$.validator.addMethod("api_uri", function(value, element) {
    var formate=/^\/{1}[a-z]+\.php\/{1}[A-Za-z0-9_\/]*$/;
    if(!formate.test(value)){return false;}
    if(/\/{2,}/.test(value)){
        return false;
    }
    return true;
}, "请填写正确的uri路径");


//图片上传
function $upload(object,option) {
    if($("#upload_file_script_package").length<=0){
        var html='<div id="upload_file_script_package" style="height:0;width:0;margin:0;padding:0;display:none;"><link rel="stylesheet" href="/js/uploadify/css/uploadify.css"/><script src="/js/uploadify/jquery.uploadify.min.js"></script>';
        html+='<link rel="stylesheet" href="/js/uploadifive/uploadifive.css"/><script src="/js/uploadifive/jquery.uploadifive.min.js"></script></div>';
        $("body").append(html);
    }
    if(typeof option.height =="undefined"){
        option.height=30;
    }
    if(typeof option.width =="undefined"){
        option.width=90;
    }
    if(typeof option.text =="undefined"){
        option.text="上传图片";
    }
    if(typeof option.name =="undefined"){
        option.name="uploadify";
    }
    if(typeof option.class_name =="undefined"){
        option.class_name="";
    }
    if(typeof option.queue_id =="undefined"){
        option.queue_id="";
    }else{
        if($("#"+option.queue_id).length<=0){
            $("body").append("<div style='display:none;width:0;height:0' id='"+option.queue_id+"'></div>");
        }
    }
    if(typeof option.size =="undefined"){
        option.size="50MB";
    }
    if(typeof option.success =="undefined"){
        option.success=function(){};
    }
    if(typeof option.multi =="undefined"){
        option.multi=false;
    }
    var set_option={
        'auto' : true,
        'uploadScript' :option.url,
        'method':'post',
        'height':option.height,
        'width':option.width,
        'buttonText' : option.text,
        'buttonCursor': 'pointer',
        'removeCompleted': true,
        'fileObjName' : option.name,
        'fileType' : 'image/*',
        'buttonClass':option.class_name,
        'queueID' : option.queue_id,
        'multi' : option.multi,
        'fileSizeLimit'   : option.size,
        'onUploadComplete' : option.success,
        'onError':function (errorType) {
            alert('The error was: ' + errorType);
        }
    };
    object.uploadifive(set_option);
}



/**
 * 上传通用方法
 * @param object  上传的jquery input type=file 对象
 * @param type  上传的类型,audio|video|picture|avatar|comment_picture
 * @param success 成功回调方法
 * @param fail  失败回调方法
 */
function common_upload(object,type,success,fail){
    //样式修正和进度条
    if($(object).attr("id")){
        var for_id=$(object).attr("id");
    }else{
        var for_id="upload_temp_file"+Math.round(Math.random() * 10000000).toString();
        $(object).attr("id",for_id);
    }
    $(object).before('<label for="'+for_id+'" class="upload_new_btn_style glyphicon glyphicon-cloud-upload">选择上传</label><div id="percent_progress_'+for_id+'" class="percent_progress_new"><span class="percent_inner_span"></span><span class="percent_inner_span_tip"></span></div>');
    $(object).hide();
    var percent_progress=$("#percent_progress_"+for_id);
    //上传
    $(object).get(0).addEventListener('change', function () {
        var file = this.files[0];
        var data={};
        var ext=file.name.split(".");
        ext="."+ext[ext.length-1];
        var accept_set=$(object).attr("extension");
        if(typeof accept_set!="undefined" && accept_set!=""){
            var accept_set_arr = accept_set.split(","); //字符分割
            var check_ext=false;
            for(j=0;j<accept_set_arr.length;j++){
                if(ext==accept_set_arr[j]){
                    check_ext=true;
                    break;
                }
            }
            if(!check_ext){
                $alert("请选择对应类型的文件:"+accept_set);
                return false;
            }
        }
        var key_prefix='';
        var KeyPrefixInput=$("#key_prefix");
        if(KeyPrefixInput.length>0){
            key_prefix=KeyPrefixInput.val();
        }
        $.ajax({
            url:'/common/get_upload_token',
            type:'get',
            async:false,
            dataType:'json',
            data:{'type':type,'ext':ext,'key_prefix':key_prefix},
            success:function (result_data) {
                percent_progress.show();
                percent_progress.find(".percent_inner_span").css("width","0");
                data=result_data.response;
                //上返回的参数使用formData中
                let formData = new FormData();
                formData.append('key', data.key+ext);
                formData.append('OSSAccessKeyId', data.OSSAccessKeyId);
                formData.append('policy', data.policy);
                formData.append('Signature', data.signature);
                formData.append('callback', data.callback);
                formData.append('success_action_status', "200"); // 成功后返回的操作码
                formData.append('file', file);
                //接收到服务端返回的签名参数，开始通过另一个Ajax请求来上传文件到OSS
                //成功获取签名后上传文件到阿里云OSS
                $.ajax({
                    type : "POST", //提交方式
                    url : data.host,//路径
                    dataType:'XML',
                    processData: false,
                    cache: false,
                    async: false,
                    contentType: false,
                    //关键是要设置contentType 为false，不然发出的请求头 没有boundary
                    //该参数是让jQuery去判断contentType
                    data : formData,//要发送到OSS数据，使用我这个ajax的格式可避开跨域问题。
                    success : function(res2) {//返回数据根据结果进行相应的处理
                        if(typeof success!="undefined"){
                            var imgUrl=data.host+"/"+data.key+ext;
                            success(imgUrl,file.name);
                        }
                        percent_progress.hide();
                    },
                    error:function (err) {
                        percent_progress.hide();
                        if(typeof fail!="undefined"){
                            fail(err);
                        }else{
                            alert("上传出错");
                        }
                    }
                });
            }
        });
    });
}

/**
 * 上传图片
 * 调用方法  $("xxxx").image_upload(function(url,name){},function(error){});
 * @param success
 * @param fail
 */
$.fn.image_upload=function(success,fail){
    common_upload($(this),'picture',success,fail);
};
/**
 * 上传视频
 * 调用方法 $("xxxx").video_upload(function(url,name){},function(error){});
 * @param success
 * @param fail
 */
$.fn.video_upload=function(success,fail){
    common_upload($(this),'video',success,fail);
};
/**
 * 上传音频
 * 调用方法 $("xxxx").audio_upload(function(url,name){},function(error){});
 * @param success
 * @param fail
 */
$.fn.audio_upload=function(success,fail){
    common_upload($(this),'audio',success,fail);
};
/**
 * 上传头像
 * 调用方法 $("xxxx").avatar_upload(function(url,name){},function(error){});
 * @param success
 * @param fail
 */
$.fn.avatar_upload=function(success,fail){
    common_upload($(this),'avatar',success,fail);
};
/**
 * 上传评论图片
 * 调用方法 $("xxxx").comment_picture_upload(function(url,name){},function(error){});
 * @param success
 * @param fail
 */
$.fn.comment_picture_upload=function(success,fail){
    common_upload($(this),'comment_picture',success,fail);
};

//增加modal关闭按钮
$(document).on("click",".modal .modal_close",function(){
    $(this).closest(".modal").find(".close").trigger("click");
});

//alert 弹窗
var alert_callback='';
function $alert(msg,callback){
    var body_height=$("body").height();
    var window_height=$(window).height();
    if(body_height<window_height){
        body_height=window_height;
    }
    var html='<div style="width: 100%;height: '+body_height+'px;position: absolute;left: 0;top: 0;z-index: 1000;background: #000;opacity: 0.5;z-index: 1051" class="alert_layer_bg"></div>';
    html+='<div class="modal fade in alert_content" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;z-index: 1052;">';
    html+='<div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header">';
    html+='<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
    html+='<h4 class="modal-title" id="myModalLabel">警告</h4></div><div class="modal-body">';
    html+=msg+'</div><div class="modal-footer alert_content_btn"><button type="button" class="btn btn-primary">确认</button>';
    html+='</div></div></div></div>';
    $("body").append(html);
    alert_callback=callback;
}
$(document).on("click ",".alert_content .alert_content_btn button",function(){
    $(".alert_layer_bg").remove();
    $(".alert_content").remove();
    if(typeof alert_callback != "undefined" && alert_callback!=""){
        alert_callback();
    }
});
$(document).on("click",".alert_content .close",function(){
    $(".alert_layer_bg").remove();
    $(".alert_content").remove();
});
//confirm 确认框
var confirm_callback='';
var confirm_cancel_callback='';
function $confirm(msg,callback,cancel_callback){
    var body_height=$("body").height();
    var window_height=$(window).height();
    if(body_height<window_height){
        body_height=window_height;
    }
    var html='<div style="width: 100%;height: '+body_height+'px;position: absolute;left: 0;top: 0;z-index: 1050;background: #000;opacity: 0.5;" class="confirm_layer_bg"></div>';
    html+='<div class="modal fade in confirm_content" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">';
    html+='<div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header">';
    html+='<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
    html+='<h4 class="modal-title" id="myModalLabel">警告</h4></div><div class="modal-body">';
    html+=msg+'</div><div class="modal-footer confirm_content_btn"><button type="button" class="btn btn-default confirm_content_cancel" data-dismiss="modal">取消</button><button type="button" class="btn btn-primary">确认</button>';
    html+='</div></div></div></div>';
    $("body").append(html);
    confirm_callback=callback;
    confirm_cancel_callback=cancel_callback;
}
$(document).on("click ",".confirm_content .confirm_content_btn button",function(){
    $(".confirm_layer_bg").remove();
    $(".confirm_content").remove();
    if($(this).hasClass("confirm_content_cancel")){
        if(typeof confirm_cancel_callback!="undefined" && confirm_cancel_callback!=""){
            confirm_cancel_callback();
        }
    }else{
        if(typeof confirm_callback!="undefined" && confirm_callback!=""){
            confirm_callback();
        }
    }
});
$(document).on("click",".confirm_content .close",function(){
    $(".confirm_layer_bg").remove();
    $(".confirm_content").remove();
});