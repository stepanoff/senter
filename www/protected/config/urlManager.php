<?php 
return array(
	'urlFormat'=>'path',
	'showScriptName'=>false,
	'rules'=>array(
        '/admin/<_c:([a-zA-Z0-9]+)>/<_a:([a-zA-Z0-9]+)>/<id:([0-9]+)>' => 'admin/<_c>/<_a>',
        '/admin/<_c:([a-zA-Z0-9]+)>' => 'admin/<_c>/index',
        '/admin/<_c:([a-zA-Z0-9]+)>/edit' => 'admin/<_c>/edit',
	),
);
?>