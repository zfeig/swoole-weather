/*
Navicat MySQL Data Transfer

Source Server         : master
Source Server Version : 50544
Source Host           : 192.168.63.241:3306
Source Database       : weather

Target Server Type    : MYSQL
Target Server Version : 50544
File Encoding         : 65001

Date: 2016-12-27 16:21:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for weather_city_info
-- ----------------------------
DROP TABLE IF EXISTS `weather_city_info`;
CREATE TABLE `weather_city_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `city` varchar(255) DEFAULT NULL COMMENT '预报城市',
  `country` varchar(255) DEFAULT NULL COMMENT '预报国家',
  `prov` varchar(255) DEFAULT NULL COMMENT '省份',
  `city_code` varchar(255) DEFAULT NULL COMMENT '城市代码',
  `lat` varchar(255) DEFAULT NULL COMMENT '预报城市纬度',
  `lon` varchar(255) DEFAULT NULL COMMENT '预报城市经度',
  `update_time` int(10) DEFAULT NULL COMMENT '预报时间戳',
  `date_time` int(10) DEFAULT NULL COMMENT '预报日期时间戳如2016-11-21时间戳',
  `txt_d` varchar(255) DEFAULT NULL COMMENT '白天天气',
  `txt_n` varchar(255) DEFAULT NULL COMMENT '夜间天气',
  `txt_now` varchar(255) DEFAULT NULL COMMENT '当前时间天气',
  `tmp_max` int(10) DEFAULT NULL COMMENT '最高温',
  `tmp_min` int(10) DEFAULT NULL COMMENT '最低温',
  `tmp_now` int(10) DEFAULT NULL COMMENT '当前温度',
  `hum` int(10) DEFAULT NULL COMMENT '当天相对湿度',
  `hum_now` int(10) DEFAULT NULL COMMENT '当前相对湿度',
  `pcpn` int(10) DEFAULT NULL COMMENT '降水量（mm）',
  `pop` int(10) DEFAULT NULL COMMENT '降水概率',
  `pres` int(10) DEFAULT NULL COMMENT '气压',
  `vis` int(10) DEFAULT NULL COMMENT '能见度（km）',
  `vis_now` int(10) DEFAULT NULL COMMENT '能见度（km）',
  `wind_dir` varchar(255) DEFAULT NULL COMMENT '风向',
  `wind_sc` varchar(255) DEFAULT NULL COMMENT '风力等级',
  `wind_spd` int(10) DEFAULT NULL COMMENT '风速',
  `fl_now` int(10) DEFAULT NULL COMMENT '当前体感温度',
  `uv` int(10) DEFAULT NULL COMMENT '紫外线指数',
  `pm25` int(10) DEFAULT NULL COMMENT 'pm2.5指数',
  `qlty` varchar(255) DEFAULT NULL COMMENT '空气质量',
  `raw_data` text COMMENT '原始接口数据',
  PRIMARY KEY (`id`),
  KEY `city_code` (`city_code`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15109 DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS=1;
