<?php

$ws = new swoole_websocket_server('0.0.0.0', 8000);

$ws->user_c = [];

$ws->set([
    'daemonize' => true,
]);

$ws->on('open', function($ws, $request) {
    $ws->user_c[$request->fd] = [
        'name' => '游客' . $request-> fd,
        'fd' => $request->fd,
    ];
   // unset($list[$request->fd]);
   //$ws->push($request->fd, json_encode(['type' => 'list', 'data' => $list]));
   
});



$ws->on('message', function($ws, $request) {
    $data = json_decode($request->data, true);
    $fd = $request->fd;
    if (isset($data['type']) && $data['type'] == 'name') {
	if ($data['name'] != 'null') {
	    $ws->user_c[$fd]['name']= $data['name'];
        }
        $name = $data['name'] == 'null' ? $ws->user_c[$fd]['name'] : $data['name'];
        $ws->push($fd, json_encode(['type' => 'msg', 'data' => $name]));
	foreach ($ws->user_c as $k => $v) {
          $list = $ws->user_c;
          unset($list[$k]);
        $ws->push($k, json_encode(['type' => 'list', 'data' => $list]));
    }

    } else if (isset($data['type']) && $data['type'] == 'msg') {
        $ws->push($data['fd'], json_encode(['type' => 'speak', 'from' => $data['name'], 'fd' => $request->fd, 'data' => $data['msg']]));
    } else {
	$ws->push($fd, json_encode(['type' => 'msg', 'data' => 'error']));
    }
});


$ws->on('close', function($ws, $fd) {
   var_dump($fd);
   unset($ws->user_c[$fd]);

});

$ws->start();
