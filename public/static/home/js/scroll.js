var Album = function (option) {
    this.$moveObj = $(option.moveObj);
    this.direction = option.direction || "left";
    this.speed = option.speed || 300;
    this.step = option.step || 3;
    this.autoStart = option.autoStart || false;
    this.delay = option.delay || 3000;
    this.stopOnHover = option.stopOnHover || false;
    this.objPrev = option.objPrev || null;
    this.objNext = option.objNext || null;
    this._toDir = this.direction;
    this.Init();
};
Album.prototype.Init = function () {
    var _this = this;
    var moveChilds = _this.$moveObj.children();
    var allWH = 0;
    var wOrh;
    $.each(moveChilds, function (i, o) {
        switch (_this.direction) {
            case ("left"): wOrh = "width"; allWH += $(o).outerWidth(); break;
            case ("top"): wOrh = "height"; allWH += $(o).outerHeight(); break;
        }
    });
    _this.$moveObj.css({ position: "absolute" });
    _this.$moveObj.css(wOrh, allWH);
    if (_this.objPrev) {
        $(_this.objPrev).on('click', function () {
            _this._toDir = _this.direction;
            clearInterval(_this.timer);
            movePrev();
            if (_this.autoStart) {
                _this.timer = setInterval(movePrev, _this.delay);
            }
        });
    }
    if (_this.objNext) {
        $(_this.objNext).on('click', function () {
            _this._toDir = "-" + _this.direction;
            clearInterval(_this.timer);
            moveNext();
            if (_this.autoStart) {
                _this.timer = setInterval(moveNext, _this.delay);
            }
        });
    }
    if (_this.autoStart) {
        setInter();
        if (_this.stopOnHover) {
            _this.$moveObj.mouseover(function () {
                clearInterval(_this.timer);
            }).mouseout(setInter);
        }
    }
    function setInter() {
        if (_this._toDir.indexOf("-") == -1) {
            _this.timer = setInterval(moveNext, _this.delay);
        }
        else {
            _this.timer = setInterval(movePrev, _this.delay);
        }
    };
    function moveNext() {
        var objS = _this.getScrollWH("top");
        var attr = {};
        attr[_this.direction] = -objS.scrollWH;
        _this.$moveObj.stop().animate(attr, _this.speed, function () {
            _this.$moveObj.css(_this.direction, 0);
            $(objS.objScrolled).appendTo(_this.$moveObj);
        });
    };
    function movePrev() {
        var attr = {};
        attr[_this.direction] = 0;
        var objS = _this.getScrollWH("bottom");
        _this.$moveObj.css(_this.direction, -objS.scrollWH + 'px');
        $(objS.objScrolled).prependTo(_this.$moveObj);
        _this.$moveObj.stop().animate(attr, _this.speed);
    };
};
Album.prototype.getScrollWH = function (type) {
    var _this = this;
    var objScrolled = [];
    var scrollWH = 0;
    $.each(_this.$moveObj.children(), function (i, o) {
        if (type == "top") {
            if (i < _this.step) {
                objScrolled.push(o);
                switch (_this.direction) {
                    case ("left"): scrollWH += $(o).outerWidth(); break;
                    case ("top"): scrollWH += $(o).outerHeight(); break;
                }
            }
        }
        else if (type == "bottom") {
            if (i >= (_this.$moveObj.children().length - _this.step) && i < _this.$moveObj.children().length) {
                objScrolled.push(o);
                switch (_this.direction) {
                    case ("left"): scrollWH += $(o).outerWidth(); break;
                    case ("top"): scrollWH += $(o).outerHeight(); break;
                }
            }
        }
    });
    return { objScrolled: objScrolled, scrollWH: scrollWH };
};