(function(mui, window, document, undefined) {
    mui.init();

  
    var get = function(id) {
        return document.getElementById(id);
    };
    var qsa = function(sel) {
        return [].slice.call(document.querySelectorAll(sel));
    };
    var ui = {
        cerfornt: get('cerfornt'),
        cerback: get('cerback'),
        submit: get('sendMail')
    };
    ui.clearForm = function() {
        ui.cerfornt.innerHTML = '';
        ui.cerback.innerHTML = '';
        ui.newceriuPlaceholder();
        ui.newceridPlaceholder();
    };
    ui.getFileInputArray = function() {
        return [].slice.call(ui.cerfornt.querySelectorAll('input[type="file"]'));

    };

    ui.getbcFileInputArray = function() {
        return [].slice.call(ui.cerback.querySelectorAll('input[type="file"]'));

    };
    
    ui.getcdFileInputIdArray = function() {
        var fileInputArray = ui.getbcFileInputArray();
        var idArray = [];
        fileInputArray.forEach(function(fileInput) {
            if (fileInput.value != '') {
                idArray.push(fileInput.getAttribute('id'));
            }
        });
        return idArray;
    };
    
    
    ui.getFileInputIdArray = function() {
        var fileInputArray = ui.getFileInputArray();
        var idArray = [];
        fileInputArray.forEach(function(fileInput) {
            if (fileInput.value != '') {
                idArray.push(fileInput.getAttribute('id'));
            }
        });
        return idArray;
    };
    
    //ceriUpList
    var imageIndexIdNum = 0;
    ui.newceriuPlaceholder = function() {
        var fileInputArray = ui.getFileInputArray();
        if (fileInputArray &&
            fileInputArray.length > 0 &&
            fileInputArray[fileInputArray.length - 1].parentNode.classList.contains('space')) {
            return;
        }
        imageIndexIdNum++;
        var placeholder = document.createElement('div');
        placeholder.setAttribute('class', 'image-item space');
        
        var fileInput = document.createElement('input');
        fileInput.setAttribute('type', 'file');
        fileInput.setAttribute('accept', 'image/*');
        fileInput.setAttribute('id', 'image-' + imageIndexIdNum);
        fileInput.addEventListener('change', function(event) {
            var file = fileInput.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function() {
                    //处理 android 4.1 兼容问题
                    var base64 = reader.result.split(',')[1];
                    fileInput.setAttribute('vase', base64);
                    var dataUrl = 'data:image/png;base64,' + base64;
                    //
                    placeholder.style.backgroundImage = 'url(' + dataUrl + ')';
                }
                reader.readAsDataURL(file);
                placeholder.classList.remove('space');
                // ui.newceriuPlaceholder();
            }
        }, false);
        placeholder.appendChild(fileInput);
        ui.cerfornt.appendChild(placeholder);
    };
    ui.newceriuPlaceholder();
    //end ceriUpList
    //
    var imageIndexIdNum1 = 0;
    ui.newceridPlaceholder = function() {
        var fileInputArray = ui.getbcFileInputArray();
        if (fileInputArray &&
            fileInputArray.length > 0 &&
            fileInputArray[fileInputArray.length - 1].parentNode.classList.contains('space')) {
            return;
        }
        imageIndexIdNum1++;
        var placeholder1 = document.createElement('div');
        placeholder1.setAttribute('class', 'image-item space');
        var closeButton = document.createElement('div');
        closeButton.setAttribute('class', 'image-close');
        closeButton.innerHTML = 'X';
        closeButton.addEventListener('click', function(event) {
            event.stopPropagation();
            event.cancelBubble = true;
            setTimeout(function() {
                ui.ceriDownList.removeChild(placeholder1);
            }, 0);
            return false;
        }, false);
        var fileInput1 = document.createElement('input');
        fileInput1.setAttribute('type', 'file');
        fileInput1.setAttribute('accept', 'image/*');
        fileInput1.setAttribute('id', 'image1-' + imageIndexIdNum1);
        fileInput1.addEventListener('change', function(event) {
            var file = fileInput1.files[0];
            if (file) {
                var reader1 = new FileReader();
                reader1.onload = function() {
                    //处理 android 4.1 兼容问题
                    var base64 = reader1.result.split(',')[1];
                    fileInput1.setAttribute('vase1', base64);
                    var dataUrl1 = 'data:image/png;base64,' + base64;
                    //
//                  console.log(dataUrl1+'////////////////111111111111')
                    placeholder1.style.backgroundImage = 'url(' + dataUrl1 + ')';
                }
                reader1.readAsDataURL(file);
                placeholder1.classList.remove('space');
                // ui.newceridPlaceholder();
            }
        }, false);
        placeholder1.appendChild(fileInput1);
        ui.cerback.appendChild(placeholder1);
    };
    ui.newceridPlaceholder();
    //end ceriDownList
        
})(mui, window, document, undefined);
