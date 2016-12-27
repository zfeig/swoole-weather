<?php
  $client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
  
  $client->on('connect',function($cli){
    $cli->send("hello,world! time is: ".date('Y-m-d H:i:s',time())."\n");
  });

  $client->on('receive',function($cli,$data){
    echo "Received:".$data."\n";
    $cli->close();
  });

  $client->on('error',function($cli){
    echo "connect faild!\n";
  });

  $client->on('close',function($cli){
    echo "Connect close!\n";
  });

  $client->connect('127.0.0.1',9501,0.5);

?>
