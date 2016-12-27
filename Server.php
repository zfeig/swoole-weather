<?php
  //类库引用
  require_once __DIR__.DIRECTORY_SEPARATOR.'weather.php';
  
  //获取配置项
  $config = Weather::getConfig();

  //查询即将采集的城市id数据，启动执行一次
  Weather::getPdo($config);
  $weatherInfo = Weather::query("SELECT id FROM `weather_city_list` WHERE status = :status",[':status'=>1],true);

  //统计采集长度
  $quitNum = count($weatherInfo);


  //开启后台服务
  $serv = new swoole_server("127.0.0.1",9501);
  $serv->set([
   'worker_num'=> 4,
   'daemonize'=>true,
   'max_request'=>5000,
   'log_file' =>'weather.log',
   'task_worker_num'=>8
  ]); 

  
  //处理客户端连接
  $serv->on('connect',function($serv,$fd){
    echo "Client:Connected! fd id:$fd on ".date('Y-m-d H:i:s',time())."\n";
  });

  //处理接收到客户端的消息
  $serv->on('receive',function($serv,$fd,$from_id,$data) use($quitNum){

  //打印接收到客户端的消息
   echo $data."\n";

  //添加定时器分配任务
  $count = 0; 
  $serv->tick(10000, function($id) use(&$count,$serv,$quitNum){
     if($count > $quitNum-1){//采集完毕清除当前定时器
         $serv->clearTimer($id);
         $count = 0;
         Weather::log("current timer $id is cleared!");
       }else{//将数据发送到异步任务进程处理
         $serv->task($count); 
         $count++;
         Weather::log("====================Timer_$id: run $count times in:".date('Y-m-d H:i:s',time())."====================");
       }
    });
   
    //给客户端发送消息通知客户端关闭
    $serv->send($fd,"close client!");

  });
 

  //异步任务处理耗时操作，负责具体数据采集业务逻辑
  $serv->on('task',function($serv,$task_id,$from_id,$data) use($weatherInfo){
      //获取参数
      Weather::log("task[$task_id] get index:$data");
      $id = $weatherInfo[$data]['id'];
      
      //处理任务
      $url = "https://free-api.heweather.com/v5/weather?city=$id&key=bf5297f3ab594499b654732884dc561f";
      $json = Weather::getWeatherByUrl($url);
      Weather::saveData($json); 
      
      //完成任务
      $serv->finish("$data");
  });
  

  //处理定时器回调
  $serv->on('timer',function($serv,$interval){
    Weather::log("now is:".date('Y-m-d H:i:s',time()));
  });


  //结束异步任务工作
  $serv->on('finish',function($serv,$task_id,$data){
     Weather::log("task[$task_id] finished!");
  });


  //监听客户端关闭
  $serv->on('close',function($serv,$fd){
    echo "Client: $fd closed on ".date('Y-m-d H:i:s',time())."\n";
  });

  //启动服务器
  $serv->start();
?>
