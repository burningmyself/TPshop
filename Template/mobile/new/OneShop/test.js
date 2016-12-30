/**
 * Created by yang_ on 2016/11/16.
 */
define('wq.qiangbao.index',
    function(require, exports, module) {
        var PAGE_SIZE = 30;
        var FIRST_PAGE = 1;
        var $ = require("zepto");
        var redirect = require('wq.qiangbao.urlredirect');
        var _url = require('url');
        var _comm = require('wq.qiangbao.lib.comm');
        var _fail = require('wq.qiangbao.ui.fail');
        var _touch = require('wq.qiangbao.ui.touch');
        var _slider = require('wq.qiangbao.ui.slider');
        var _footNav = require('wq.qiangbao.ui.footnav');
        var _tpl = require('tpl_wq.qiangbao.index');
        var api = {
            getList: function(cfg) {
                cfg.url = window.TWS_PREFIX + '/GetItemList';
                cfg.ump = {
                    bizid: UMP_BIZ_TYPE,
                    operation: UMP_OP_GETITEMLIST
                };
                cfg.retryCount = 1;
                _comm.api.call(cfg);
            },
            getBroadcast: function(cfg) {
                cfg.url = window.TWS_PREFIX + '/GetFortunatePersons';
                cfg.ump = {
                    bizid: UMP_BIZ_TYPE,
                    operation: UMP_OP_GETFORTUNATEPERSONS
                };
                cfg.retryCount = 0;
                _comm.api.call(cfg);
            },
            getRecommend: function(cfg) {
                cfg.url = window.TWS_PREFIX + '/GetRecommendItemList';
                cfg.ump = {
                    bizid: UMP_BIZ_TYPE,
                    operation: UMP_OP_GETRECOMMENDITEMLIST
                };
                cfg.retryCount = 0;
                _comm.api.call(cfg);
            }
        };
        var tabTypes = ['1', '4', '5', '6', '7'];
        var ListView = function(type, category) {
            var TREASURE_LIST_KEY = ITEM_LIST_STORE_KEY;
            var self = {};
            var $treasures = $('[name=treasure]');
            var $treasure = $treasures.filter('[data-type="' + type + '"]');
            var _ = {
                list: [],
                type: type,
                category: category,
                forceRefresh: false,
                lock_next: false,
                lock_scrollLoad: false,
                page: FIRST_PAGE,
                pageSize: PAGE_SIZE,
                finish: false,
                lazyImages: [],
                $treasures: $treasures,
                $treasure: $treasure,
                $treasure_list: $treasure.find('#treasure_view'),
                $loading: $treasure.find('#treasure_loading')
            };
            var wndHeight = $(window).height();
            $(document.body).css('min-height', wndHeight + $('.slider').height());
            _.fail = _fail({
                tplType: 'index',
                $parent: $treasure,
                show: function(data) {
                    _.lock_scrollLoad = true;
                    _.$loading.hide();
                },
                onClick: function(data) {
                    if (data.type == _fail.TYPE_PAGE) {
                        _.page = FIRST_PAGE;
                        _.finish = false;
                    }
                    self.next();
                }
            });
            var fail = function(ret) {
                ret = ret || ErrCodeSysErr;
                if (_.page == FIRST_PAGE) {
                    var pageType = _fail.TYPE_PAGE;
                    if (_.list.length > 0) {
                        pageType = _fail.TYPE_NEXT;
                    }
                    switch (parseInt(ret)) {
                        case ErrCodeNoData:
                            _.fail.show({
                                type:
                                pageType,
                                message: '没有抢宝商品<br/>请稍后尝试刷新看看！',
                                btn_text: '刷新看看',
                                ret: ErrCodeNoData
                            });
                            break;
                        case ErrCodeFrequency:
                            _.fail.show({
                                type:
                                pageType,
                                message: '您操作的频率过高<br/>请稍后尝试刷新看看！',
                                btn_text: '刷新看看',
                                ret: ErrCodeFrequency
                            });
                            break;
                        case ErrCodeNetErr:
                            _.fail.show({
                                type:
                                pageType,
                                message: '网络状况不佳<br/>请稍后尝试刷新看看！',
                                btn_text: '刷新看看',
                                ret: ErrCodeNetErr
                            });
                            break;
                        default:
                            _.fail.show({
                                type:
                                pageType,
                                ret: ret
                            });
                            break;
                    }
                } else {
                    if (ret == ErrCodeNetErr) {
                        _.fail.show({
                            type: _fail.TYPE_NEXT,
                            message: '网络状况不佳，稍等一会刷新看看！',
                            ret: ErrCodeNetErr
                        });
                    } else {
                        _.fail.show({
                            type: _fail.TYPE_NEXT,
                            ret: ret
                        });
                    }
                }
            }
            var saveStatus = function() {
                if (!window.sessionStorage) {
                    return;
                }
                if (self.isShow() && _.list) {
                    _comm.replaceHashParam('mode', 'cache');
                    var obj = {
                        finish: _.finish,
                        list: _.list,
                        page: _.page,
                        type: type,
                        scrollTop: $(window).scrollTop()
                    }
                    sessionStorage.setItem(TREASURE_LIST_KEY, JSON.stringify(obj));
                }
            };
            var resetStatus = function() {
                try {
                    var mode = _url.getHashParam('mode');
                    if (mode != 'cache') {
                        return;
                    }
                    if (!window.sessionStorage) {
                        return;
                    }
                    var json = sessionStorage.getItem(TREASURE_LIST_KEY);
                    var obj = null;
                    if (json && (obj = JSON.parse(json)) && obj.type == type) {
                        _.lock_scrollLoad = true;
                        try {
                            _.list = obj.list;
                            _.page = obj.page;
                            _.finish = obj.finish;
                            render(_.list);
                            if (_.finish) {
                                _.$loading.hide();
                            }
                            var scrollTop = obj.scrollTop;
                            _comm.setScrollTop(scrollTop);
                            sessionStorage.removeItem(TREASURE_LIST_KEY);
                            self.forceRefresh();
                        } finally {
                            _.lock_scrollLoad = false;
                        }
                    }
                } catch(e) {
                    console.warn && console.warn('列表缓存有问题！err:' + e)
                }
            };
            var lazyLoadImg = function() {
                _.lazyImages = _.$treasure_list.find('img[data-src]');
                var wndHeight = $(window).height();
                var scrollTop = $(window).scrollTop();
                var scrollBottom = scrollTop + wndHeight;
                loadImage(scrollTop, scrollBottom);
            };
            var loadImage = function(scrollTop, scrollBottom) {
                if (_.lazyImages && _.lazyImages.length > 0) {
                    _.lazyImages.each(function(img) {
                        var $img = $(this);
                        var loaded = $img.data('loaded');
                        if (loaded && loaded == '1') {
                            return;
                        }
                        var offset = $img.offset();
                        if (offset.top <= scrollBottom + LAZY_IMAGE_RANGE_SIZE && offset.top + offset.height >= scrollTop - LAZY_IMAGE_RANGE_SIZE) {
                            $img.attr('src', $img.data('src'));
                            $img.data('loaded', '1');
                        }
                    });
                }
            };
            var render = function(list) {
                _.$treasure_list.append(_tpl.child_list({
                    list: list
                }));
                setTimeout(function() {
                        lazyLoadImg();
                    },
                    0);
            };
            var initEvent = function() {
                _.$treasure_list.on('click',
                    function(e) {
                        var $elem = $(e.srcElement || e.target);
                        $elem = $elem.closest('[type="linkDetail"]');
                        if ($elem && $elem.length > 0) {
                            var id = $elem.data('id');
                            var skuid = $elem.data('skuid');
                            var index = $elem.index() + 1;
                            var issue = $elem.data('issue');
                            saveStatus();
                            setTimeout(function() {
                                    var ptag = '';
                                    switch (parseInt(_.type)) {
                                        case 7:
                                            ptag = env == 'weixin' ? '39133.7': '39133.8';
                                            break;
                                        case 4:
                                            ptag = env == 'weixin' ? '39133.5': '39133.6';
                                            break;
                                        case 5:
                                        case 6:
                                            ptag = env == 'weixin' ? '39133.3': '39133.4';
                                            break;
                                        case 1:
                                        default:
                                            ptag = env == 'weixin' ? '39133.1': '39133.2';
                                            break;
                                    }
                                    ptag += '.' + index;
                                    window.location = window.URL_DETAIL + '?id=' + id + '&sku=' + skuid + '&issue=' + issue + '&ptag=' + ptag + '&buyflag=1';
                                },
                                100);
                        }
                    });
            };
            self.scrollNotify = function(scrollTop, scrollBottom) {
                var loadingTop = _.$loading.offset().top;
                if (scrollBottom > loadingTop && !_.lock_scrollLoad) {
                    self.next();
                }
                loadImage(scrollTop, scrollBottom);
            };
            self.forceRefresh = function() {
                _.forceRefresh = true;
            };
            self.show = function(autoScroll) {
                if (_.forceRefresh) {
                    _.$treasure_list.empty();
                    _.page = FIRST_PAGE;
                    _.finish = false;
                    _.list = [];
                    _.forceRefresh = false;
                }
                _.$treasures.css('display', 'none');
                _.$treasure.show();
                if (autoScroll) {
                    var $treasure_head = $('.treasure_cate_wrap');
                    var offset = $treasure_head.offset();
                    var fixedTop = offset.top;
                    $(window).scrollTop(fixedTop);
                }
                if (!_.__init) {
                    resetStatus();
                    _.__init = true;
                }
                var $tab_list = $('#tab_list');
                $tab_list.removeClass('fixed');
                lazyLoadImg();
                if (_.page <= FIRST_PAGE) {
                    self.next();
                } else {
                    var wndHeight = $(window).height();
                    var scrollTop = $(window).scrollTop();
                    var scrollBottom = scrollTop + wndHeight;
                    self.scrollNotify(scrollTop, scrollBottom);
                }
            };
            self.isShow = function() {
                return _.$treasure.css('display') != 'none'
            };
            self.next = function() {
                if (_.lock_next || _.finish) {
                    return;
                }
                _.lock_scrollLoad = false;
                _.$loading.show(true);
                _.fail.hide();
                _.lock_next = true;
                var result = {
                    recommendDone: false,
                    listDone: false,
                    recommendRes: null,
                    listRes: null
                };
                var pageSize = _.pageSize;
                var autoNext = false;
                var param = {
                    categoryId: _.category,
                    sortType: _.type,
                    pageNo: _.page,
                    pageSize: pageSize
                };
                if (_.type == 7) {
                    param.sortType = 1;
                }
                if (_.page == 1 && _.type == 1 && _.list.length == 0) {
                    api.getRecommend({
                        success: function(res) {
                            result.recommendDone = true;
                            result.recommendRes = res;
                            resolve(result);
                        },
                        error: function() {
                            result.recommendDone = true;
                            result.recommendRes = {};
                            resolve(result);
                        }
                    });
                } else {
                    result.recommendDone = true;
                    result.recommendRes = {};
                }
                api.getList({
                    data: param,
                    success: function(res) {
                        result.listDone = true;
                        result.listRes = res;
                        resolve(result);
                        window._PFM_TIMING[5] = new Date();
                    },
                    error: function() {
                        fail(ErrCodeNetErr);
                        result.listDone = true;
                        result.listRes = {};
                        resolve(result);
                    },
                    finally: function() {
                        if (!autoNext) {
                            _.lock_next = false;
                            _.$loading.hide(!_.finish);
                        }
                    }
                });
                function resolve(result) {
                    if (result.recommendDone && result.listDone) {
                        var renderList = [];
                        if (result.recommendRes.ret == 0) {
                            if (result.recommendRes.data && result.recommendRes.data.list && result.recommendRes.data.list.length > 0) {
                                var list = result.recommendRes.data.list;
                                var newList = _comm.arrayFilter(list, _.list,
                                    function(src, dest) {
                                        return src.itemId == dest.itemId;
                                    });
                                renderList = renderList.concat(newList);
                                _.list = _.list.concat(newList);
                            }
                        }
                        if (result.listRes.ret == 0) {
                            if (result.listRes.data && result.listRes.data.list && result.listRes.data.list.length > 0) {
                                var list = result.listRes.data.list;
                                var newList = _comm.arrayFilter(list, _.list,
                                    function(src, dest) {
                                        return src.itemId == dest.itemId;
                                    });
                                renderList = renderList.concat(newList);
                                _.list = _.list.concat(newList);
                                if (list.length < pageSize) {
                                    _.finish = true;
                                } else {
                                    if (newList.length < 5) {
                                        _.lock_next = false;
                                        autoNext = true;
                                        self.next();
                                    }
                                }
                            } else {
                                _.finish = true;
                                if (_.list.length < 1) {
                                    fail(ErrCodeNoData);
                                }
                            }
                            _.page++;
                        } else {
                            if (!renderList.length) {
                                fail(result.listRes.ret);
                            }
                        }
                        render(renderList);
                    }
                }
            }; (function() {
                initEvent();
            })();
            return self;
        };
        var view = (function() {
            var self = {};
            var _ = {
                type: null,
                $tab_list: $('#tab_list'),
                $category_list: $('.list_cols_5'),
                listViews: {}
            };
            var getListView = function(type, category) {
                var listView = _.listViews[type + '_' + category];
                if (!listView) {
                    listView = ListView(type, category);
                    _.listViews[type + '_' + category] = listView;
                }
                return listView;
            };
            self.tab = function(type, category, auto, force) {
                if (!_comm.arrayContains(tabTypes, type)) {
                    type = 1;
                }
                category = category || 0;
                if (type == 5 || type == 6) {
                    var type5 = _.$tab_list.find('a[data-type="' + type + '"]');
                    if (type5.hasClass('cur')) {
                        type = 11 - type;
                        type5.data('type', type);
                    }
                    if (type == 5) {
                        type5.find('i').removeClass('down').addClass('up');
                    } else {
                        type5.find('i').removeClass('up').addClass('down');
                    }
                }
                var listView = getListView(type, category);
                if (_.type == type && _.category == category || force) {
                    listView.forceRefresh();
                }
                _.type = type;
                _.category = category;
                _.$tab_list.find('a').removeClass('cur');
                _.$tab_list.find('a[data-type="' + type + '"]').addClass('cur');
                if (type != 5 && type != 6) {
                    _.$tab_list.find('a[data-type="5"], a[data-type="6"]').find('i').removeClass('up').removeClass('down');
                }
                if (type == 7) {
                    var curCategory = $('#categorys').find('[data-id="' + category + '"]');
                    curCategory.addClass('cur');
                    _.$tab_list.find('a.cur').html(curCategory.find('.text').html() + '<i class="icon_order_dec"></i>');
                } else {
                    _.$tab_list.find('a[data-type="7"]').html('分类<i class="icon_order_dec"></i>');
                }
                listView.show(!auto);
                _comm.replaceHashParam('type', type);
                _comm.replaceHashParam('category', category);
            };
            self.initBannerCategory = function() {
                $('.slider').html(_tpl.child_banner());
                _.$category_list.html(_tpl.child_category());
            };
            self.initTab = function() {
                _.$tab_list.find('a').on('click',
                    function() {
                        var type = $(this).data('type');
                        var category = $(this).data('category');
                        if (type == 7) {
                            $('#categorys').toggleClass('hide');
                            return;
                        }
                        self.tab(type, category);
                    });
                _.$category_list.on('click', '.item',
                    function(event) {
                        $(this).addClass('cur').siblings().removeClass('cur');
                        var title = $(this).closest('#categorys').prev();
                        var type = title.data('type');
                        var category = $(this).data('id');
                        title.data('category', category);
                        self.tab(type, category, null, true);
                        $('#categorys').toggleClass('hide');
                    });
                $(document).on('click',
                    function(e) {
                        var _this = $(e.target);
                        if (_this.closest('#categorys').length || _this.data('type') == 7 || _this.hasClass('icon_order_dec')) {
                            return;
                        }
                        $('#categorys').addClass('hide');
                    });
            };
            var fixedTabList = function() {
                var $treasure_head = $('.treasure_cate_wrap');
                var $up = $('#aside_up');
                var offset = $treasure_head.offset();
                var fixedTop = offset.top;
                var scrollTop = $(window).scrollTop();
                if (fixedTop < scrollTop) {
                    _.$tab_list.addClass('fixed');
                    $up.show();
                } else {
                    $up.hide();
                    _.$tab_list.removeClass('fixed');
                }
            };
            var moveComment = function() {
                var speed = 2000;
                var _transitionEnd = 'onwebkittransitionend' in window ? "webkitTransitionEnd": 'transitionend';
                var DOM_inner = $(".treasure_broadcast p"),
                    domOut = $('.treasure_broadcast'),
                    hidedom = null;
                DOM_inner.first().on(_transitionEnd, swich);
                function move() {
                    hidedom = $(".treasure_broadcast p")[0];
                    var moveY = hidedom.getBoundingClientRect().height;
                    DOM_inner.css({
                        'transition': 'transform 0.5s linear',
                        '-webkit-transition': '-webkit-transform 0.5s linear',
                        'transform': "translate3d(0,-" + moveY + "px,0)",
                        '-webkit-transform': "translate3d(0,-" + moveY + "px,0)"
                    });
                }
                function swich() {
                    DOM_inner.css({
                        'transition': 'none',
                        '-webkit-transition': 'none',
                        'transform': 'translate3d(0,0,0)',
                        '-webkit-transform': 'translate3d(0,0,0)'
                    });
                    domOut.append(domOut.find('p').first());
                }
                if (self.interval) {
                    clearInterval(self.interval);
                }
                self.interval = setInterval(move, speed);
            };
            self.initList = function() {
                $(window).scroll(function() {
                    var wndHeight = $(window).height();
                    var scrollTop = $(window).scrollTop();
                    var scrollBottom = scrollTop + wndHeight;
                    var listView = getListView(_.type, _.category);
                    listView.scrollNotify(scrollTop, scrollBottom);
                    fixedTabList();
                });
            };
            self.initBroadcast = function() {
                api.getBroadcast({
                    data: {
                        size: 5
                    },
                    success: function(res) {
                        if (res.ret == 0 && res.data.list && res.data.list.length) {
                            window._PFM_TIMING[4] = new Date();
                            $('.treasure_broadcast').html(_tpl.child_broadcast({
                                data: res.data.list
                            }));
                            $('.treasure_broadcast').show();
                            moveComment();
                        }
                    },
                    error: function() {},
                    finally: function() {}
                });
            };
            self.initFloating = function() {
                var floating_flag = window.__CONFIG.floating_flag;
                if (floating_flag == 1) {
                    var $floating = $('.floating');
                    var floating_img_url = window.__CONFIG.floating_img_url;
                    var floating_mode = window.__CONFIG.floating_mode;
                    if (floating_img_url) {
                        $floating.find('img').attr('src', floating_img_url);
                    }
                    $floating.find('.fl_closer').on('click',
                        function() {
                            $floating.hide();
                        });
                    var $floating_wx_follow = $('#floating_wx_follow');
                    $floating_wx_follow.find('.close').on('click',
                        function() {
                            $floating_wx_follow.removeClass('show');
                        });
                    var floating_follow_wx_url = window.__CONFIG.floating_follow_wx_url;
                    if (floating_follow_wx_url) {
                        $jd_qrcode = $floating_wx_follow.find('img');
                        $jd_qrcode.attr('src', floating_follow_wx_url);
                    }
                    $floating.on('click',
                        function(e) {
                            var $elem = $(e.srcElement || e.target);
                            if (!$elem.hasClass('fl_closer')) {
                                if (floating_mode == 1 && env == 'weixin') {
                                    $floating_wx_follow.addClass('show');
                                } else if (floating_mode == 0) {
                                    var floating_url = window.__CONFIG.sqfloating_url;
                                    if (env == 'weixin') {
                                        floating_url = window.__CONFIG.wxfloating_url;
                                    }
                                    if (floating_url) {
                                        window.location = floating_url;
                                    }
                                } else {
                                    var floating_url = window.__CONFIG.floating_follow_sq_url;
                                    if (floating_url) {
                                        window.location = floating_url;
                                    }
                                }
                            }
                        });
                    $floating.show();
                }
            }
            self.init = function() {
                self.initBannerCategory();
                self.initTab();
                self.initList();
                self.initFloating();
                self.initBroadcast();
                _footNav();
                if (_url.getHashParam('fromQQWallet') == 1 && env == 'qq') {
                    $('.QQ_wallet_guide').show();
                    _comm.deleteHashParam('fromQQWallet');
                }
                var $slider = $('.slider');
                _.slider = _slider({
                    dom: $slider.find('.slider_list'),
                    dom_items: $slider.find('.slider_list a'),
                    tab_items: $slider.find('.slider_indexs span')
                });
                $('#showRule').on('click',
                    function() {
                        if (env == 'weixin') {
                            window.location = window.URL_RULE + '/wx.htm';
                        } else {
                            window.location = window.URL_RULE + '/sq.htm';
                        }
                    });
                $('.treasure_aside').on('click',
                    function(e) {
                        var $elem = $(e.srcElement || e.target);
                        var clickType = $elem.data('clicktype');
                        switch (clickType) {
                            case 'up':
                                $(window).scrollTop(0);
                                break;
                            case 'intoUC':
                                window.location = window.URL_USERCENTER;
                                break;
                        }
                    });
                var type = _url.getHashParam('type') || 1;
                var category = _url.getHashParam('category') || 0;
                if (type == 5 || type == 6) {
                    if (type == 5) {
                        _.$tab_list.find('a[data-type="5"] i').removeClass('down').addClass('up');
                    } else {
                        _.$tab_list.find('a[data-type="5"] i').removeClass('up').addClass('down');
                    }
                    _.$tab_list.find('a[data-type="5"]').data('type', type);
                }
                self.tab(type, category, true);
                _touch({
                    dom: document.getElementById('treasure_panel'),
                    types: $('#tab_list').find('[data-type]'),
                    index: function(type) {
                        for (var i = 0,
                                 n = this.types.length; i < n; i++) {
                            if (this.types.eq(i).data('type') == type) {
                                return i;
                            }
                        }
                        return 0;
                    },
                    left: function() {
                        var idx = this.index(_.type) - 1;
                        if (idx < 0) {
                            idx = this.types.length - 1;
                        } else if (idx >= this.types.length) {
                            idx = 0;
                        }
                        self.tab(this.types.eq(idx).data('type'), this.types.eq(idx).data('category'));
                    },
                    right: function() {
                        var idx = this.index(_.type) + 1;
                        if (idx < 0) {
                            idx = this.types.length - 1;
                        } else if (idx >= this.types.length) {
                            idx = 0;
                        }
                        self.tab(this.types.eq(idx).data('type'), this.types.eq(idx).data('category'));
                    }
                });
            };
            return self;
        })();
        exports.init = function() {
            window._PFM_TIMING[3] = new Date();
            view.init();
        }
    });
define('wq.qiangbao.ui.fail',
    function(require, exports, module) {
        var _tpl = require('tpl_wq.qiangbao.ui.fail');
        var Fail = function(data) {
            data = $.extend({
                    $parent: document.body,
                    callback: function() {}
                },
                data || {});
            var self = {};
            var _ = {
                $fail: null,
                data: null
            };
            var cssSelector = {
                txt: 'p',
                btn: 'btn'
            };
            self.destory = function() {
                _.$fail && _.$fail.off();
            };
            self.show = function(d) {
                d = d || {};
                _.data = d;
                data.show && data.show(_.data);
                if (data.$parent.find(_.$fail).length < 1) {
                    self.destory();
                    init(data);
                }
                _.$fail.find('[data-type]').hide();
                _.$fail.find('[data-type="' + (_.data.type || self.TYPE_PAGE) + '"]').show();
                if (_.data.message) {
                    _.$fail.find(cssSelector.txt).html(_.data.message);
                }
                if (_.data.btn_text) {
                    _.$fail.find('.' + cssSelector.btn).html(_.data.btn_text);
                }
                if (_.data.btn_ptag) {
                    _.$fail.find('.' + cssSelector.btn).attr('ptag', _.data.btn_ptag);
                }
                _.$fail.show();
            };
            self.hide = function() {
                _.$fail.hide();
            };
            var init = function(data) {
                if (_.$fail) {
                    _.$fail.remove();
                }
                if (data.tplType && data.tplType == 'userCenter') {
                    _.$fail = $(_tpl.child_fail2({}));
                    _.$fail.find('.icon').addClass('icon_empty');
                } else if (data.tplType && data.tplType == 'index') {
                    _.$fail = $(_tpl.child_fail2({}));
                    _.$fail.find('.icon').addClass('icon_fail');
                } else {
                    _.$fail = $(_tpl.child_fail({}));
                    cssSelector = {
                        txt: '.empty_notice',
                        btn: 't_btn'
                    };
                }
                $(data.$parent).append(_.$fail);
                _.$fail.on('click',
                    function(e) {
                        var $elem = $(e.srcElement || e.target);
                        if ($elem.hasClass(cssSelector.btn)) { ! _.data.keepShow && self.hide();
                            data.onClick && data.onClick(_.data);
                        }
                    });
            }; (function(data) {
                init(data);
            })(data);
            return self;
        };
        Fail.TYPE_PAGE = 'page';
        Fail.TYPE_NEXT = 'next';
        module.exports = Fail;
    });
define('wq.qiangbao.ui.slider',
    function(require, exports, module) {
        var Slider = function(cfg) {
            var self = {};
            var $dom = cfg.dom;
            var $dom_items = cfg.dom_items;
            var $tab_items = cfg.tab_items;
            var interval = cfg.interval = cfg.interval || 5000;
            var animation_interval = cfg.animation_interval = cfg.animation_interval || 0.4;
            var isAutoMove = cfg.autoMove = cfg.autoMove == undefined ? true: cfg.autoMove;
            var index = 1;
            var items_length = 0;
            var dom_items_length = 0;
            var item_width = 0;
            var timer = null;
            var holdAuto = false;
            var stopOnce = false;
            var touch = null;
            var resetWaiting = false;
            var resetTimer = null;
            var resetIndex = 0;
            var resetWaiting = 0;
            var slider = {
                init: function() {
                    items_length = $dom_items.length;
                    if (items_length < 1) {
                        return;
                    }
                    $dom.css({
                        "-webkit-backface-visibility": "hidden",
                        "-webkit-transform": "translate3D(0,0,0)",
                        "-webkit-transition": "0"
                    });
                    $dom_items.first().clone().insertAfter($dom_items.last());
                    $dom_items.last().clone().insertBefore($dom_items.first());
                    dom_items_length = $dom_items.length + 1;
                    slider.move(index, false);
                    if (items_length == 1) {
                        isAutoMove = false;
                        $tab_items.hide();
                    } else {
                        slider.initTouchEvent();
                    }
                    if (isAutoMove) {
                        slider.autoMove();
                    }
                },
                moveItem: function(idx, animation) {
                    item_width = $dom_items.offset().width;
                    if (animation == undefined) {
                        animation = true;
                    }
                    idx = idx > dom_items_length ? dom_items_length: idx < 0 ? 0 : idx;
                    var left = -idx * item_width;
                    $dom.css({
                        'transition': animation ? 'all ' + animation_interval + 's ease': '0s',
                        'transform': 'translate3d(' + left + 'px,0, 0)',
                        '-webkit-transition': animation ? 'all ' + animation_interval + 's ease': '0s',
                        '-webkit-transform': 'translate3d(' + left + 'px,0, 0)',
                    });
                },
                moveTab: function(idx) {
                    $tab_items.removeClass('on');
                    var no = idx;
                    if (no < 1) {
                        no = 1;
                    } else if (no > items_length) {
                        no = items_length;
                    }
                    $tab_items.filter('[no="' + no + '"]').addClass('on');
                },
                move: function(idx, animation) {
                    slider.moveTab(idx);
                    slider.moveItem(idx, animation);
                    var resetFlag = true;
                    if (idx <= 0) {
                        idx = items_length;
                    } else if (idx >= dom_items_length) {
                        idx = 1;
                    } else {
                        resetFlag = false;
                    }
                    if (resetFlag) {
                        index = idx;
                        slider.moveTab(idx);
                        resetWaiting = true;
                        resetTimer = setTimeout(function() {
                                slider.moveItem(index, false);
                                resetWaiting = false;
                            },
                            animation_interval * 1000);
                    }
                },
                autoMove: function() {
                    timer = setInterval(function() {
                            if (!holdAuto) {
                                if (!stopOnce) {
                                    slider.move(++index);
                                } else {
                                    stopOnce = false;
                                }
                            }
                        },
                        interval);
                },
                initTouchEvent: function() {
                    var dom = $dom.get(0);
                    touch = {
                        handleEvent: function(e) {
                            switch (e.type) {
                                case "touchstart":
                                    this.sp = this.getPosition(e);
                                    holdAuto = true;
                                    stopOnce = true;
                                    break;
                                case "touchmove":
                                    e.preventDefault();
                                    break;
                                case "touchend":
                                case "touchcancel":
                                    if (!resetWaiting) {
                                        this.ep = this.getPosition(e);
                                        var val = this.ep.x - this.sp.x;
                                        if (Math.abs(val) > 30) {
                                            if (val > 0) {
                                                e.preventDefault();
                                                self.move(--index, true);
                                            } else {
                                                e.preventDefault();
                                                self.move(++index, true);
                                            }
                                        }
                                    }
                                    holdAuto = false;
                                    break;
                                case "webkitTransitionEnd":
                                    e.preventDefault();
                                    break;
                            }
                        },
                        getPosition: function(e) {
                            var touch = e.changedTouches ? e.changedTouches[0] : e;
                            return {
                                x: touch.pageX,
                                y: touch.pageY
                            };
                        }
                    };
                    dom.addEventListener("touchstart", touch, false);
                    dom.addEventListener("touchmove", touch, false);
                    dom.addEventListener("touchend", touch, false);
                    dom.addEventListener("touchcancel", touch, false);
                    dom.addEventListener("webkitTransitionEnd", touch, false);
                },
                destory: function() {
                    timer && clearInterval(timer);
                    resetTimer && clearTimeout(resetTimer);
                    $dom.off();
                }
            };
            slider.init();
            self.destory = slider.destory;
            self.move = slider.move;
            return self;
        };
        module.exports = Slider;
    });
define('wq.qiangbao.ui.touch',
    function(require, exports, module) {
        var Touch = function(data) {
            var self = {};
            var _ = {}; (function() {
                var touch = {
                    handleEvent: function(e) {
                        switch (e.type) {
                            case "touchstart":
                                this.sp = this.getPosition(e);
                                break;
                            case "touchmove":
                                break;
                            case "touchend":
                            case "touchcancel":
                                this.ep = this.getPosition(e);
                                var xval = this.ep.x - this.sp.x;
                                var yval = this.ep.y - this.sp.y;
                                if (Math.abs(xval) > Math.abs(yval) && Math.abs(yval) < 30) {
                                    if (Math.abs(xval) > 100) {
                                        if (xval > 0) {
                                            setTimeout(function() {
                                                    data.left && data.left();
                                                },
                                                50);
                                        } else {
                                            setTimeout(function() {
                                                    data.right && data.right();
                                                },
                                                50);
                                        }
                                        try {
                                            e.preventDefault();
                                        } catch(e) {}
                                    }
                                }
                                break;
                        }
                    },
                    getPosition: function(e) {
                        var touch = e.changedTouches ? e.changedTouches[0] : e;
                        return {
                            x: touch.pageX,
                            y: touch.pageY
                        };
                    }
                };
                var dom = data.dom || document.body;
                dom.addEventListener("touchstart", touch, false);
                dom.addEventListener("touchmove", touch, false);
                dom.addEventListener("touchend", touch, false);
                dom.addEventListener("touchcancel", touch, false);
            })();
        }
        module.exports = Touch;
    });
define('wq.qiangbao.urlredirect',
    function(require, exports, module) {
        var _url = require('url');
        _url.replaceParam = function(param, value, url, forceReplace) {
            url = url || location.href;
            var reg = new RegExp("([\\?&]" + param + "=)[^&#]*"),
                hash = url.indexOf('#') == -1 ? '': url.substring(url.indexOf('#'));
            if (!url.match(reg)) {
                if ( !! hash) {
                    url = url.substring(0, url.indexOf('#'));
                }
                return ((url.indexOf("?") == -1) ? (url + "?" + param + "=" + value) : (url + "&" + param + "=" + value)) + hash;
            }
            if (forceReplace) {
                return url.replace(reg, "$1" + value);
            }
            return url;
        };
        var jumpLinks = window.__CONFIG.jumpLinks;
        try {
            if (jumpLinks && jumpLinks.length) {
                var now = new Date();
                for (var i = 0; i < jumpLinks.length; i++) {
                    if (now >= new Date(jumpLinks[i].actStart) && now <= new Date(jumpLinks[i].actEnd) && jumpLinks[i].actLink) {
                        var iUrl = _url.parseUrl(jumpLinks[i].actLink);
                        if ((iUrl.host + iUrl.path) != (window.location.host + window.location.pathname)) {
                            window.location = _url.replaceParam('from', _url.getUrlParam('from'), jumpLinks[i].actLink, 1);
                            return;
                        }
                    }
                }
            }
            var iUrl = _url.parseUrl(URL_INDEX);
            if ((iUrl.host + iUrl.path) != (window.location.host + window.location.pathname)) {
                window.location = URL_INDEX;
            }
        } catch(e) {}
    });