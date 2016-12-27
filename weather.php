<?php

class Weather{
        private static $pdo = null;

        /**
         * @获取数据库配置
         * @return array
         */
        public static function getConfig(){
                return array(
                    "host" => "127.0.0.1",
                    "user" => "root",
                    "password" => "root",
                    "dbname" => "weather"
                );
        }

        /**
         * @模拟get请求
         * @param $url
         * @return mixed|string
         */
        public static function httpGet($url){
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            $https = substr($url,0,8) == "https://" ? true:false;
            if($https){
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查  
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在  
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转  
             curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer  
            }
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,1000);
            curl_setopt($ch,CURLOPT_HEADER,0);
            $res = "";
            $res = curl_exec($ch);
            curl_close($ch);
            return $res;
        }

      
            /**
             * @模拟post请求
             * @param $url
             * @param array $query
             * @param array $header
             * @return mixed
             */
        public static function httpPost($url,$query=array(),$header=array("Content-Type" =>"application/x-www-form-urlencoded")) {
            $ch =curl_init();
            $query = http_build_query($query);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            curl_setopt($ch, CURLOPT_POST, true );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            $ret = curl_exec($ch);
            curl_close($ch);
            return $ret;
        }

        /**
         * @ 获取数据库连接对象
         * @param $config
         * @return null|PDO
         */
        public static function getPdo($config){
            $dsn = "mysql:host=".$config["host"].";dbname=".$config['dbname'];
            //echo $dsn."<br/>";
            if(empty(self::$pdo)){
                try{
                    self::$pdo = new PDO($dsn,$config["user"],$config["password"]);
                    //echo "connect ok";
                }catch(PDOException $e){
                    exit("connection failed,please check");
                    //echo $e->getMessage();
                }
            }
            return self::$pdo;
        }

        /**
         * @查询sql语句
         * @param $sql
         * @param $params
         * @param bool $fetch true则获取查询结果
         * @return array
         */
        public static function query($sql,$params,$fetch = false){
            if(empty(self::$pdo)){
                exit("<br/>instance pdo first");
            }
            $res = self::$pdo->prepare($sql);
            foreach($params as $k => $v){
                //echo $k." bind ".$v."<br/>";
                $res->bindValue($k,$v);
            }
            $res->execute();
            if($fetch){
                $retData =array();
                while($row = $res->fetch(PDO::FETCH_ASSOC)){
                  array_push($retData,$row);
                }
                return $retData;
            }
        }

         /**
        * @解析get请求的参数
        * @return array
        */
        public static function parseQueryInfo(){
            $retData = [];
            parse_str($_SERVER["QUERY_STRING"],$apiParam);
            foreach ($apiParam as $k => $v){
                $apiParam[urlencode($k)] = urlencode($v);//中文转码
            }
            $retData["ip"] = $_SERVER["REMOTE_ADDR"];
            $retData["shareInfo"] = urldecode(json_encode($apiParam));//处理中文解码
            return $retData;
        }


        /**
         * @打印数组
         * @param $arr
         */
        public static function log($arr){
            $msg = null;
            $file = __DIR__.DIRECTORY_SEPARATOR.strtolower(__CLASS__)."-".date('Y-m-d',time()).".log";
            if(!file_exists($file)){
                touch($file);
            } 
            if(is_array($arr)){
              $arr = var_export($arr,true);
            }else if(!is_string($arr)){
               $arr = (string)$arr;
            }
            $msg = "\n".$arr."\n";
            file_put_contents($file, $msg,FILE_APPEND); 
        }


                
        /**
         * @通过网址采集天气数据
         * @param $url 采集地址
         */
        public static function getWeatherByUrl($url){
            $json = Weather::httpGet($url); 
            //Weather::log($json);
            return $json;
        }

        
        /**
         * @将采集数据写入数据入库
         * @param $json 采集结果
         */
        public static function saveData($json){
            //Weather::log($json);
            if (!is_null($json)){
                //构造sql语句
                $sql = "INSERT INTO `weather_city_info` 
                (city,country,prov,city_code,lat,lon,update_time,date_time,txt_d,txt_n,txt_now,tmp_max,tmp_min,tmp_now,hum,hum_now,pcpn,pop,pres,vis,vis_now,wind_dir,wind_sc,wind_spd,fl_now,uv,pm25,qlty,raw_data) VALUES
                (:city,:country,:prov,:city_code,:lat,:lon,:update_time,:date_time,:txt_d,:txt_n,:txt_now,:tmp_max,:tmp_min,:tmp_now,:hum,:hum_now,:pcpn,:pop,:pres,:vis,:vis_now,:wind_dir,:wind_sc,:wind_spd,:fl_now,:uv,:pm25,:qlty,:raw_data)";
                
                //初始化参数
                $raw_data = $json;
                
                //将json解析为数组
                $json = json_decode($json,true);
                $json = $json['HeWeather5'][0];
                
                //进一步处理参数
                $params[':city'] = $json['basic']['city'];
                $params[':country'] = $json['basic']['cnty'];
                $params[':prov'] = null;
                $params[':city_code'] = $json['basic']['id'];
                $params[':lat'] = $json['basic']['lat'];
                $params[':lon'] = $json['basic']['lon'];
                $params[':update_time'] = intval(strtotime($json['basic']['update']['loc']));
                
                $daily_forecast = $json['daily_forecast'][0];
                $now = $json['now'];

                $params[':date_time'] = intval(strtotime($daily_forecast['date']));
                $params[':txt_d'] = isset($daily_forecast['cond']['txt_d']) ? $daily_forecast['cond']['txt_d']:"unknown";
                $params[':txt_n'] = isset($daily_forecast['cond']['txt_n']) ? $daily_forecast['cond']['txt_n']:"unknown";
                $params[':txt_now'] = isset($now['cond']['txt']) ? $now['cond']['txt'] : "unknown";
                $params[':tmp_max'] = intval($daily_forecast['tmp']['max']);
                $params[':tmp_min'] = intval($daily_forecast['tmp']['min']);
                $params[':tmp_now'] = intval($now['tmp']);
                $params[':hum'] = intval($daily_forecast['hum']);
                $params[':hum_now'] = intval($now['hum']);
                $params[':pcpn'] = intval($daily_forecast['pcpn']);
                $params[':pop'] = $daily_forecast['pop'];
                $params[':pres'] = intval($daily_forecast['pres']);
                $params[':vis'] = intval($daily_forecast['vis']);
                $params[':vis_now'] = isset($now['vis']) ? intval($now['vis']) : 0;
                $params[':wind_dir'] = $daily_forecast['wind']['dir'];
                $params[':wind_sc'] = $daily_forecast['wind']['sc'];
                $params[':wind_spd'] = intval($daily_forecast['wind']['spd']);
                $params[':fl_now'] = isset($now['fl']) ? intval($now['fl']) : 0;
                $params[':uv'] = intval($daily_forecast['uv']);
                $params[':pm25'] = isset($json['aqi']) ? intval($json['aqi']['city']['pm25']) : 0;
                $params[':qlty'] = isset($json['aqi']) ? $json['aqi']['city']['qlty'] : null;
                $params[':raw_data'] = $raw_data;
                Weather::log($params);

                //执行入库
                Weather::query($sql,$params); 
            }
        }
}

?>
