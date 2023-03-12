<?php
/**
 * --------------------------------------------------------------------------
 *
 *  MySQLi Class 환경설정
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


#
define('ERR_DB_STOP_WORD', -100);   // slave 서버에 update 명령등이 들어 갔을 때
define('ERR_DB_QUERY_NOT', -101);   // 쿼리 조건에 맞는것이 없을 때
define('ERR_DB_QUERY',     -102);   // 쿼리 오류



#
# -------------------------------------------------------------
# 멀티 마스터 - 갈레라 클러스터를 위한 다중 설정
# -------------------------------------------------------------
#   아래 마스터, 슬레이브 변수와 무관하게 master, slave 구분은 배열의 idx 값으로 구분 합니다.
#   - 배열 인덱스 기준 100 ~ 199 까지를 마스터로 본다.
#                      200 ~ 슬레이브로 판단 한다.
#
#
# 다중 처리가 가능해도 장애를 위한 설정을 위한것이다
#  > 한곳에 insert, update, delete가 되어야 반값지 않은 메시지를 보지 않게 된다.
#
$aDbMaser[100]['host']      = '192.168.25.80';
$aDbMaser[100]['port']      = 3307;           // ini_get("mysqli.default_port");
$aDbMaser[100]['user']      = 'test_master_user';
$aDbMaser[100]['pass']      = 'testpw1';
$aDbMaser[100]['db']        = 'testdb';
$aDbMaser[100]['charset']   = 'utf8mb4';
$aDbMaser[100]['log']       = false;

$aDbMaser[101]['host']      = '192.168.25.90';
$aDbMaser[101]['port']      = $aDbMaser[100]['port'];
$aDbMaser[101]['user']      = $aDbMaser[100]['user'];
$aDbMaser[101]['pass']      = $aDbMaser[100]['pass'];
$aDbMaser[101]['db']        = $aDbMaser[100]['db'];
$aDbMaser[101]['charset']   = $aDbMaser[100]['charset'];
$aDbMaser[101]['log']       = $aDbMaser[100]['log'];

#
# -------------------------------------------------------------
# 슬레이브 접속 정보
# -------------------------------------------------------------
# - 배열 idx 값을 반드시 200부터 시작 ~
#   > 기본설정으로 가중치는 없으며 랜덤하게 접속
#
$aDbSlave[200]['host']      = '192.168.25.80';
$aDbSlave[200]['port']      = 3307;
$aDbSlave[200]['user']      = 'test_slave_user';
$aDbSlave[200]['pass']      = 'testpw2';
$aDbSlave[200]['db']        = 'testdb';
$aDbSlave[200]['charset']   = 'utf8mb4';
$aDbSlave[200]['log']       = false;

$aDbSlave[201]['host']      = '192.168.25.90';
$aDbSlave[201]['port']      = $aDbSlave[200]['port'];
$aDbSlave[201]['user']      = $aDbSlave[200]['user'];
$aDbSlave[201]['pass']      = $aDbSlave[200]['pass'];
$aDbSlave[201]['db']        = $aDbSlave[200]['db'];
$aDbSlave[201]['charset']   = $aDbSlave[200]['charset'];
$aDbSlave[201]['log']       = $aDbSlave[200]['log'];

// $aDbSlave[202]['host']      = '127.0.55.83';
// $aDbSlave[202]['port']      = $aDbSlave[200]['port'];
// $aDbSlave[202]['user']      = $aDbSlave[200]['user'];
// $aDbSlave[202]['pass']      = $aDbSlave[200]['pass'];
// $aDbSlave[202]['db']        = $aDbSlave[200]['db'];
// $aDbSlave[202]['charset']   = $aDbSlave[200]['charset'];
// $aDbSlave[202]['log']       = $aDbSlave[200]['log'];

# 특정 쿼리 저장
$aQuerySave             = [];
$aQuerySave[]           = 'or 1=1 ';
$aQuerySave['path']     = '/www/pabburi/log/db/query';

# 슬로우쿼리
#   - 웹서버가 쓰기 가능한 권한을 가지고 있어야 한다.
$aSlowQuery             = [];
$aSlowQuery['max_time'] = 0.2;
$aSlowQuery['path']     = '/www/pabburi/log/db/slowquery';



