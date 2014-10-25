<?php
/**
 * @var $this yii\web\View
 * @var $acceptUrl string
 * @var $imageSelector string
 * @var $fileInputName string
 * @var $resultSize array
 * @var $customParams array
 * @var $cropParams array
 */
use webvimark\extensions\fancybox\Fancybox;

?>
<a href="" id='cropper-link' title="<span id='crop-click-start' class='btn btn-info btn-block'>Upload</span>"></a>

<span 
        data-shrinked-width=''
        data-shrinked-height='' 
        data-x='' 
        data-y=''
        data-x2='' 
        data-y2=''
        data-w='' 
        data-h=''
        id='cropper-storage' style='display:none'></span>


<?php

$this->registerJs(<<<JS

$(document).on('click', '#crop-click-start', function(){
	cropImage();
});
// Send request with cropped params
function cropImage()
{
        var storage = $('#cropper-storage');

	var data =  {
		'cropper-data':'aga',
		'x': storage.attr('data-x'),
		'y': storage.attr('data-y'),
		'w': storage.attr('data-w'),
		'h': storage.attr('data-h'),
		'x2': storage.attr('data-x2'),
		'y2': storage.attr('data-y2'),
		'shrinked-width': storage.attr('data-shrinked-width'),
		'shrinked-height': storage.attr('data-shrinked-height')
	};

	var rs = $resultSize;

	if ( rs ) {
		data['result-size-w'] = rs[0];
		data['result-size-h'] = rs[1];
	}

	var cp = $customParams;
	$.each(cp, function(k, v) {
		data[k] = v;
	});

        $.get('$acceptUrl', data)
		.success(function(data){
			if ( '$imageSelector' ) {
				$('$imageSelector').html("<img src='" + data + "' />");
			}
		}).complete(function(){
			$.fancybox.close();
		});
}

// Simple event handler, called from onChange and onSelect
// event handlers, as per the Jcrop invocation above
function showCoords(c)
{
        var storage = $('#cropper-storage');
        var img = $( '.fancybox-inner' ).find( 'img' );

        storage.attr('data-shrinked-width', img.width());
        storage.attr('data-shrinked-height', img.height());

        storage.attr('data-x', c.x);
        storage.attr('data-y', c.y);
        storage.attr('data-w', c.w);
        storage.attr('data-h', c.h);
        storage.attr('data-x2', c.x2);
        storage.attr('data-y2', c.y2);
}

var jcropParams = $cropParams;
jcropParams.onChange = showCoords;
jcropParams.onSelect = showCoords;
jcropParams.bgColor = '';

// Attach fancybox to the cropper link
$('#cropper-link').fancybox({
	//fitToView: false,
	helpers: {
		title: { type: 'inside' },
		overlay : {closeClick: false}
	},
	afterShow : function(){
		$( '.fancybox-inner' ).find( 'img' ).Jcrop(jcropParams);
	},
	afterClose: function(){
		$.get('$acceptUrl', { 'cropper-deleteTmpImage': 'aga' });
	}
});

// When select new image
$('input[name="$fileInputName"]').on('change', function(event){
	files = event.target.files;

	event.stopPropagation();
	event.preventDefault();

	var data = new FormData();
	data.append('$fileInputName', files[0]);

	var cp = $customParams;
	$.each(cp, function(k, v) {
		data.append(k, v);
	});

	$(this).val("");

	$.ajax({
		url: '$acceptUrl',
		type: 'POST',
		data: data,
		cache: false,
		dataType: 'json',
		processData: false,
		contentType: false
	}).success(function(response){
		if ( response.success )
		{
			$('#cropper-link').attr('href', response.file).trigger('click');
		}
		else
		{
			alert(response.message);
		}
	}).error(function(jqXHR, textStatus, errorThrown){
		console.log(textStatus);
	});
});
JS
);
?>


<?php Fancybox::widget() ?>
