create database if not exists Upload;
use Upload;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `username` varchar(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`username`, `password`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
GRANT ALL PRIVILEGES ON Upload.* TO 'MYSQL_USER'@'%';
FLUSH PRIVILEGES;