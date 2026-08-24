-- LDMS views - run AFTER importing the database.
-- phpMyAdmin: SQL tab, paste ALL of this, Go. Safe to re-run.
--
-- Why the DROP TABLE lines: mysqldump writes a placeholder TABLE for each
-- view, then replaces it with the real view later in the file. When the
-- view statement fails (cPanel rejects its DEFINER), the placeholder table
-- is left behind - which is what causes
--     #1347 ... is not VIEW
-- These placeholders hold no data, so dropping them loses nothing.
--
-- OPTIONAL CHECK - run this first if you want to confirm they are empty:
--   SELECT TABLE_NAME, TABLE_TYPE, TABLE_ROWS FROM information_schema.TABLES
--   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%_all';

-- 1. remove the leftover placeholder tables
DROP TABLE IF EXISTS `training_all`;
DROP TABLE IF EXISTS `ojt_all`;
DROP TABLE IF EXISTS `participation_all`;
DROP TABLE IF EXISTS `participateojt_all`;

-- 2. create the real views
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `training_all` AS select `training`.`id` AS `id`,`training`.`trainingcode` AS `trainingcode`,`training`.`title` AS `title`,`training`.`program` AS `program`,`training`.`cost` AS `cost`,`training`.`platform` AS `platform`,`training`.`function` AS `function`,`training`.`startdate` AS `startdate`,`training`.`enddate` AS `enddate`,`training`.`starttime` AS `starttime`,`training`.`endtime` AS `endtime`,`training`.`venue` AS `venue`,`training`.`trainer` AS `trainer`,`training`.`hadc` AS `hadc` from `training` union all select `training_archive`.`id` AS `id`,`training_archive`.`trainingcode` AS `trainingcode`,`training_archive`.`title` AS `title`,`training_archive`.`program` AS `program`,`training_archive`.`cost` AS `cost`,`training_archive`.`platform` AS `platform`,`training_archive`.`function` AS `function`,`training_archive`.`startdate` AS `startdate`,`training_archive`.`enddate` AS `enddate`,`training_archive`.`starttime` AS `starttime`,`training_archive`.`endtime` AS `endtime`,`training_archive`.`venue` AS `venue`,`training_archive`.`trainer` AS `trainer`,`training_archive`.`hadc` AS `hadc` from `training_archive`;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW `ojt_all` AS select `ojt`.`id` AS `id`,`ojt`.`trainingcode` AS `trainingcode`,`ojt`.`title` AS `title`,`ojt`.`startdate` AS `startdate`,`ojt`.`enddate` AS `enddate`,`ojt`.`starttime` AS `starttime`,`ojt`.`endtime` AS `endtime`,`ojt`.`venue` AS `venue`,`ojt`.`trainertype` AS `trainertype`,`ojt`.`trainername` AS `trainername`,`ojt`.`totalday` AS `totalday`,`ojt`.`totalhour` AS `totalhour`,`ojt`.`totalman` AS `totalman` from `ojt` union all select `ojt_archive`.`id` AS `id`,`ojt_archive`.`trainingcode` AS `trainingcode`,`ojt_archive`.`title` AS `title`,`ojt_archive`.`startdate` AS `startdate`,`ojt_archive`.`enddate` AS `enddate`,`ojt_archive`.`starttime` AS `starttime`,`ojt_archive`.`endtime` AS `endtime`,`ojt_archive`.`venue` AS `venue`,`ojt_archive`.`trainertype` AS `trainertype`,`ojt_archive`.`trainername` AS `trainername`,`ojt_archive`.`totalday` AS `totalday`,`ojt_archive`.`totalhour` AS `totalhour`,`ojt_archive`.`totalman` AS `totalman` from `ojt_archive`;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW `participation_all` AS select `participation`.`id` AS `id`,`participation`.`trainingid` AS `trainingid`,`participation`.`userid` AS `userid`,`participation`.`attendance` AS `attendance`,`participation`.`q1` AS `q1`,`participation`.`q2` AS `q2`,`participation`.`q3` AS `q3`,`participation`.`q4` AS `q4`,`participation`.`q5` AS `q5`,`participation`.`q6` AS `q6`,`participation`.`q7` AS `q7`,`participation`.`q8` AS `q8`,`participation`.`q9` AS `q9`,`participation`.`q10` AS `q10`,`participation`.`q11` AS `q11`,`participation`.`q12` AS `q12`,`participation`.`q13` AS `q13`,`participation`.`q14` AS `q14`,`participation`.`q15` AS `q15`,`participation`.`q16` AS `q16` from `participation` union all select `participation_archive`.`id` AS `id`,`participation_archive`.`trainingid` AS `trainingid`,`participation_archive`.`userid` AS `userid`,`participation_archive`.`attendance` AS `attendance`,`participation_archive`.`q1` AS `q1`,`participation_archive`.`q2` AS `q2`,`participation_archive`.`q3` AS `q3`,`participation_archive`.`q4` AS `q4`,`participation_archive`.`q5` AS `q5`,`participation_archive`.`q6` AS `q6`,`participation_archive`.`q7` AS `q7`,`participation_archive`.`q8` AS `q8`,`participation_archive`.`q9` AS `q9`,`participation_archive`.`q10` AS `q10`,`participation_archive`.`q11` AS `q11`,`participation_archive`.`q12` AS `q12`,`participation_archive`.`q13` AS `q13`,`participation_archive`.`q14` AS `q14`,`participation_archive`.`q15` AS `q15`,`participation_archive`.`q16` AS `q16` from `participation_archive`;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW `participateojt_all` AS select `participateojt`.`id` AS `id`,`participateojt`.`ojtid` AS `ojtid`,`participateojt`.`userid` AS `userid`,`participateojt`.`attendance` AS `attendance`,`participateojt`.`q1` AS `q1`,`participateojt`.`q2` AS `q2`,`participateojt`.`q3` AS `q3`,`participateojt`.`totalman` AS `totalman`,`participateojt`.`department` AS `department`,`participateojt`.`clerkid` AS `clerkid`,`participateojt`.`operation` AS `operation` from `participateojt` union all select `participateojt_archive`.`id` AS `id`,`participateojt_archive`.`ojtid` AS `ojtid`,`participateojt_archive`.`userid` AS `userid`,`participateojt_archive`.`attendance` AS `attendance`,`participateojt_archive`.`q1` AS `q1`,`participateojt_archive`.`q2` AS `q2`,`participateojt_archive`.`q3` AS `q3`,`participateojt_archive`.`totalman` AS `totalman`,`participateojt_archive`.`department` AS `department`,`participateojt_archive`.`clerkid` AS `clerkid`,`participateojt_archive`.`operation` AS `operation` from `participateojt_archive`;

