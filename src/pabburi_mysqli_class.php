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


/**
 * MySQLi 확장 클래스
 */
class pabburi_mysqli extends mysqli
{
  private $_MASTER_MAX_IDX  = 200;    // 마스터, 슬레이브 서버의 기준

  private static $_connected = false; // 커넥션 성공 했나?
  private $_dbSvrType        = '';    // _MASTER_MAX_IDX 으로 체크한 master, slave
  public  $_dbSvrIdx         = 0;     // 환경설정의 서버 index

  // 커넥션 타임
  private $_timeConAllStart  = 0;     // 커넥선 시작시간
  private $_timeConAllEnd    = 0;     // 커넥션 종료시간
  private $_timeConAllResult = 0;     // 커넥션 총 소요시간 - failover 포함 전체

  private $_timeConStart     = 0;     // 커넥션 시작시간 - 최종 커녁션
  private $_timeConEnd       = 0;     // 커넥션 종료시간 - 최종 커녁션
  private $_timeConResult    = 0;     // 커넥션 총 소요시간 - 최종 커넥션

  // 쿼리 타임
  private $_timeQryStart     = 0;     // _query 메소드 시작시간
  private $_timeQryEnd       = 0;     // _query 메소드 종료시간
  private $_timeQryResult    = 0;     // _query 메소드 총 소요시간

  /**
   * 디비 커넥션을 수행(failover 처리 진행)
   *
   * @param array $aConInfo
   *
   */
  public function __construct( array $aConInfo ) {
    $this->setConTime('all', 'start');

    $aDbSvrIdx      = array_keys( $aConInfo );
    $dbSvrIdxStart  = $aDbSvrIdx[0];
    $dbSvrIdxEnd    = array_pop($aDbSvrIdx);
    $this->_dbSvrType   = 'master';

    # slave 일 경우 사용할 서버를 랜덤하게 섞어 사용한다.
    if ( $dbSvrIdxStart >= $this->_MASTER_MAX_IDX ) {
      $this->_dbSvrType   = 'slave';
      $aConInfo       = $this->_shuffle_assoc($aConInfo);
    }

    # 커넥션에 실패하게 되면 failover 설정으로 다음 커넥션을 시도 한다.
    foreach ( $aConInfo as $dbSvrIdx => $aDbInfo ) {
      // extract( $aDbInfo );
      $oDbinfo    = (object)$aDbInfo;
      $host       = $oDbinfo->host;
      $socket     = null;
      $user       = $oDbinfo->user;
      $pass       = $oDbinfo->pass;

      # 공백으로 넣거나 생략이 가능한것들
      if ( isset($oDbinfo->port) ) {
        $port       = $oDbinfo->port;
      }
      if ( isset($oDbinfo->db) ) {
        $db         = $oDbinfo->db;
      }
      if ( isset($oDbinfo->charset) ) {
        $charset    = $oDbinfo->charset;
      }
      if ( isset($oDbinfo->log) ) {
        $is_log     = $oDbinfo->log;
      }
      if ( !$port ) {
        $port       = 3306;
      }

      $this->setConTime('single', 'start');
      try {
        // 없는 서버의 경우 나오는 메시지
        // PHP Warning:  mysqli::__construct(): (HY000/2002): 대상 컴퓨터에서 연결을 거부했으므로 연결하지 못했습니다.
        parent::__construct($host, $user, $pass, $db, $port, $socket);
      }
      catch (mysqli_sql_exception $e) {
        // error_log($e->__toString());
        // echo date('Y-m-d H:i:s ') . '접속실패 svrIdx: ' . $dbSvrIdx . PHP_EOL;
      }
      $this->setConTime('single', 'end');

      if ( $this->connect_errno < 1 ) {
        if ( isset($charset) && strlen($charset) > 3 ) {
          $this->set_charset($charset);
        }
        self::$_connected   = true;
        $this->_dbSvrIdx    = $dbSvrIdx;
        $this->setConTime('all', 'end');
        return $this;
      }
    }
	}

  /**
   * 디비 커넥션 처리 시간 저장
   *
   * @param mixed $mode='all'
   * @param mixed $type='start'
   *
   */
  private function setConTime( $mode='all', $type='start' ) {
    if ( $mode == 'all' ) {
      if ( $type == 'start' ) $this->_timeConAllStart = microtime(true);
      if ( $type == 'end' ) {
        $this->_timeConAllEnd     = microtime(true);
        $this->_timeConAllResult  = bcsub($this->_timeConAllEnd, $this->_timeConAllStart, 4);
      }
    }
    else {
      if ( $type == 'start' ) $this->_timeConStart = microtime(true);
      if ( $type == 'end' ) {
        $this->_timeConEnd        = microtime(true);
        $this->_timeConResult     = bcsub($this->_timeConEnd, $this->_timeConStart, 4);
      }
    }
  }

  /**
   * 저장된 디비 커넥션 시간 돌려줌
   *
   * @return object
   *
   */
  public function getConTime() : object
  {
    $oDbConInfo   = (object)[];
    $oDbConInfo->timeConAllStart  = $this->_timeConAllStart;
    $oDbConInfo->timeConAllEnd    = $this->_timeConAllEnd;
    $oDbConInfo->timeConAllResult = $this->_timeConAllResult;

    $oDbConInfo->timeConStart     = $this->_timeConStart;
    $oDbConInfo->timeConEnd       = $this->_timeConEnd;
    $oDbConInfo->timeConResult    = $this->_timeConResult;

    return $oDbConInfo;
  }

  /**
   * 쿼리 사용시간
   *
   * @return object
   *
   */
  private function getQuryTime() : object {
    $oDbQryTime     = (object)[];
    $oDbQryTime->timeQryStart   = $this->_timeQryStart;
    $oDbQryTime->timeQryEnd     = $this->_timeQryEnd;
    $oDbQryTime->timeQryResult  = $this->_timeQryResult;

    return $oDbQryTime;
  }

  /**
   * 통로를 단일화 하여 통제하기 위해서 사용하는 쿼리
   *
   * @param string $sqlQuery
   * @param string $resultType='array'
   *
   * @return [type]
   *
   */
  public function _query( string $sqlQuery, string $resultType='assoc' )
  {
    global $aSlowQuery;     // 슬로우 쿼리 체크 환경
    global $aQuerySave;     // 로그를 남겨야할 쿼리

    $resultType     = strtolower($resultType);
    $result         = '';
    $this->_timeQryStart  = microtime(true);

    # 쿼리에 대한 필터링
    if ( isset($aQuerySave) && is_array($aQuerySave) )
    {
      foreach ( $aQuerySave as $key => $chkWord ) {
        if ( $key == 'path' ) continue;
        if ( stristr($sqlQuery, $chkWord) ) {
          $aData        = array($sqlQuery, $chkWord);
          $wrtSize      = $this->_log_write( $aData, $aQuerySave['path']);
          break;
        }
      }
    }

    # slave 서버인 경우 체크
    # 허용이 되는 유저로 사용 되는 경우 insert, update, delete 안되도록 한다.
    $is_stopWord    = false;
    if ( $this->_dbSvrType == 'slave' ) {
      $is_stopWord    =  _query_stopword_check( $sqlQuery );
    }
    if ( $is_stopWord ) $result = ERR_DB_STOP_WORD;

    #
    if ( $resultType == 'insert' || $resultType == 'update' || $resultType == 'delete' ) {
      parent::query($sqlQuery);
      if ( $this->errno > 0 ) {
        $result = $this->error;
      }
      else {
        $result = $this->affected_rows;
      }
    }
    # SELECT
    else
    {
      $oResult      = parent::query($sqlQuery);
      if ( is_object($oResult) )
      {
        // 여러개의 rows 결과
        $this->_countSelect   = $oResult->num_rows;
        if ( $resultType == 'rSelect' || $resultType == 'rselect' ) {
          $result = $oResult;
        }
        elseif ( $resultType == 'assoc' ) {
          $result       = $oResult->fetch_assoc();
        }
        elseif ( $resultType == 'obj' || $resultType == 'object' ) {
          $result       = $oResult->fetch_object();
        }
        else {
          $result = ERR_DB_QUERY_NOT;
        }
      }
      else {
        $result = ERR_DB_QUERY;
      }
    }
    $this->_timeQryEnd    = microtime(true);
    $this->_timeQryResult = bcsub($this->_timeQryEnd, $this->_timeConAllStart, 4);

    # 슬로우 쿼리 저장
    if ( isset($aSlowQuery['max_time']) && $this->_timeQryResult > $aSlowQuery['max_time'] ) {
      $wrtSize      = $this->_log_write( array($sqlQuery), $aSlowQuery['path']);
    }

    return $result;
  }


  /**
   * 슬레이브 서버에 실행되면 안되는 쿼리
   *  > 설정이 안되어 있거나 root 유저 사용해야 되는 경우 실수 방지를 위한 것
   */
  public function _query_stopword_check( string $dbQuery ) : bool
  {
    $is_stopWord    = false;
    $dbQuery        = trim($dbQuery );
    $aQueryStopWord = ['revoke', 'insert', 'drop', 'alter', 'grant', 'replace', 'update', 'delete', 'create'];
    foreach( $aQueryStopWord as $aidx => $stopWord ) {
      $stopWordLen    = strlen($stopWord);
      $chkQryStr      = substr(strtolower($dbQuery), 0, $stopWordLen);
      if ( $chkQryStr == $stopWord ) {
        $is_write       = db_query_stopword_check_log( $stopWordLen );
        $is_stopWord    = true;
        break;
      }
    }

    return $is_stopWord;
  }


  /**
   *  로그 분석할 수 있도록 URL을 남긴다.
   */
  private function _log_write( array $aData, string $path)
  {
    if ( !is_dir($path) ) {
      return -1;
    }

    $aUrl         = [];
    $aUrl[]       = $_SERVER['REQUEST_SCHEME'] . '://';
    $aUrl[]       = $_SERVER['HTTP_HOST'];
    $aUrl[]       = $_SERVER['REQUEST_URI'];
    $url          = implode('', $aUrl);

    $logData      = PHP_EOL;
    foreach ($aData as $key => $data ) {
      $logData     .= date('Y-m-d H:i:s ') . $data . PHP_EOL;
    }
    $logData     .= date('Y-m-d H:i:s ') . $url . PHP_EOL;
    $logData     .= date('Y-m-d H:i:s ') . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;

    $wrtFile      = $path . '/' . date('Ymd') . '.log';
    $wrtSize      = file_put_contents($wrtFile, $logData, FILE_APPEND);

    return $wrtSize;
  }


  /**
   * 배열을 인텍스 변함없이 섞어 주기
   */
  public function _shuffle_assoc( array $aData ) : array
  {
    $aNew     = [];
    $aKeys    = array_keys($aData);
    shuffle($aKeys);
    print_r($aKeys);

    foreach($aKeys as $key) {
      $aNew[$key] = $aData[$key];
    }
    return $aNew;
  }

  /**
   * 디비 연결이 되었으면 끊어 준다.
   */
  public function close() {
    if( self::$_connected ) {
      parent::close();
      self::$_connected = false;
    }
  }

  /**
   * 소멸될때 커넥션을 닫는다.
   */
  public function __destruct() {
    $this->close();
  }

}

