define('ui', function (require, exports, module) {
    var _cacheThisModule_, container = document.querySelector(".wx_wrap") || document.body;
    var bInit1111Tips = false;
    var goLink = "//wqs.jd.com/my/mywx1111.shtml?p=my1111Record";
    var objInfos = {
        quan: {
            success: {
                title: '优惠券领取成功',
                subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                btn1: '朕已阅',
                btn2: ''
            },
            alreadyReceived: {title: '重复领取', subtitle: '这张券已经安静躺在您的个人中心里了！', btn1: '随便逛逛', btn2: ''},
            alreadyReceivedToday: {title: '领取上限', subtitle: '今日已经领取够多了，明天再来！', btn1: '随便逛逛', btn2: ''},
            reachLimit: {title: '券已领完', subtitle: '很遗憾，券被一抢而空了！', btn1: '随便逛逛', btn2: ''},
            fail: {title: '领取失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛', btn2: ''},
            allDone: {title: '券已领完', subtitle: '很遗憾，券被一抢而空了！', btn1: '随便逛逛', btn2: ''},
            todayDone: {title: '领取上限', subtitle: '今日已经领取够多了，明天再来！', btn1: '随便逛逛', btn2: ''},
            hourDone: {title: '领取上限', subtitle: '今日已经领取够多了，明天再来！', btn1: '随便逛逛', btn2: ''},
            notExist: {title: '活动不存在', subtitle: '抱歉，您领的券飞往外星球！', btn1: '随便逛逛', btn2: ''},
            notStart: {title: '领券活动未开始', subtitle: '还未开张，再耐心等等！', btn1: '随便逛逛', btn2: ''},
            expire: {title: '活动已结束', subtitle: '活动打烊，下次趁早噢！', btn1: '随便逛逛', btn2: ''},
            blackList: {title: '领取失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛', btn2: ''},
            tryAgain: {title: '领取失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛', btn2: ''}
        },
        yuyue: {
            success: {
                title: '预约成功',
                subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                btn1: '朕已阅',
                btn2: ''
            },
            exist: {title: '预约成功', subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ', btn1: '朕已阅', btn2: ''},
            notExist: {title: '活动不存在', subtitle: '抱歉，您预约的活动飞往外星球！', btn1: '随便逛逛', btn2: ''},
            expire: {title: '活动已结束', subtitle: '活动打烊，下次趁早噢！', btn1: '随便逛逛', btn2: ''},
            notStart: {title: '活动未开始', subtitle: '还未开张，再耐心等等！', btn1: '随便逛逛', btn2: ''},
            systemError: {title: '预约失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛', btn2: ''}
        },
        mix: function (objReqeust) {
            var returnMsg = null;
            var bContinue = true;
            for (var key in objReqeust) {
                var item = objReqeust[key];
                if (bContinue) {
                    if (item.result == false && key.indexOf('quan') > -1) {
                        bContinue = false;
                        returnMsg = {title: '领券失败', subtitle: '抱歉，优惠券不翼而飞', btn1: '随便逛逛'};
                    } else if (item.result == false && key.indexOf('yuyue') > -1) {
                        returnMsg = {title: '预约失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛'};
                    } else if (item.result == false && key.indexOf('shop') > -1) {
                        returnMsg = {title: '收藏失败', subtitle: '抱歉，店铺人气太旺，稍后再试！', btn1: '随便逛逛'};
                    }
                }
            }
            if (!returnMsg) {
                if (objReqeust.shop && objReqeust.quan && objReqeust.yuyue) {
                    returnMsg = {
                        title: '预约、领券成功',
                        subtitle: '<a href="' + goLink + '">店铺已收藏，还有不能说的秘密噢！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.shop && objReqeust.quan) {
                    returnMsg = {
                        title: '领券成功，店铺已收藏',
                        subtitle: '<a href="' + goLink + '">店铺已收藏，还有不能说的秘密噢！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.shop && objReqeust.yuyue) {
                    returnMsg = {
                        title: '预约成功',
                        subtitle: '<a href="' + goLink + '">店铺已收藏，还有不能说的秘密噢！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.yuyue && objReqeust.quan) {
                    returnMsg = {
                        title: '预约成功',
                        subtitle: '<a href="' + goLink + '">送您一张优惠券，还有神秘特权哟！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.yuyue) {
                    returnMsg = {
                        title: '预约成功',
                        subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.shop) {
                    returnMsg = {
                        title: '店铺收藏成功',
                        subtitle: '<a href="' + goLink + '">店铺已收藏，还有不能说的秘密噢！</a> ',
                        btn1: '朕已阅'
                    };
                } else if (objReqeust.quan) {
                    returnMsg = {
                        title: '优惠券领取成功',
                        subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                        btn1: '朕已阅'
                    };
                }
            }
            return returnMsg;
        },
        fav: {
            success: {
                title: '店铺收藏成功',
                subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                btn1: '朕已阅',
                btn2: ''
            },
            exist: {title: '重复收藏', subtitle: '此店铺已经收藏了，去看看别家吧！', btn1: '随便逛逛', btn2: ''},
            systemError: {title: '收藏失败', subtitle: '参与人数过多，稍后在试！', btn1: '随便逛逛', btn2: ''}
        },
        cart: {
            success: {
                title: '商品加车成功',
                subtitle: '<a href="' + goLink + '">同时我们还为您精心准备了神秘特权哟！</a> ',
                btn1: '朕已阅',
                btn2: ''
            },
            exist: {title: '重复加车', subtitle: '这个宝贝已经安静的躺在您的购物车里！', btn1: '随便逛逛', btn2: ''},
            systemError: {title: '加车失败', subtitle: '剁手节果然人多，服务器说想歇歇！', btn1: '随便逛逛', btn2: ''}
        }
    };
    var alertCoverDiv = document.createElement('div');
    alertCoverDiv.style.cssText = "position: fixed; width: 100%; height: 100%; top: 0px; left: 0px; z-index: 109; background: rgba(0, 0, 0, 0.3);";
    function notNeedLoadCss() {
        var uiCSSUrl = 'base.s.min', uiCSSUrl2 = 'gb.min_'
        links = document.getElementsByTagName('link'), isHave = false;
        for (var i = 0, l = links.length; i < l; i++) {
            if (links[i].rel == 'stylesheet' && (links[i].href.indexOf(uiCSSUrl) >= 0 || links[i].href.indexOf(uiCSSUrl2) >= 0)) {
                isHave = true;
                break;
            }
        }
        return isHave;
    }

    function loadCss(url) {
        if (notNeedLoadCss()) {
            return;
        }
        var l = document.createElement('link');
        l.setAttribute('type', 'text/css');
        l.setAttribute('rel', 'stylesheet');
        l.setAttribute('href', url);
        document.getElementsByTagName("head")[0].appendChild(l);
    };
    function showTip(obj) {
        if (!obj)return;
        var className = obj.className || 'g_small_tips', tips = document.querySelector("." + className), t = obj.t || 2000, title = obj.title || '操作成功!';
        if (!tips) {
            tips = document.createElement('div');
            tips.className = className;
            document.body.appendChild(tips);
        }
        tips.innerText = title;
        tips.style.display = 'block';
        setTimeout(function () {
            tips.style.display = 'none';
        }, t);
    }

    function extend(a, b) {
        for (var i in b) {
            if (b.hasOwnProperty(i)) {
                a[i] = b[i];
            }
        }
        return a;
    }

    function info(opts) {
        var option = {msg: "", icon: "none", delay: 2000};
        opts = opts || {};
        extend(option, opts);
        var el = document.createElement('div');
        el.className = "mod_alert show fixed";
        el.innerHTML = (option.icon != 'none' ? ('<i class="icon' + (option.icon != 'info' ? (' icon_' + option.icon) : '') + '"></i>') : '') + '<p>' + option.msg + '</p>';
        container.appendChild(el);
        setTimeout(function () {
            el.style.display = 'none';
            container.removeChild(el);
        }, option.delay);
    }

    function alert(opts) {
        var option = {
            showClose: false,
            msg: "",
            confirmText: '确认',
            icon: "none",
            onConfirm: null,
            stopMove: false,
            btnClass: 'btn_1'
        }, stopMove = function (e) {
            e.preventDefault();
        }, el = document.createElement('div');
        opts = opts || {};
        extend(option, opts);
        container = opts.container || container;
        el.className = "mod_alert show fixed";
        el.innerHTML = (option.showClose ? '<span class="close"></span>' : '') + (option.icon != 'none' ? ('<i class="icon' + (option.icon != 'info' ? (' icon_' + option.icon) : '') + '"></i>') : '') + '<p>' + option.msg + '</p>' + (option.subMsg ? '<p class="small">' + option.subMsg + '</p>' : '') + '<p class="btns"><a href="javascript:;" class="btn ' + option.btnClass + '">' + option.confirmText + '</a></p>';
        container.appendChild(el);
        option.showClose && (el.querySelector(".close").onclick = function (e) {
            this.onclick = null;
            clear();
        });
        el.querySelector(".btn").onclick = function (e) {
            option.onConfirm && option.onConfirm();
            this.onclick = null;
            clear();
        };
        document.body.appendChild(alertCoverDiv);
        option.stopMove && document.addEventListener("touchmove", stopMove, false);
        function clear() {
            document.body.removeChild(alertCoverDiv);
            el.style.display = 'none';
            container.removeChild(el);
            option.stopMove && document.removeEventListener("touchmove", stopMove, false);
        }
    }

    function confirm(opts) {
        var option = {msg: "", icon: "none", okText: "确定", cancelText: "取消", onConfirm: null, onCancel: null};
        opts = opts || {};
        extend(option, opts);
        container = opts.container || container;
        var el = document.createElement('div');
        el.className = "mod_alert show fixed";
        el.innerHTML = (option.icon != 'none' ? ('<i class="icon' + (option.icon != 'info' ? (' icon_' + option.icon) : '') + '"></i>') : '') + '<p>' + option.msg + '</p><p class="btns"><a href="javascript:;" id="ui_btn_confirm" class="btn btn_1">' + option.okText + '</a><a href="javascript:;" id="ui_btn_cancel" class="btn btn_1">' + option.cancelText + '</a></p>';
        container.appendChild(el);
        document.body.appendChild(alertCoverDiv);
        el.querySelector("#ui_btn_cancel").onclick = function (e) {
            option.onCancel && option.onCancel();
            clear();
        };
        el.querySelector("#ui_btn_confirm").onclick = function (e) {
            option.onConfirm && option.onConfirm();
            clear();
        };
        function clear() {
            el.style.display = 'none';
            container.removeChild(el)
            document.body.removeChild(alertCoverDiv);
        }
    }

    function showPromoteTips(opt) {
        console.log("调用弹窗的参数>", opt);
        if (!bInit1111Tips) {
            var html = ['<div class="preheat_mod_popup" style="display:none;" id="pop_1111_speci">', '    <div class="preheat_mod_popup_bd">', '      <div class="preheat_mod_popup_ctn">', '        <p class="preheat_mod_popup_tit">活动预约成功</p>', '        <p class="preheat_mod_popup_desc">同时我们为您精心准备了神秘特权礼包!</p>', '        <div class="preheat_mod_popup_btns">', '          <a class="preheat_mod_popup_btn mybtn1" href="javascript:;" style="display:none">查看特权</a>', '          <a class="preheat_mod_popup_btn mybtn2" href="#"  style="display:none">去使用优惠券</a>', '        </div>', '      </div>', '      <span class="preheat_mod_popup_close"></span>', '    </div>', '  </div>'].join("");
            $("body").append(html);
            bInit1111Tips = true;
        }
        var msg;
        if (opt.type == "mix") {
            msg = objInfos[opt.type](opt.result);
        } else {
            msg = objInfos[opt.type][opt.result];
        }
        var dom = document.getElementById("pop_1111_speci");
        dom.querySelector(".preheat_mod_popup_tit").innerHTML = msg.title;
        dom.querySelector(".preheat_mod_popup_desc").innerHTML = msg.subtitle;
        var domBtn1 = dom.querySelector(".mybtn1");
        domBtn1.style.display = "none";
        if (msg.btn1) {
            domBtn1.style.display = "inline-block";
            if (opt.ignoreGift) {
                domBtn1.innerHTML = "随便逛逛";
            } else {
                domBtn1.innerHTML = msg.btn1;
            }
        }
        dom.addEventListener('click', function (e) {
            if (e.target.className.indexOf("close") !== -1) {
                dom.style.display = "none";
            } else if (e.target.getAttribute("href") && e.target.className.indexOf("mybtn") === -1) {
                location.href = e.target.getAttribute("href");
            } else {
                dom.style.display = "none";
            }
            e.preventDefault && e.preventDefault();
            return false;
        }, false);
        dom.style.display = "block";
    }

    JD.store.comboLoad(["/fd/h5/1111/wx_mall/css/popup.min_9a3a699e.css"], "css");
    loadCss("//wq.360buyimg.com/fd/base/css/base/mod_alert.s.min.css");
    module.exports = {showTip: showTip, info: info, alert: alert, confirm: confirm, showPromoteTips: showPromoteTips};
});
