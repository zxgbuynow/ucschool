/**
 * Created by cm on 2016/5/11.
 */
booking = new (function(){
    var bookingURL = '/booking/';
    window.isSubmitting = false;
    var oBookInfoTemplate = {
        'logined'       :   false,
        'name'          :   '',
        'phone'         :   '',
        'number'        :   0,
        'title'         :   '',
        'categoryType'  :   '',
        'url'           :   '',
        'id'            :   null,
        'time'          :   '',
        'orderType'     :   0
    };
    var _bookCallback = function(oData,callback){
        if(callback && typeof(callback)==='function'){
            callback(oData);
        }
    };
    function isMobile(){
        var sHref = window.location.href;
        if(sHref.indexOf('http://www.') > -1){
            return false;
        }
        if(sHref.indexOf('https://www.') > -1){
            return false;
        }
        return true;
    };
    function submitBookInfo(oBookInfo,callback,bSkipValidate){
        bSkipValidate = bSkipValidate || false
        if(window.isSubmitting) { //如果用户已点击，正在提交中，则禁止重复提交
            callback({'success':false,'message':'正在提交您的信息,请稍后'});
            return false;
        }
        window.isSubmitting = true;
        var processedResult = _preProcessBookInfo(oBookInfo,bSkipValidate);
        if(processedResult === false){
            callback({'success':false,'message':'请检查您输入的信息是否正确'});
        }
        $.ajax({
            url: bookingURL,
            dataType: 'json',
            //timeout:3000,
            data: processedResult,
            type: 'POST',
            complete: function(xhr, textStatus){
                if(textStatus === 'success'){
                    var oData = JSON.parse(xhr.responseText);
                    if(oData.result){
                        _bookCallback({'success':true},callback);
                    }else{
                        _bookCallback({'success':false,'message':oData.message,'data':oData},callback);
                    }
                }else{
                    _bookCallback({'success':false},callback);
                }
                window.isSubmitting = false;
            }
        });
    };
    function _preProcessBookInfo(oData,bSkipValidate){
        if(!bSkipValidate && _validateData(oData)){
            return false;
        }
        var oBookingInfo = {};
        var now=new Date();
        oBookInfoTemplate.time = now.getFullYear()+"/"+(now.getMonth()+1)+"/"+now.getDate()+" "+now.getHours()+":"+now.getMinutes()+":"+now.getSeconds();
        $.extend(oBookingInfo, oBookInfoTemplate, oData);
        var sUrl = window.location.href;
        oBookingInfo.url = sUrl;
        oBookingInfo.id = _getIdByUrl(sUrl,oBookingInfo);
        return oBookingInfo;
    }
    function _getIdByUrl(sUrl,oBookingInfo){
        var id = null;
        var idInfo = sUrl.split('/product-');
        if(idInfo.length > 1){
            id = parseInt(idInfo[1]);
        }else{
            if(id == undefined || id == '' || empty(id)){
                id = oBookingInfo.id;
            }
        }
        return id;
    }
    function _validateData(oData){
        if(oData || (oData==null)){
            return false;
        }
        if(typeof oData.phone !== 'number'){
            return false;
        }
        var phoneNumber = oData.phone;
        var phoneRule = /^1[34578]\d{9}$/;
        if(!phoneRule.test(phoneNumber)){
            return false;
        }
        if(oData.name){
            var oNameResult = _validateChinese(oData.name);
            if(!oNameResult.success){
                return false;
            }
        }
    }

    /**
     * 检查名字中是否包含非中文字符
     * @param sName
     * @returns {boolean}
     * @private
     */
    function _validateChinese(sName){
        var reg=/^[\u4E00-\u9FA5]+$/;
        if(reg.test(sName)){
            return {
                'result':true,
                'message':''
            };
        }else{
            return {
                'result':false,
                'message':'请检查输入的名字'
            };
        }
    }

    return {
        'submitBookInfo' : submitBookInfo,
        'isMobile':isMobile
    };
})();