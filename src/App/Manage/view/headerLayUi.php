<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>小红书点赞</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css?122233" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css?1222222" media="all">
    <script src="/layuiadmin/layui/layui.js?123435622"></script>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="/js/jquery-1.11.1.min.js"></script>
    <script src="/js/qrcode.js"></script>
    <script type="text/javascript" charset="utf-8" src="/js/mouse.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/js/clipboard.js"></script>
    <script>
        //拓展表单验证
        layui.form.verify({
            //必须
            required: function (value, item) {
                //不可见
                if(item.offsetWidth <= 0 || item.offsetHeight <= 0){
                    return;
                }
                if (value === "") {
                    return "必填项不能为空";
                }
            },
            //金额
            money: function (value, item) {
                if (value === "") {
                    return;
                }
                var money = /^\d+(\.\d{0,2})?$/;
                if (!money.test(value)) {
                    return "请填写正确金额";
                }
            },
            moneyBothSide: function (value, item) {
                if (value === "") {
                    return;
                }
                var money = /^\-?\d+(\.\d{0,2})?$/;
                if (!money.test(value)) {
                    return "请填写正确金额";
                }
            },
            money5small: function (value, item) {
                if (value === "") {
                    return;
                }
                var money = /^\d+(\.\d{0,5})?$/;
                if (!money.test(value)) {
                    return "请填写正确金额";
                }
            },
            money5small2: function (value, item) {
                if (value === "") {
                    return;
                }
                var money = /^(\+|\-)?\d+(\.\d{0,5})?$/;
                if (!money.test(value)) {
                    return "请填写正确金额";
                }
            },
            //最小长度
            minlength: function (value, item) {
                if (value === "") {
                    return;
                }
                var min = parseInt(item.getAttribute('lay-minlength'));
                if (value.length < min) {
                    return '不能小于' + min + '个字符的长度';
                }
            },
            //最大长度
            maxlength: function (value, item) {
                if (value === "") {
                    return;
                }
                var min = parseInt(item.getAttribute('lay-maxlength'));
                if (value.length > min) {
                    return '不能大于' + min + '个字符的长度';
                }
            },
            //最小值
            min: function (value, item) {
                if (value === "") {
                    return;
                }
                var min = parseInt(item.getAttribute('lay-min'));
                if (value < min) {
                    return '不能小于' + min;
                }
            },
            //最大值
            max: function (value, item) {
                if (value === "") {
                    return;
                }
                var max = parseInt(item.getAttribute('lay-max'));
                if (value > max) {
                    return '不能大于' + max;
                }
            },
            //输入长度必须介于 5 和 10 之间的字符串（汉字算一个字符）
            rangelength: function (value, item) {
                if (value === "") {
                    return;
                }
                var rangelength = item.getAttribute('lay-rangelength');
                rangelength = rangelength.split(",");
                if (value.length < parseInt(rangelength[0])) {
                    return '输入长度必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
                }
                if (value.length > parseInt(rangelength[1])) {
                    return '输入长度必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
                }
            },
            //输入值必须介于 5 和 10 之间。
            range: function (value, item) {
                if (value === "") {
                    return;
                }
                value=parseInt(value);
                var rangelength = item.getAttribute('lay-range');
                rangelength = rangelength.split(",");
                if (value < parseInt(rangelength[0])) {
                    return '输入值必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
                }
                if (value > parseInt(rangelength[1])) {
                    return '输入值必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
                }
            },
            //密码
            password: function (value, item) {
                if (value === "") {
                    return;
                }
                var reg = /^[0-9A-Za-z_!@#\$\%\^\&\*\(\)]{6,15}$/;
                if (reg.test(value) && !/^\d{6,15}$/.test(value) && !/^[a-zA-Z]{6,15}$/.test(value) && !/^_{6,15}$/.test(value)) {
                    return;
                }
                return "6-15位数字、字母、_！@#￥%^&*（）组成";
            },
            readioCheckboxRequire: function(value,item){
                var $ = layui.$;
                var verifyName=$(item).attr('name')
                    , verifyType=$(item).attr('type')
                    ,formElem=$(item).parents('.layui-form')//获取当前所在的form元素，如果存在的话
//,verifyElem=formElem.find('input[name='+verifyName+']')//获取需要校验的元素
                    ,verifyElem=formElem.find("input[name='"+verifyName+"']")//获取需要校验的元素
                    ,isTrue= verifyElem.is(':checked')//是否命中校验
                    ,focusElem = verifyElem.next().find('i.layui-icon');//焦点元素
                if(!isTrue || !value){
                    //定位焦点
                    focusElem.css(verifyType=='radio'?{"color":"#FF5722"}:{"border-color":"#FF5722"});
                    //对非输入框设置焦点
                    focusElem.first().attr("tabIndex","1").css("outline","0").blur(function() {
                        focusElem.css(verifyType=='radio'?{"color":""}:{"border-color":""});
                    }).focus();
                    return '必填项不能为空';
                }
            },
            //小红书号
            red_id:function(value,item){
                if (value === "") {
                    return "小红书号不能为空";
                }
                var result="";
                $.ajax({
                    url:"/xiaohongshu/account/red_info",
                    type:"post",
                    dataType:"json",
                    data:{red_id:value},
                    async:false,
                    success:function (resp) {
                        if (resp.meta.code != undefined) {
                            if (resp.meta.code == 0) {
                                result="成功";
                            } else if (resp.meta.msg != undefined && resp.meta.msg) {
                                result=resp.meta.msg;
                            } else {
                                result="操作失败";
                            }
                        } else {
                            result="返回结果异常";
                        }
                    },
                    error:function(){
                        result="系统繁忙，稍后再试";
                    }
                });
                if(result=="成功"){
                    return;
                }
                return result;
            },
        });
        //输入框的最大长度
        $(document).on("input", "input[type='number']", function () {
            var maxlength = $(this).attr("maxlength");
            if (maxlength === undefined) {
                return;
            }
            maxlength = parseInt(maxlength);
            var value = $(this).val();
            if (value.length > maxlength) {
                $(this).val(value.slice(0, maxlength));
            }
        });
        //弹窗iframe
        $(document).on("click", ".data-remote", function (e) {
            e.stopPropagation();
            var url = $(this).attr("data-remote-url");
            var title = $(this).attr("data-remote-title");
            var full = $(this).attr("data-remote-full");
            var width = $(this).attr("data-remote-width");
            if(typeof width == undefined || width == "" || width == "undefined" || typeof width=="undefined"){
                width="500px";
            }
            if (typeof width!="undefined" && typeof full != undefined && full != "" && full != "undefined" && full == "true") {
                var index = layui.layer.open({
                    type: 2
                    , content: url
                    , title: title
                    , area: [width, "auto"]
                });
                layui.layer.full(index);
            }else{
                var index = layui.layer.open({
                    type: 2
                    , content: url
                    , title: title
                    , area: [width, "auto"]
                    , success: function(layero, index) {
                        //找到当前弹出层的iframe元素
                        var iframe = layui.$(layero).find('iframe');
                        //设定iframe的高度为当前iframe内body的高度
                        iframe.css('height', iframe[0].contentDocument.body.offsetHeight);
                        //重新调整弹出层的位置，保证弹出层在当前屏幕的中间位置
                        $(layero).css('top', (window.innerHeight - iframe[0].offsetHeight) / 2);
                    }
                });
            }
        });
        //查看图片
        $(document).on("click", ".image-show", function () {
            var url = $(this).attr("image-show-url");
            var title = $(this).attr("image-show-title");
            var rand_key = new Date().valueOf().toString() + "_" + Math.round(Math.random() * 80 + 200).toString();
            layui.layer.photos({
                photos: {
                    title: title,
                    id: rand_key,
                    start: 0,
                    data: [
                        {
                            src: url,
                            alt: title,
                        }
                    ]
                },
                shade: 0.3,
                closeBtn: 0,
                anim: 5
            })
        });
        //上传
        $.fn.upload = function (optionsExt, success, fail, preview) {
            var id = $(this).attr("id");
            if (typeof(id) === "undefined" || id === undefined || id === "") {
                var rand_key = new Date().valueOf().toString() + "_" + Math.round(Math.random() * 80 + 200).toString();
                id = "upload_" + rand_key;
                $(this).attr("id", id);
            }
            var upload_data = null;
            var ext = "";
            var currFile = null;
            if (optionsExt !== Object(optionsExt)) {
                preview = fail;
                fail = success;
                success = optionsExt;
                optionsExt = {};
            }
            var options = {
                elem: '#' + id //绑定元素
                , url: "" //上传接口
                , method: 'post'
                , dataType: 'XML'
                , processData: false
                , cache: false
                , async: false
                , contentType: false
                , data: {}
                , before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    var loadingIndex = layui.layer.load();
                    obj.preview(function (index, file, result) {
                        var extT = file.name.split(".");
                        ext = "." + extT[extT.length - 1];
                        currFile = file;
                        $.ajax({
                            url: '<?php echo U("/common/get_upload_token",[]);?>',
                            type: 'get',
                            async: false,
                            dataType: 'json',
                            data: {},
                            success: function (result_data) {
                                upload_data = result_data.response;
                                //上返回的参数使用formData中
                                let formData = new FormData();
                                formData.append('key', upload_data.key + ext);
                                formData.append('OSSAccessKeyId', upload_data.OSSAccessKeyId);
                                formData.append('policy', upload_data.policy);
                                formData.append('Signature', upload_data.signature);
                                formData.append('callback', upload_data.callback);
                                formData.append('success_action_status', "200"); // 成功后返回的操作码
                                formData.append('file', file);
                                //接收到服务端返回的签名参数，开始通过另一个Ajax请求来上传文件到OSS
                                //成功获取签名后上传文件到阿里云OSS
                                $.ajax({
                                    type: "POST", //提交方式
                                    url: upload_data.host,//路径
                                    dataType: 'XML',
                                    processData: false,
                                    cache: false,
                                    async: false,
                                    contentType: false,
                                    //关键是要设置contentType 为false，不然发出的请求头 没有boundary
                                    //该参数是让jQuery去判断contentType
                                    data: formData,//要发送到OSS数据，使用我这个ajax的格式可避开跨域问题。
                                    success: function (res2) {//返回数据根据结果进行相应的处理
                                        if (typeof success != "undefined" && success !== null) {
                                            var imgUrl = upload_data.host + "/" + upload_data.key + ext;
                                            success(imgUrl, file.name);
                                        }
                                        layui.layer.close(loadingIndex);
                                    },
                                    error: function (err) {
                                        layui.layer.close(loadingIndex);
                                        if (typeof fail != "undefined" && fail !== null) {
                                            fail(err);
                                        } else {
                                            layui.layer.msg('上传出错');
                                        }
                                    }
                                });
                            }
                        });

                        // $('#demo1').attr('src', result); //图片链接（base64）
                        if (typeof preview !== "undefined" && preview !== null) {
                            preview(result);
                        }
                    });
                }
                , done: function (res, index, upload) {
                    // if (typeof success !== "undefined" && success !== null) {
                    //     var imgUrl = upload_data.host + "/" + upload_data.key + ext;
                    //     success(imgUrl, currFile.name);
                    // }
                }
                , error: function () {
                    if (typeof(fail) !== "undefined" && fail !== null) {
                        fail();
                    } else {
                        return layui.layer.msg('error');
                    }
                }
            };
            options = Object.assign({}, options, optionsExt);
            layui.upload.render(options);
        };
        //ajax 表单提交
        $.fn.myAjaxForm = function (options) {
            if(typeof options =="undefined" || options==undefined || typeof options ==undefined){
                options={};
            }
            var thisForm = $(this);
            var laySubmit = thisForm.find("[lay-submit]");
            if (laySubmit.length < 1) {
                return false;
            }
            //必须加上 layui-form
            if(!thisForm.hasClass("layui-form")){
                thisForm.addClass("layui-form");
            }
            var id = $(laySubmit).attr("lay-filter");
            if (typeof id === "undefined" || id === undefined || id === "") {
                var rand_key = new Date().valueOf().toString() + "_" + Math.round(Math.random() * 80 + 200).toString();
                id = "form_" + rand_key;
                $(laySubmit).attr("lay-filter", id);
            }
            layui.form.on('submit(' + id + ')', function (obj) {
                //是否有富文本编辑框
                if(options.hasOwnProperty("editor")){
                    for(var editorId in options.editor){
                        layui.layedit.sync(options.editor[editorId]);
                    }
                }
                var defaults = {
                    url: thisForm.attr("action"),
                    type: thisForm.attr("method") ? thisForm.attr("method") : "post",
                    dataType: 'json',
                    async: false,
                    data: thisForm.serialize(),
                    success: function (resp) {
                        if (resp.meta.code != undefined) {
                            if (resp.meta.code == 0) {
                                layui.layer.alert("操作成功", function () {
                                    if (window.self !== window.top && window.parent !== window.top) {
                                        parent.location.reload();
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            } else if (resp.meta.msg != undefined && resp.meta.msg) {
                                layui.layer.alert(resp.meta.msg);
                            } else {
                                layui.layer.alert("操作失败");
                            }
                        } else {
                            layui.layer.alert("返回结果异常");
                        }
                    },
                    error: function (resp) {
                        layui.layer.alert('请求失败');
                    }
                };
                var opts = $.extend(defaults, options);
                $.ajax(opts);
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });
        };
        var ajaxForm=function (method, url, params, options) {
            options = options || {};
            var defaults = {
                dataType: 'json',
                success: function (resp) {
                    if (resp.meta.code != undefined) {
                        if (resp.meta.code == 0) {
                            layui.layer.alert("操作成功", function () {
                                if (window.self !== window.top && window.parent !== window.top) {
                                    parent.location.reload();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else if (resp.meta.msg != undefined && resp.meta.msg) {
                            layui.layer.alert(resp.meta.msg);
                        } else {
                            layui.layer.alert("操作失败");
                        }
                    } else {
                        layui.layer.alert("返回结果异常");
                    }
                },
                error: function (resp) {
                    layui.layer.alert('请求失败');
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
        };
        $.fn.myAjaxGet=function (url, params, options) {
            ajaxForm('get', url, params, options);
        };
        $.fn.myAjaxPost=function (url, params, options) {
            ajaxForm('post', url, params, options);
        };
        $.myAjaxGet=function (url, params, options) {
            ajaxForm('get', url, params, options);
        };
        $.myAjaxPost=function (url, params, options) {
            ajaxForm('post', url, params, options);
        };
        if (window.self !== window.top && window.parent === window.top) {
            $(function () {
                //鼠标特效
                // $.shuicheMouse({
                //     type: 11,
                //     color: "rgba(187,67,128,1)"
                // });
            })
        }
    </script>
    <script>
        $(function () {
            function clipboardInitFun(obj){
                var content = $(obj).attr("clipboard-data");
                var id = $(obj).attr("id");
                if (typeof(id) === "undefined" || id === undefined || id === "") {
                    var rand_key = new Date().valueOf().toString() + "_" + Math.round(Math.random() * 80 + 200).toString();
                    id = "clipboard_" + rand_key;
                    $(obj).attr("id", id);
                }
                $(obj).addClass("clipboardInit");
                var clipboard = new ClipboardJS("#" + id, {
                    text: function () {
                        return content;
                    }
                });
                /*复制成功*/
                clipboard.on('success', function (e) {
                    layui.layer.msg("复制成功");
                });
                /*复制出现失败的情况下*/
                clipboard.on('error', function (e) {
                    console.log(e);
                    layui.layer.msg("复制失败");
                });
            }
            //复制到剪切板
            $(".clipboard").each(function () {
                clipboardInitFun($(this));
            });
            $(document).on("mouseover",".clipboard",function () {
                if($(this).hasClass("clipboardInit")){
                    return;
                }
                clipboardInitFun($(this));
            });
        });
    </script>
    <script>
        //鼠标悬停提示
        var tips;
        $(document).on("mouseenter",".layui_tips",function () {
            var that = this;
            var tipsHtmlElem=$(this).find(".lay_tips_content");
            if(tipsHtmlElem.length>0){
                var content=tipsHtmlElem.html();
            }else{
                var content=$(this).attr("lay_tips_content");
            }
            tips = layer.tips(content, that, { tips: [2, '#000'], time: 0, area: 'auto', maxWidth: 500 });
        });
        $(document).on("mouseleave",".layui_tips",function () {
            layui.layer.close(tips);
        });
    </script>
    <script>
        //layui表格 单元格合并 https://blog.csdn.net/Benxiaohai_311/article/details/126589396
        //使用方法
        //zai table.render 里面配置 done:function(){} 调用 layuiRowspan
        // done: function () {
        //     /**
        //      * 参数1  需要合并列的数组，0代表的是合并单选按钮或者是复选框 radio/checkbox 后端需要将这些字段进行排序
        //      * 参数2  当前表格的index (用于在一个页面中有使用了多个表格的情况，如果只有一个表格填0就行
        //      * 		  index这个值是表格加载的顺序，从0开始，如果页面上表格很多建议debug用 $(".layui-table-body")[index]看一下就拿到了)
        //      * 参数3  是按照html元素合并，还是按照标签中的text内容合并，建议填true
        //      * 参数4  radio/checkbox根据哪一行去合并
        //      */
        //     layuiRowspan(['0', 'location3', 'province3', 'prople3'], "2", true, "location3");
        // }
        //行合并 start
        function execRadioRows(childFilterArr, ckChildFilterArr, flag) {
            //获取td的个数和种类
            var chChildFilterTextObj = {};
            var chText = [];
            var chIndex = [];

            for (var i = 0; i < ckChildFilterArr.length; i++) {
                var chChildText = flag ? ckChildFilterArr[i].innerHTML : ckChildFilterArr[i].textContent;
                if (chChildFilterTextObj[chChildText] == undefined) {
                    chChildFilterTextObj[chChildText] = 1;
                    chText.push(chChildText);
                } else {
                    var num = chChildFilterTextObj[chChildText];
                    chChildFilterTextObj[chChildText] = num * 1 + 1;
                }
            }
            for (var i = 0; i < chText.length; i++) {
                var chNum = 0;
                for (var j = 0; j < ckChildFilterArr.length; j++) {
                    var chChildText = flag ? ckChildFilterArr[j].innerHTML : ckChildFilterArr[j].textContent;
                    if (chText[i] == chChildText) {
                        chNum = chNum + 1
                    }
                }
                chIndex.push(chNum);
            }
            var newIndex = [];
            for (var i = 0; i < chIndex.length; i++) {
                if (i == 0) {
                    newIndex.push(0);
                } else {
                    var newNum = 0;
                    for (var j = 0; j < chIndex.length; j++) {
                        if (j < i) {
                            newNum = newNum + chIndex[j];
                        }
                    }
                    newIndex.push(newNum);
                }
            }
            chIndex = newIndex;

            for (var j = 0; j < childFilterArr.length; j++) {
                var findFlag = false;
                for (var k = 0; k < chIndex.length; k++) {
                    if (j == chIndex[k]) {
                        findFlag = true;
                        if (chIndex[k + 1] != null) {
                            childFilterArr[j].setAttribute("rowspan", chIndex[k + 1] - j);
                            $(childFilterArr[j]).find("div.rowspan").parent("div.layui-table-cell").addClass("rowspanParent");
                            $(childFilterArr[j]).find("div.layui-table-cell")[0].style.height = (chIndex[k + 1] - j) * 38 - 10 + "px";

                        } else {
                            childFilterArr[j].setAttribute("rowspan", childFilterArr.length - j);
                            $(childFilterArr[j]).find("div.rowspan").parent("div.layui-table-cell").addClass("rowspanParent");
                            $(childFilterArr[j]).find("div.layui-table-cell")[0].style.height = (childFilterArr.length - j) * 38 - 10 + "px";
                        }
                    }
                }
                if (findFlag == false) {
                    childFilterArr[j].style.display = "none";
                }
            }
        }

        function execRowspan(fieldName, index, flag, ckRows) {
            var fixedNode = $(".layui-table-main")[index];
            var child = $(fixedNode).find("td");
            var childFilterArr = [];
            for (var j = 0; j < child.length; j++) {
                child[j].getAttribute('data-field')
                if (child[j].getAttribute("data-field") == fieldName) {
                    childFilterArr.push(child[j]);
                }
            }

            var ckChildFilterArr = [];
            if (fieldName == "0") {
                for (var j = 0; j < child.length; j++) {
                    child[j].getAttribute('data-field')
                    if (child[j].getAttribute("data-field") == ckRows) {
                        ckChildFilterArr.push(child[j]);
                    }
                }
                execRadioRows(childFilterArr, ckChildFilterArr, flag);
                return;
            }

            //获取td的个数和种类
            var childFilterTextObj = {};
            for (var i = 0; i < childFilterArr.length; i++) {
                var childText = flag ? childFilterArr[i].innerHTML : childFilterArr[i].textContent;
                if (childFilterTextObj[childText] == undefined) {
                    childFilterTextObj[childText] = 1;
                } else {
                    var num = childFilterTextObj[childText];
                    childFilterTextObj[childText] = num * 1 + 1;
                }
            }
            var canRowspan = true;
            var maxNum;//以前列单元格为基础获取的最大合并数
            var finalNextIndex;//获取其下第一个不合并单元格的index
            var finalNextKey;//获取其下第一个不合并单元格的值
            for (var i = 0; i < childFilterArr.length; i++) {
                (maxNum > 9000 || !maxNum) && (maxNum = $(childFilterArr[i]).prev().attr("rowspan") && fieldName != "8" ? $(childFilterArr[i]).prev().attr("rowspan") : 9999);
                var key = flag ? childFilterArr[i].innerHTML : childFilterArr[i].textContent;//获取下一个单元格的值
                var nextIndex = i + 1;
                var tdNum = childFilterTextObj[key];
                var curNum = maxNum < tdNum ? maxNum : tdNum;
                if (canRowspan) {
                    for (var j = 1; j <= curNum && (i + j < childFilterArr.length);) {//循环获取最终合并数及finalNext的index和key
                        finalNextKey = flag ? childFilterArr[i + j].innerHTML : childFilterArr[i + j].textContent;
                        finalNextIndex = i + j;
                        if ((key != finalNextKey && curNum > 1) || maxNum == j) {
                            canRowspan = true;
                            curNum = j;
                            break;
                        }
                        j++;
                        if ((i + j) == childFilterArr.length) {
                            finalNextKey = undefined;
                            finalNextIndex = i + j;
                            break;
                        }
                    }
                    childFilterArr[i].setAttribute("rowspan", curNum);
                    if ($(childFilterArr[i]).find("div.rowspan").length > 0) {//设置td内的div.rowspan高度适应合并后的高度
                        $(childFilterArr[i]).find("div.rowspan").parent("div.layui-table-cell").addClass("rowspanParent");
                        $(childFilterArr[i]).find("div.layui-table-cell")[0].style.height = curNum * 38 - 10 + "px";
                    }
                    canRowspan = false;
                } else {
                    childFilterArr[i].style.display = "none";
                }
                if (--childFilterTextObj[key] == 0 | --maxNum == 0 | --curNum == 0 | (finalNextKey != undefined && nextIndex == finalNextIndex)) {
                    canRowspan = true;
                }
            }
        }

        function layuiRowspan(fieldNameTmp, index, flag, ckRows) {
            var fieldName = [];
            if (typeof fieldNameTmp == "string") {
                fieldName.push(fieldNameTmp);
            } else {
                fieldName = fieldName.concat(fieldNameTmp);
            }
            for (var i = 0; i < fieldName.length; i++) {
                execRowspan(fieldName[i], index, flag, ckRows);
            }
        }
        function layuiRowspanFixedHandle(){
            //合并表格数据后，改变固定列的高度
            $(".layui-table-box").each(function () {
                var table_main=$(this).find(".layui-table-main");
                var table_fixed=$(this).find(".layui-table-fixed");
                var tr_index=0;
                table_main.find("tr").each(function(){
                    var td_temp=$(this).find("td");
                    td_temp.each(function () {
                        var field_temp=$(this).attr("data-field");
                        var rowspan_temp=$(this).attr("rowspan");
                        var style=$(this).attr("style");
                        var temp_height=$(this).height();
                        table_fixed.each(function () {
                            var temp_fixed_tr=$(this).find(".layui-table-body").find("tr").eq(tr_index);
                            temp_fixed_tr.find("td").each(function(){
                                var field_temp_fixed=$(this).attr("data-field");
                                if(field_temp_fixed==field_temp){
                                    if(style=="display: none;"){
                                        $(this).attr("style","display:none;");
                                    }else{
                                        $(this).attr("rowspan",rowspan_temp).height(temp_height);
                                    }
                                }
                            });
                        });
                    });
                    tr_index++;
                });
            });
        }
        //行合并 end
    </script>
    <script>
        //下载
        $(document).on("click",".download_btn",function(){
            var form = $(this).closest("form");
            if (form.length < 1) {
                return;
            }
            var export_data=form.find(".export_data");
            if (export_data.length < 1) {
                form.append("<input type='hidden' name='export_data' class='export_data' value='1'>");
            }else{
                export_data.val("1");
            }
            var target = form.attr("target");
            form.attr("target",'_blank');
            form.submit();
            form.find(".export_data").val("0");
            form.attr("target",target);
        });
    </script>
</head>
<body>
<div class="layui-fluid">