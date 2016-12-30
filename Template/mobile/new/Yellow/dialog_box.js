/**
 * Created by yang_ on 2016/11/3.
 */
function dialog() {
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
}