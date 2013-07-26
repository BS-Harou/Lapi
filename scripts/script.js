var	
	d = document,
	xPos = false,
	yPos = false,
	form = d.createElement('form'),
	post
;

form.action = '/smaz';
form.method = 'post';

d.addEventListener('touchstart', handleTouchStart, false);
d.addEventListener('touchend', handleTouchEnd, false);



d.addEventListener('DOMContentLoaded', function() {
	var fontTags = d.querySelectorAll('font');
	[].forEach.call(fontTags, function(fontTag) {
		if (fontTag.color != 'black' && fontTag.color != '#000000') {
			fontTag.addEventListener('click', handleSpoilerClick);	
		}
	});

	var hiddenImages = d.querySelectorAll('img[data-src]');
	[].forEach.call(hiddenImages, function(img) {
		img.addEventListener('click', handleImgClick);
	});
	
	
	//document.body.addEventListener('click', handleClick);
	
}, false);

function handleImgClick(e) {
	this.src = this.getAttribute('data-src');
	this.removeAttribute('data-src');
	this.removeEventListener('click', handleImgClick);
	e.preventDefault();
	e.stopPropagation();
}


/**
 * Swaps time/data with ID.
 * Called in chain from click event added on each time element  
 **/
function swapTime(what) {
	var t = what; 
	if (t.className == 'datetime') {
		if (t.getAttribute('data-time')) {
			t.innerHTML = t.getAttribute('data-time');
			t.setAttribute('data-time', '');
		} else {
			t.setAttribute('data-time', t.innerHTML);
			t.innerHTML = 'ID: ' + t.parentNode.parentNode.parentNode.querySelector('[data-id]').getAttribute('data-id');
		}
	}
}

function handleSpoilerClick(e) {
	e.target.removeAttribute('color');
	this.removeEventListener('click', handleClick);
}



function handleTouchStart(e) {
	if (e.target.className === 'avatar') {
		xPos = e.touches.item(0).pageX;
		yPos = e.touches.item(0).pageY;
		post = e.target;
	} else {
		xPos = yPos = post = false;
	}
}


function handleTouchEnd(e) {
	var t = e.changedTouches.item(0);
	if (xPos && t.pageX-xPos > 200 && Math.abs(t.pageY - yPos) < 80) {
		if (co = post.getAttribute('data-candelete')) {
			deletePost(post.getAttribute('data-id'), co);
		} else {
			alert('Nemáte práva na to smazat tento příspěvek.');
		}
	} 
	
	xPos = yPos = post = false;
}

function getRemType(co) {
	if (co == 'posta' || co == 'uschovna') {
		return co;
	}
	return 'klub';
}

function deletePost(p, co) {
	if (confirm('Chcete určitě smazat tento příspěvek?')) {
		addValue(form, 'type', getRemType(co));
		addValue(form, 'post', p);
		form.submit();
	}
}

function addValue(form, name, value) {
	var tmp = d.createElement('input');
	tmp.type = 'hidden';
	tmp.name = name;
	tmp.value = value;
	form.appendChild(tmp);
}

