(function(mui, window, document, undefined) {
	mui.init();
	
	var get = function(id) {
		return document.getElementById(id);
	};
	var qsa = function(sel) {
		return [].slice.call(document.querySelectorAll(sel));
	};
	var ui = {
		avatar: get('avatar'),
		submit: get('sendMail')
	};
	ui.clearForm = function() {
		ui.avatar.innerHTML = '';
		ui.newceriuPlaceholder();
	};
	ui.getFileInputArray = function() {
		return [].slice.call(ui.avatar.querySelectorAll('input[type="file"]'));

	};
	
	ui.getcdFileInputIdArray = function() {
		var fileInputArray = ui.getcdFileInputArray();
		var idArray = [];
		fileInputArray.forEach(function(fileInput) {
			if (fileInput.value != '') {
				idArray.push(fileInput.getAttribute('id'));
			}
		});
		return idArray;
	};
	
	ui.getbuFileInputIdArray = function() {
		var fileInputArray = ui.getbuFileInputArray();
		var idArray = [];
		fileInputArray.forEach(function(fileInput) {
			if (fileInput.value != '') {
				idArray.push(fileInput.getAttribute('id'));
			}
		});
		return idArray;
	};
	
	ui.getbdFileInputIdArray = function() {
		var fileInputArray = ui.getbdFileInputArray();
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
		ui.avatar.appendChild(placeholder);
	};
	ui.newceriuPlaceholder();
	//end ceriUpList
	
	
})(mui, window, document, undefined);