/** layuiAdmin.std-v1.4.0 LPPL License By https://www.layui.com/admin/ */
;layui.define(["laytpl", "layer"], function (e) {
    var t = layui.jquery, a = layui.laytpl, n = layui.layer, r = layui.setter, o = (layui.device(), layui.hint()),
        i = function (e) {
            return new d(e)
        }, s = "LAY_app_body", d = function (e) {
            this.id = e, this.container = t("#" + (e || s))
        };
    i.loading = function (e) {
        e.append(this.elemLoad = t('<i class="layui-anim layui-anim-rotate layui-anim-loop layui-icon layui-icon-loading layadmin-loading"></i>'))
    }, i.removeLoad = function () {
        this.elemLoad && this.elemLoad.remove()
    }, i.exit = function (e) {
        layui.data(r.tableName, {key: r.request.tokenName, remove: !0}), e && e()
    }, i.req = function (e) {
        var a = e.success, n = e.error, o = r.request, s = r.response, d = function () {
            return r.debug ? "<br><cite>URL：</cite>" + e.url : ""
        };
        if (e.data = e.data || {}, e.headers = e.headers || {}, o.tokenName) {
            var l = "string" == typeof e.data ? JSON.parse(e.data) : e.data;
            e.data[o.tokenName] = o.tokenName in l ? e.data[o.tokenName] : layui.data(r.tableName)[o.tokenName] || "", e.headers[o.tokenName] = o.tokenName in e.headers ? e.headers[o.tokenName] : layui.data(r.tableName)[o.tokenName] || ""
        }
        return delete e.success, delete e.error, t.ajax(t.extend({
            type: "get", dataType: "json", success: function (t) {
                if (t.hasOwnProperty("meta") && t.hasOwnProperty("response")) {
                    if (t.meta.code != undefined) {
                        if (t.meta.code == 0) {
                            if ("function" == typeof e.done) {
                                e.done(t);
                            } else {
                                i.alert("操作成功", function () {
                                    location.reload();
                                });
                            }
                        } else if (t.meta.msg != undefined && t.meta.msg) {
                            // var r = ["<cite>Error：</cite> " + t.meta.msg, d()].join("");
                            var r = t.meta.msg;
                            i.error(r)
                            //登录页面
                            if (t.meta.code == 4001) {
                                document.getElementById("LAY-user-login-vercode").value="";
                                document.getElementById("LAY-user-get-vercode").click();
                            }
                        } else {
                            // var r = ["<cite>Error：</cite> " + "操作失败", d()].join("");
                            i.error("操作失败")
                        }
                    } else {
                        var r = ["<cite>Error：</cite> " + "返回结果异常", d()].join("");
                        i.error(r)
                    }
                } else {
                    var n = s.statusCode;
                    if (t[s.statusName] == n.ok) "function" == typeof e.done && e.done(t); else if (t[s.statusName] == n.logout) i.exit(); else {
                        var r = ["<cite>Error：</cite> " + (t[s.msgName] || "返回状态码异常"), d()].join("");
                        i.error(r)
                    }
                    "function" == typeof a && a(t)
                }
            }, error: function (e, t) {
                var a = ["请求异常，请重试<br><cite>错误信息：</cite>" + t, d()].join("");
                i.error(a), "function" == typeof n && n(res)
            }
        }, e))
    }, i.popup = function (e) {
        var a = e.success, r = e.skin;
        return delete e.success, delete e.skin, n.open(t.extend({
            type: 1,
            title: "提示",
            content: "",
            id: "LAY-system-view-popup",
            skin: "layui-layer-admin" + (r ? " " + r : ""),
            shadeClose: !0,
            closeBtn: !1,
            success: function (e, r) {
                var o = t('<i class="layui-icon" close>&#x1006;</i>');
                e.append(o), o.on("click", function () {
                    n.close(r)
                }), "function" == typeof a && a.apply(this, arguments)
            }
        }, e))
    }, i.error = function (e, a) {
        return i.popup(t.extend({content: e, maxWidth: 300, offset: "t", anim: 6, id: "LAY_adminError"}, a))
    }, d.prototype.render = function (e, a) {
        var n = this;
        layui.router();
        return e = r.views + e + r.engine, t("#" + s).children(".layadmin-loading").remove(), i.loading(n.container), t.ajax({
            url: e,
            type: "get",
            dataType: "html",
            data: {v: layui.cache.version},
            success: function (e) {
                e = "<div>" + e + "</div>";
                var r = t(e).find("title"), o = r.text() || (e.match(/\<title\>([\s\S]*)\<\/title>/) || [])[1],
                    s = {title: o, body: e};
                r.remove(), n.params = a || {}, n.then && (n.then(s), delete n.then), n.parse(e), i.removeLoad(), n.done && (n.done(s), delete n.done)
            },
            error: function (e) {
                return i.removeLoad(), n.render.isError ? i.error("请求视图文件异常，状态：" + e.status) : (404 === e.status ? n.render("template/tips/404") : n.render("template/tips/error"), void(n.render.isError = !0))
            }
        }), n
    }, d.prototype.parse = function (e, n, r) {
        var s = this, d = "object" == typeof e, l = d ? e : t(e), u = d ? e : l.find("*[template]"), c = function (e) {
            var n = a(e.dataElem.html()), o = t.extend({params: p.params}, e.res);
            e.dataElem.after(n.render(o)), "function" == typeof r && r();
            try {
                e.done && new Function("d", e.done)(o)
            } catch (i) {
                console.error(e.dataElem[0], "\n存在错误回调脚本\n\n", i)
            }
        }, p = layui.router();
        l.find("title").remove(), s.container[n ? "after" : "html"](l.children()), p.params = s.params || {};
        for (var y = u.length; y > 0; y--) !function () {
            var e = u.eq(y - 1), t = e.attr("lay-done") || e.attr("lay-then"), n = a(e.attr("lay-url") || "").render(p),
                r = a(e.attr("lay-data") || "").render(p), s = a(e.attr("lay-headers") || "").render(p);
            try {
                r = new Function("return " + r + ";")()
            } catch (d) {
                o.error("lay-data: " + d.message), r = {}
            }
            try {
                s = new Function("return " + s + ";")()
            } catch (d) {
                o.error("lay-headers: " + d.message), s = s || {}
            }
            n ? i.req({
                type: e.attr("lay-type") || "get",
                url: n,
                data: r,
                dataType: "json",
                headers: s,
                success: function (a) {
                    c({dataElem: e, res: a, done: t})
                }
            }) : c({dataElem: e, done: t})
        }();
        return s
    }, d.prototype.autoRender = function (e, a) {
        var n = this;
        t(e || "body").find("*[template]").each(function (e, a) {
            var r = t(this);
            n.container = r, n.parse(r, "refresh")
        })
    }, d.prototype.send = function (e, t) {
        var n = a(e || this.container.html()).render(t || {});
        return this.container.html(n), this
    }, d.prototype.refresh = function (e) {
        var t = this, a = t.container.next(), n = a.attr("lay-templateid");
        return t.id != n ? t : (t.parse(t.container, "refresh", function () {
            t.container.siblings('[lay-templateid="' + t.id + '"]:last').remove(), "function" == typeof e && e()
        }), t)
    }, d.prototype.then = function (e) {
        return this.then = e, this
    }, d.prototype.done = function (e) {
        return this.done = e, this
    }, e("view", i)
});