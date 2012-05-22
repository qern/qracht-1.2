<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CKEIP DEMO</title>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>

<script src="ckeip.js" type="text/javascript"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>


<script type="text/javascript">
$(document).ready(function() {

/// Example 1 - no options
	$('#my_div_1').ckeip({
		e_url: 'test.php',
		});



/// Example 2 - All options set.
	$('#my_div').ckeip({
		e_url: 'test.php',
		e_width:100, 
		e_height:100,
		e_hover_color:'#666666',


		data: {
    example_key1 : 'example_value',
    example_key2 : 'example_value2'
              },
		
		ckeditor_config : {
		
		toolbar:
		[
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote']


		]
	}
		
		},
		
		function (response) {
			alert(response);
			}
		
		);
	
	
});
</script>
<style type="text/css">
.main {
	width: 800px;
	margin-top: 50px;
	margin-right: auto;
	margin-left: auto;
}
</style>
</head>

<body>
<p>CKeip Demo - <a href="http://www.bitsntuts.com/jquery/ckeditor-edit-in-place-jquery-plugin">Back To Post</a></p>
<div class="main">
<div id="my_div_1">

    <h1>Basic Config with no options set </h1>
    <p><img src="images/sample.jpg" alt="Sample image" width="300" height="200" hspace="10" vspace="10" align="left" />Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec nisl   tincidunt massa cursus pharetra a a erat. Pellentesque habitant morbi   tristique senectus et netus et malesuada fames ac turpis egestas. Fusce   quis rhoncus enim. Sed velit ante, laoreet ornare ultrices nec, tempus   non lectus. Vestibulum elementum enim quis ante sodales venenatis. Proin   mattis molestie ligula, eget accumsan dolor auctor egestas. Sed et   magna vitae tellus vehicula facilisis vulputate vel lacus. In commodo   fringilla ipsum at ultrices. Nulla sit amet magna eget ligula ultrices   porta ac in mi. Pellentesque ut turpis eget libero bibendum iaculis. Ut   interdum massa at enim sollicitudin elementum. Proin venenatis est vel   orci dapibus ornare. Lorem ipsum dolor sit amet, consectetur adipiscing   elit. </p>
    <p> Integer a velit lorem. Phasellus nisl erat, ultrices eu dapibus at,   consectetur a sapien. In interdum odio sit amet odio vestibulum   consectetur. Donec quis volutpat turpis. Integer gravida, erat ac   pharetra scelerisque, tortor mauris vestibulum turpis, nec semper orci   nulla nec sapien. Cras scelerisque blandit placerat. Aliquam risus nisl,   </p>
  </div>
<div id="my_div">

    <h1>Advanced config </h1>
    <p><img src="images/sample.jpg" alt="Sample image" width="300" height="200" hspace="10" vspace="10" align="left" />Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec nisl   tincidunt massa cursus pharetra a a erat. Pellentesque habitant morbi   tristique senectus et netus et malesuada fames ac turpis egestas. Fusce   quis rhoncus enim. Sed velit ante, laoreet ornare ultrices nec, tempus   non lectus. Vestibulum elementum enim quis ante sodales venenatis. Proin   mattis molestie ligula, eget accumsan dolor auctor egestas. Sed et   magna vitae tellus vehicula facilisis vulputate vel lacus. In commodo   fringilla ipsum at ultrices. Nulla sit amet magna eget ligula ultrices   porta ac in mi. Pellentesque ut turpis eget libero bibendum iaculis. Ut   interdum massa at enim sollicitudin elementum. Proin venenatis est vel   orci dapibus ornare. Lorem ipsum dolor sit amet, consectetur adipiscing   elit. </p>
    <p> Integer a velit lorem. Phasellus nisl erat, ultrices eu dapibus at,   consectetur a sapien. In interdum odio sit amet odio vestibulum   consectetur. Donec quis volutpat turpis. Integer gravida, erat ac   pharetra scelerisque, tortor mauris vestibulum turpis, nec semper orci   nulla nec sapien. Cras scelerisque blandit placerat. Aliquam risus nisl,   commodo id feugiat gravida, lobortis ullamcorper sem. Vestibulum ante   ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;   Nulla a nisi sem, vitae dapibus massa. Suspendisse vehicula, est non   dictum ultricies, purus tellus sollicitudin arcu, sed hendrerit velit   risus non ante. Aliquam sed sem justo, venenatis rhoncus felis. Mauris a   turpis ac mauris sollicitudin dapibus a sit amet massa. In aliquet   pretium enim, in vulputate libero gravida a. Lorem ipsum dolor sit amet,   consectetur adipiscing elit. Maecenas blandit massa sit amet justo   semper quis vehicula nulla scelerisque. Cras dictum facilisis dolor, at   dapibus nunc pulvinar ut. Aliquam ultrices fringilla odio quis   hendrerit. Maecenas suscipit semper nulla, nec molestie orci eleifend   quis. </p>

</div>


</div>
</body>
</html>
