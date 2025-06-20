/** layuiAdmin.std-v1.4.0 LPPL License By https://www.layui.com/admin/ */
;layui.define(["form", "upload"], function (t) {
    var i = layui.$, e = layui.layer, a = (layui.laytpl, layui.setter, layui.view, layui.admin), n = layui.form,
        s = layui.upload;
    i("body");
    n.verify({
        nickname: function (t, i) {
            return new RegExp("^[a-zA-Z0-9_一-龥\\s·]+$").test(t) ? /(^\_)|(\__)|(\_+$)/.test(t) ? "用户名首尾不能出现下划线'_'" : /^\d+\d+\d$/.test(t) ? "用户名不能全为数字" : void 0 : "用户名不能有特殊字符"
        }, pass: [/^[\S]{6,12}$/, "密码必须6到12位，且不能出现空格"], repass: function (t) {
            if (t !== i("#LAY_password").val()) return "两次密码输入不一致"
        }
    }), n.on("submit(set_website)", function (t) {
        return e.msg(JSON.stringify(t.field)), !1
    }), n.on("submit(set_system_email)", function (t) {
        return e.msg(JSON.stringify(t.field)), !1
    }), n.on("submit(setmyinfo)", function (t) {
        return e.msg(JSON.stringify(t.field)), !1
    });
    var r = i("#LAY_avatarSrc");
    s.render({
        url: "/api/upload/", elem: "#LAY_avatarUpload", done: function (t) {
            0 == t.status ? r.val(t.url) : e.msg(t.msg, {icon: 5})
        }
    }), a.events.avartatPreview = function (t) {
        var i = r.val();
        e.photos({photos: {title: "查看头像", data: [{src: i}]}, shade: .01, closeBtn: 1, anim: 5})
    }, n.on("submit(setmypass)", function (t) {
        return e.msg(JSON.stringify(t.field)), !1
    }), t("set", {})
});