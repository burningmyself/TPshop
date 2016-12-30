/**
 * Created by yang_ on 2016/10/13.
 */
(function(w) {
    w.slider = function(cfg) {
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
                item_width = $dom_items.width();
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
})(window);

(function() {
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
    return Touch;
})(window);