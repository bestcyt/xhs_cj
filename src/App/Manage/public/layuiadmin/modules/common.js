/** layuiAdmin.std-v1.4.0 LPPL License By https://www.layui.com/admin/ */
;layui.define(function (e) {
    var i = (layui.$, layui.layer, layui.laytpl, layui.setter, layui.view, layui.admin);
    i.events.logout = function () {
        // i.req({
        //     url: layui.setter.base + "json/user/logout.js", type: "get", data: {}, done: function (e) {
        //         i.exit(function () {
        //             // logout-url
        //             location.href = "/logout/index"
        //         })
        //     }
        // })
        location.href = layui.$("#logout_btn").attr("logout-url");
    }, e("common", {})
});