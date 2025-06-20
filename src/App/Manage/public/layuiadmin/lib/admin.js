/** layuiAdmin.std-v1.4.0 LPPL License By https://www.layui.com/admin/ */
;layui.define("view", function (e) {
    var a = layui.jquery, t = layui.laytpl, i = layui.element, n = layui.setter, l = layui.view, s = layui.device(),
        r = a(window), o = a("body"), u = a("#" + n.container), d = "layui-show", c = "layui-hide", y = "layui-this",
        f = "layui-disabled", m = "#LAY_app_body", h = "LAY_app_flexible", p = "layadmin-layout-tabs",
        v = "layadmin-side-spread-sm", b = "layadmin-tabsbody-item", g = "layui-icon-shrink-right",
        x = "layui-icon-spread-left", C = "layadmin-side-shrink", k = "LAY-system-side-menu", P = {
            v: "1.4.0 std", req: l.req, exit: l.exit, escape: function (e) {
                return String(e || "").replace(/&(?!#?[a-zA-Z0-9]+;)/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/'/g, "&#39;").replace(/"/g, "&quot;")
            }, on: function (e, a) {
                return layui.onevent.call(this, n.MOD_NAME, e, a)
            }, sendAuthCode: function (e) {
                e = a.extend({seconds: 60, elemPhone: "#LAY_phone", elemVercode: "#LAY_vercode"}, e);
                var t, i = e.seconds, n = a(e.elem), l = function (a) {
                    i--, i < 0 ? (n.removeClass(f).html("获取验证码"), i = e.seconds, clearInterval(t)) : n.addClass(f).html(i + "秒后重获"), a || (t = setInterval(function () {
                        l(!0)
                    }, 1e3))
                };
                e.elemPhone = a(e.elemPhone), e.elemVercode = a(e.elemVercode), n.on("click", function () {
                    var t = e.elemPhone, n = t.val();
                    if (i === e.seconds && !a(this).hasClass(f)) {
                        if (!/^1\d{10}$/.test(n)) return t.focus(), layer.msg("请输入正确的手机号");
                        if ("object" == typeof e.ajax) {
                            var s = e.ajax.success;
                            delete e.ajax.success
                        }
                        P.req(a.extend(!0, {
                            url: "/auth/code", type: "get", data: {phone: n}, success: function (a) {
                                layer.msg("验证码已发送至你的手机，请注意查收", {
                                    icon: 1,
                                    shade: 0
                                }), e.elemVercode.focus(), l(), s && s(a)
                            }
                        }, e.ajax))
                    }
                })
            }, screen: function () {
                var e = r.width();
                return e > 1200 ? 3 : e > 992 ? 2 : e > 768 ? 1 : 0
            }, sideFlexible: function (e) {
                var t = u, i = a("#" + h), l = P.screen();
                "spread" === e ? (i.removeClass(x).addClass(g), l < 2 ? t.addClass(v) : t.removeClass(v), t.removeClass(C)) : (i.removeClass(g).addClass(x), l < 2 ? t.removeClass(C) : t.addClass(C), t.removeClass(v)), layui.event.call(this, n.MOD_NAME, "side({*})", {status: e})
            }, popup: l.popup, popupRight: function (e) {
                return P.popup.index = layer.open(a.extend({
                    type: 1,
                    id: "LAY_adminPopupR",
                    anim: -1,
                    title: !1,
                    closeBtn: !1,
                    offset: "r",
                    shade: .1,
                    shadeClose: !0,
                    skin: "layui-anim layui-anim-rl layui-layer-adminRight",
                    area: "300px"
                }, e))
            }, theme: function (e) {
                var i = (n.theme, layui.data(n.tableName)), l = "LAY_layadmin_theme", s = document.createElement("style"),
                    r = t([".layui-side-menu,", ".layadmin-pagetabs .layui-tab-title li:after,", ".layadmin-pagetabs .layui-tab-title li.layui-this:after,", ".layui-layer-admin .layui-layer-title,", ".layadmin-side-shrink .layui-side-menu .layui-nav>.layui-nav-item>.layui-nav-child", "{background-color:{{d.color.main}} !important;}", ".layui-nav-tree .layui-this,", ".layui-nav-tree .layui-this>a,", ".layui-nav-tree .layui-nav-child dd.layui-this,", ".layui-nav-tree .layui-nav-child dd.layui-this a", "{background-color:{{d.color.selected}} !important;}", ".layui-layout-admin .layui-logo{background-color:{{d.color.logo || d.color.main}} !important;}", "{{# if(d.color.header){ }}", ".layui-layout-admin .layui-header{background-color:{{ d.color.header }};}", ".layui-layout-admin .layui-header a,", ".layui-layout-admin .layui-header a cite{color: #f8f8f8;}", ".layui-layout-admin .layui-header a:hover{color: #fff;}", ".layui-layout-admin .layui-header .layui-nav .layui-nav-more{border-top-color: #fbfbfb;}", ".layui-layout-admin .layui-header .layui-nav .layui-nav-mored{border-color: transparent; border-bottom-color: #fbfbfb;}", ".layui-layout-admin .layui-header .layui-nav .layui-this:after, .layui-layout-admin .layui-header .layui-nav-bar{background-color: #fff; background-color: rgba(255,255,255,.5);}", ".layadmin-pagetabs .layui-tab-title li:after{display: none;}", "{{# } }}"].join("")).render(e = a.extend({}, i.theme, e)),
                    u = document.getElementById(l);
                "styleSheet" in s ? (s.setAttribute("type", "text/css"), s.styleSheet.cssText = r) : s.innerHTML = r, s.id = l, u && o[0].removeChild(u), o[0].appendChild(s), o.attr("layadmin-themealias", e.color.alias), i.theme = i.theme || {}, layui.each(e, function (e, a) {
                    i.theme[e] = a
                }), layui.data(n.tableName, {key: "theme", value: i.theme})
            }, initTheme: function (e) {
                var a = n.theme;
                e = e || 0, a.color[e] && (a.color[e].index = e, P.theme({color: a.color[e]}))
            }, tabsPage: {}, tabsBody: function (e) {
                return a(m).find("." + b).eq(e || 0)
            }, tabsBodyChange: function (e, a) {
                a = a || {}, P.tabsBody(e).addClass(d).siblings().removeClass(d), F.rollPage("auto", e), layui.event.call(this, n.MOD_NAME, "tabsPage({*})", {
                    url: a.url,
                    text: a.text
                })
            }, resize: function (e) {
                var a = layui.router(), t = a.path.join("-");
                P.resizeFn[t] && (r.off("resize", P.resizeFn[t]), delete P.resizeFn[t]), "off" !== e && (e(), P.resizeFn[t] = e, r.on("resize", P.resizeFn[t]))
            }, resizeFn: {}, runResize: function () {
                var e = layui.router(), a = e.path.join("-");
                P.resizeFn[a] && P.resizeFn[a]()
            }, delResize: function () {
                this.resize("off")
            }, closeThisTabs: function () {
                P.tabsPage.index && a(_).eq(P.tabsPage.index).find(".layui-tab-close").trigger("click")
            }, fullScreen: function () {
                var e = document.documentElement,
                    a = e.requestFullScreen || e.webkitRequestFullScreen || e.mozRequestFullScreen || e.msRequestFullscreen;
                "undefined" != typeof a && a && a.call(e)
            }, exitScreen: function () {
                document.documentElement;
                document.exitFullscreen ? document.exitFullscreen() : document.mozCancelFullScreen ? document.mozCancelFullScreen() : document.webkitCancelFullScreen ? document.webkitCancelFullScreen() : document.msExitFullscreen && document.msExitFullscreen()
            }
        }, F = P.events = {
            flexible: function (e) {
                var a = e.find("#" + h), t = a.hasClass(x);
                P.sideFlexible(t ? "spread" : null)
            }, refresh: function () {
                var e = ".layadmin-iframe", t = a("." + b).length;
                P.tabsPage.index >= t && (P.tabsPage.index = t - 1);
                var i = P.tabsBody(P.tabsPage.index).find(e);
                i[0].contentWindow.location.reload(!0)
            }, serach: function (e) {
                e.off("keypress").on("keypress", function (a) {
                    if (this.value.replace(/\s/g, "") && 13 === a.keyCode) {
                        var t = e.attr("lay-action"), i = e.attr("lay-text") || "搜索";
                        t += this.value, i = i + ' <span style="color: #FF5722;">' + P.escape(this.value) + "</span>", layui.index.openTabsPage(t, i), F.serach.keys || (F.serach.keys = {}), F.serach.keys[P.tabsPage.index] = this.value, this.value === F.serach.keys[P.tabsPage.index] && F.refresh(e), this.value = ""
                    }
                })
            }, message: function (e) {
                e.find(".layui-badge-dot").remove()
            }, theme: function () {
                P.popupRight({
                    id: "LAY_adminPopupTheme", success: function () {
                        l(this.id).render("system/theme")
                    }
                })
            }, note: function (e) {
                var a = P.screen() < 2, t = layui.data(n.tableName).note;
                F.note.index = P.popup({
                    title: "便签",
                    shade: 0,
                    offset: ["41px", a ? null : e.offset().left - 250 + "px"],
                    anim: -1,
                    id: "LAY_adminNote",
                    skin: "layadmin-note layui-anim layui-anim-upbit",
                    content: '<textarea placeholder="内容"></textarea>',
                    resize: !1,
                    success: function (e, a) {
                        var i = e.find("textarea"),
                            l = void 0 === t ? "便签中的内容会存储在本地，这样即便你关掉了浏览器，在下次打开时，依然会读取到上一次的记录。是个非常小巧实用的本地备忘录" : t;
                        i.val(l).focus().on("keyup", function () {
                            layui.data(n.tableName, {key: "note", value: this.value})
                        })
                    }
                })
            }, fullscreen: function (e) {
                var a = "layui-icon-screen-full", t = "layui-icon-screen-restore", i = e.children("i");
                i.hasClass(a) ? (P.fullScreen(), i.addClass(t).removeClass(a)) : (P.exitScreen(), i.addClass(a).removeClass(t))
            }, about: function () {
                P.popupRight({
                    id: "LAY_adminPopupAbout", success: function () {
                        l(this.id).render("system/about")
                    }
                })
            }, more: function () {
                P.popupRight({
                    id: "LAY_adminPopupMore", success: function () {
                        l(this.id).render("system/more")
                    }
                })
            }, back: function () {
                history.back()
            }, setTheme: function (e) {
                var a = e.data("index");
                e.siblings(".layui-this").data("index");
                e.hasClass(y) || (e.addClass(y).siblings(".layui-this").removeClass(y), P.initTheme(a))
            }, rollPage: function (e, t) {
                var i = a("#LAY_app_tabsheader"), n = i.children("li"), l = (i.prop("scrollWidth"), i.outerWidth()),
                    s = parseFloat(i.css("left"));
                if ("left" === e) {
                    if (!s && s <= 0) return;
                    var r = -s - l;
                    n.each(function (e, t) {
                        var n = a(t), l = n.position().left;
                        if (l >= r) return i.css("left", -l), !1
                    })
                } else "auto" === e ? !function () {
                    var e, r = n.eq(t);
                    if (r[0]) {
                        if (e = r.position().left, e < -s) return i.css("left", -e);
                        if (e + r.outerWidth() >= l - s) {
                            var o = e + r.outerWidth() - (l - s);
                            n.each(function (e, t) {
                                var n = a(t), l = n.position().left;
                                if (l + s > 0 && l - s > o) return i.css("left", -l), !1
                            })
                        }
                    }
                }() : n.each(function (e, t) {
                    var n = a(t), r = n.position().left;
                    if (r + n.outerWidth() >= l - s) return i.css("left", -r), !1
                })
            }, leftPage: function () {
                F.rollPage("left")
            }, rightPage: function () {
                F.rollPage()
            }, closeThisTabs: function () {
                var e = parent === self ? P : parent.layui.admin;
                e.closeThisTabs()
            }, closeOtherTabs: function (e) {
                var t = "LAY-system-pagetabs-remove";
                "all" === e ? (a(_ + ":gt(0)").remove(), a(m).find("." + b + ":gt(0)").remove(), a(_).eq(0).trigger("click")) : (a(_).each(function (e, i) {
                    e && e != P.tabsPage.index && (a(i).addClass(t), P.tabsBody(e).addClass(t))
                }), a("." + t).remove())
            }, closeAllTabs: function () {
                F.closeOtherTabs("all")
            }, shade: function () {
                P.sideFlexible()
            }, update: function () {
                a.ajax({
                    type: "get",
                    dataType: "jsonp",
                    data: {name: "layuiAdmin", version: P.v},
                    url: "https://fly.layui.com/api/product_update/",
                    success: function (e) {
                        0 === e.status ? e.version === P.v.replace(/\s|pro|std/g, "") ? layer.alert("当前版本已经是最新版本") : layer.alert("检查到更新，是否前往下载？", {btn: ["更新", "暂不"]}, function (e) {
                            layer.close(e), layer.open({
                                type: 2,
                                content: "https://fly.layui.com/user/product/",
                                area: ["100%", "100%"],
                                title: "检查更新"
                            })
                        }) : 1 == e.status ? layer.alert(e.msg, {btn: ["登入", "暂不"]}, function (e) {
                            layer.close(e), layer.open({
                                type: 2,
                                content: "https://fly.layui.com/user/login/",
                                area: ["100%", "100%"],
                                title: "检查更新"
                            })
                        }) : layer.msg(e.msg || e.code, {shift: 6})
                    },
                    error: function (e) {
                        layer.msg("请求异常，请重试", {shift: 6})
                    }
                })
            }, im: function () {
                P.popup({
                    id: "LAY-popup-layim-demo",
                    shade: 0,
                    area: ["800px", "300px"],
                    title: "面板外的操作示例",
                    offset: "lb",
                    success: function () {
                        layui.view(this.id).render("layim/demo").then(function () {
                            layui.use("im")
                        })
                    }
                })
            }
        };
    !function () {
        var e = layui.data(n.tableName);
        e.theme ? P.theme(e.theme) : n.theme && P.initTheme(n.theme.initColorIndex), "pageTabs" in layui.setter || (layui.setter.pageTabs = !0), n.pageTabs || (a("#LAY_app_tabs").addClass(c), u.addClass("layadmin-tabspage-none")), s.ie && s.ie < 10 && l.error("IE" + s.ie + "下访问可能不佳，推荐使用：Chrome / Firefox / Edge 等高级浏览器", {
            offset: "auto",
            id: "LAY_errorIE"
        })
    }(), i.on("tab(" + p + ")", function (e) {
        P.tabsPage.index = e.index
    }), P.on("tabsPage(setMenustatus)", function (e) {
        var t = e.url, i = function (e) {
            return {list: e.children(".layui-nav-child"), a: e.children("*[lay-href]")}
        }, n = a("#" + k), l = "layui-nav-itemed", s = function (e) {
            e.each(function (e, n) {
                var s = a(n), r = i(s), o = r.list.children("dd"), u = t === r.a.attr("lay-href");
                if (o.each(function (e, n) {
                    var s = a(n), r = i(s), o = r.list.children("dd"), u = t === r.a.attr("lay-href");
                    if (o.each(function (e, n) {
                        var s = a(n), r = i(s), o = t === r.a.attr("lay-href");
                        if (o) {
                            var u = r.list[0] ? l : y;
                            return s.addClass(u).siblings().removeClass(u), !1
                        }
                    }), u) {
                        var d = r.list[0] ? l : y;
                        return s.addClass(d).siblings().removeClass(d), !1
                    }
                }), u) {
                    var d = r.list[0] ? l : y;
                    return s.addClass(d).siblings().removeClass(d), !1
                }
            })
        };
        n.find("." + y).removeClass(y), P.screen() < 2 && P.sideFlexible(), s(n.children("li"))
    }), i.on("nav(layadmin-system-side-menu)", function (e) {
        e.siblings(".layui-nav-child")[0] && u.hasClass(C) && (P.sideFlexible("spread"), layer.close(e.data("index"))), P.tabsPage.type = "nav"
    }), i.on("nav(layadmin-pagetabs-nav)", function (e) {
        var a = e.parent();
        a.removeClass(y), a.parent().removeClass(d)
    });
    var A = function (e) {
        var a = (e.attr("lay-id"), e.attr("lay-attr")), t = e.index();
        P.tabsBodyChange(t, {url: a})
    }, _ = "#LAY_app_tabsheader>li";
    o.on("click", _, function () {
        var e = a(this), t = e.index();
        P.tabsPage.type = "tab", P.tabsPage.index = t, A(e)
    }), i.on("tabDelete(" + p + ")", function (e) {
        var t = a(_ + ".layui-this");
        e.index && P.tabsBody(e.index).remove(), A(t), P.delResize()
    }), o.on("click", "*[lay-href]", function () {
        var e = a(this), t = e.attr("lay-href"), i = e.attr("lay-text");
        layui.router();
        P.tabsPage.elem = e;
        var n = parent === self ? layui : top.layui;
        n.index.openTabsPage(t, i || e.text()), t === P.tabsBody(P.tabsPage.index).find("iframe").attr("src") && P.events.refresh()
    }), o.on("click", "*[layadmin-event]", function () {
        var e = a(this), t = e.attr("layadmin-event");
        F[t] && F[t].call(this, e)
    }), o.on("mouseenter", "*[lay-tips]", function () {
        var e = a(this);
        if (!e.parent().hasClass("layui-nav-item") || u.hasClass(C)) {
            var t = e.attr("lay-tips"), i = e.attr("lay-offset"), n = e.attr("lay-direction"), l = layer.tips(t, this, {
                tips: n || 1, time: -1, success: function (e, a) {
                    i && e.css("margin-left", i + "px")
                }
            });
            e.data("index", l)
        }
    }).on("mouseleave", "*[lay-tips]", function () {
        layer.close(a(this).data("index"))
    });
    var z = layui.data.resizeSystem = function () {
        layer.closeAll("tips"), z.lock || setTimeout(function () {
            P.sideFlexible(P.screen() < 2 ? "" : "spread"), delete z.lock
        }, 100), z.lock = !0
    };
    r.on("resize", layui.data.resizeSystem), e("admin", P)
});