# swoole-weather
swoole爬虫，抓取天气预报信息  

原理：基于swoole+crontab+异步任务+定时器 实现对天气预报api数据的抓取
  crontab定时唤起客户端，客户端连接swoole服务器，开启异步任务抓取天气预报数据
# 启动
  开启爬虫服务端
  
```
   php Server.php
```
  
  按照crontab中的规则，开启新的定时任务
  
```
  crontab -e 
  0 11,17 * * * /bin/php -f /data/swoole/server_client_timer/Client.php
```

  
