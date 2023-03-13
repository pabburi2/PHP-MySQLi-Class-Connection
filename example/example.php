<?php
/**
 * --------------------------------------------------------------------------
 *
 *  MySQLi Class
 *
 *  작성자: pabburi.co.kr
 *  작성일: 2023. 03. 00
 *
 * --------------------------------------------------------------------------
 */

// set_time_limit(0);
// ini_set("display_errors", 0);
// ini_set('memory_limit','2048M');
// setlocale(LC_CTYPE, 'ko_KR.utf8');
// date_default_timezone_set('Asia/Seoul');

if ( !isset($_ENV['HOSTNAME']) ) $_ENV['HOSTNAME'] = '';
$HOST_NAME  = ($_ENV['HOSTNAME']) ? $_ENV['HOSTNAME']:php_uname('n');


# 환경설정이 먼저 include 되어야 한다.
include('../src/pabburi_mysqli_config.php');
include('../src/pabburi_mysqli_class.php');


# 현재 PHP버전
echo 'Current PHP version: ' . phpversion() . PHP_EOL;

# 마스터 접속 테스트
mysqli_report(MYSQLI_REPORT_OFF);
$oMysqliM       = new pabburi_mysqli($aDbMaser);
$oDbConInfo     = $oMysqliM->getConTime();
echo date('Y-m-d H:i:s ') . '마스터 커넥션 소요시간: ' . $oDbConInfo->timeConAllResult . '초' . PHP_EOL;
echo date('Y-m-d H:i:s ') . '마스터 접속서버: ' . $oMysqliM->host_info . PHP_EOL;
echo date('Y-m-d H:i:s ') . '마스터 접속서버 idx: ' . $oMysqliM->_dbSvrIdx . PHP_EOL;

# Slave 접속
// $oMysqliS       = new pabburi_mysqli($aDbSlave);
// $oDbConInfo     = $oMysqliS->getConTime();
// echo date('Y-m-d H:i:s ') . 'Slave 커넥션 소요시간: ' . $oDbConInfo->timeConAllResult . '초' . PHP_EOL;
// echo date('Y-m-d H:i:s ') . 'Slave 접속서버: ' . $oMysqliS->host_info . PHP_EOL;
// echo date('Y-m-d H:i:s ') . 'Slave 접속서버 idx: ' . $oMysqliS->dbSvrIdx . PHP_EOL;

# 내장 메소드를 이용한 기본적인 데이터 가져오는 것
// $query    = " select * from testdb.test_table ORDER BY acol DESC LIMIT 3";
// $q=$oMysqliM->query($query);
// while($r=$q->fetch_assoc()) {
//   print_r($r);
// }
// echo date('Y-m-d H:i:s ') . '---------------------------------------------------' . PHP_EOL;

// 한개의 데이터 가져오기
$query      = " select * from testdb.test_table ORDER BY acol DESC LIMIT 1";
$aRsDB      = $oMysqliM->_query($query, 'assoc');
print_r($aRsDB);
echo PHP_EOL;

// 여러개의 데이터 처리 하기
$query      = " select * from testdb.test_table ORDER BY acol DESC LIMIT 2";
$oResult    = $oMysqliM->_query($query, 'rSelect');
while ( $oRs = $oResult->fetch_object() ) {
  print_r($oRs);
}

echo PHP_EOL;
echo date('Y-m-d H:i:s ') . '-----------------------인서트----------------------------' . PHP_EOL;
$sql        = "insert into testdb.test_table values (10, '테이브3', now())";
$result     = $oMysqliM->_query($sql, 'insert');
echo date('Y-m-d H:i:s ') . '인서트갯수: ' . $result . PHP_EOL;
// print_r($oMysqliM);
// print_r($result);

echo PHP_EOL;
echo date('Y-m-d H:i:s ') . '-----------------------업데이트----------------------------' . PHP_EOL;
$sql        = "UPDATE testdb.test_table SET bcol='업데이트' WHERE acol=10 LIMIT 2";
$result     = $oMysqliM->_query($sql, 'update');
echo date('Y-m-d H:i:s ') . '업데이트된갯수: ' . $result . PHP_EOL;


echo PHP_EOL;
echo date('Y-m-d H:i:s ') . '-----------------------Delete----------------------------' . PHP_EOL;
$sql        = "DELETE FROM testdb.test_table WHERE bcol='업데이트' LIMIT 1";
$result     = $oMysqliM->_query($sql, 'delete');
echo date('Y-m-d H:i:s ') . '삭제된갯수: ' . $result . PHP_EOL;


