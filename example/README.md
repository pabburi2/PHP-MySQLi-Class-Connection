# Multi Server Class Test
마스터, 슬레이브 모두 커넥션 연결이 안되면 다른 서버로 연결 되도록 테스트 가능 합니다.
<br/>


## 테스트를 위한 설정
MySQL 권한 설정은 아래 페이지를 참고하세요.<br>
https://www.pabburi.co.kr/content/linux_server/mysql-%EC%9C%A0%EC%A0%80-%EC%B6%94%EA%B0%80-%EB%B0%8F-%EC%A0%91%EA%B7%BC%EA%B6%8C%ED%95%9C-%EC%A3%BC%EB%8A%94%EB%B0%A9%EB%B2%95grant-revoke/

### 1) Master 유저용
플러그인 설치가 안되어 아래처럼 오류가 발생하면 아래 것을 사용 한다.<br/>
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'BY 'testpw1'' at line 1<br/>

  ```
  CREATE USER 'test_master_user'@'192.168.%' IDENTIFIED WITH 'mysql_native_password' BY 'testpw1';
  CREATE USER 'test_master_user'@'192.168.%' IDENTIFIED BY 'testpw1';

  GRANT REPLICATION SLAVE, REPLICATION CLIENT, RELOAD, SUPER, SELECT, INSERT, UPDATE, DELETE, CREATE, DROP ON *.* TO 'test_master_user'@'192.168.%';
  flush privileges;
  SHOW GRANTS FOR 'test_master_user'@'192.168.%';
  ```
<br/>

### 2) Slave 에선 쓰기 기능이 없는 유저가 필요
  ```
  CREATE USER 'test_slave_user'@'192.168.%' IDENTIFIED WITH 'mysql_native_password' BY 'testpw2';
  CREATE USER 'test_slave_user'@'192.168.%' IDENTIFIED BY 'testpw2';

  GRANT REPLICATION SLAVE, REPLICATION CLIENT, SELECT ON *.* TO 'test_slave_user'@'192.168.%';
  flush privileges;
  SHOW GRANTS FOR 'test_slave_user'@'192.168.%';
  ```
  command denied to user 메시지를 만나면 권한이 없다는 것이다. DELETE 명령에서 나오면 정상인 것이다.


## 데이터 입력하기
```
create database testdb;
use testdb;
create table test_table( acol int, bcol text, dcol datetime comment '입력일' ) comment = '테스트 테이블';
desc test_table;

insert into testdb.test_table values (1, '테이브1', now());
insert into testdb.test_table values (2, '테이브2', now());
insert into testdb.test_table values (3, '테이브3', now());
insert into testdb.test_table values (5, '테이브5', now());
select * from testdb.test_table;
```

## SQL인젝션 대응 - 안전한 쿼리 만들기
번거로워도 아래 처럼 질의 하는 것이 좋습니다. 그리고 오류 발생 한다고 WHERE 문에서 숫자 컬럼에 값 비교 할때 따음표 사용하지 마세요.
```
$link       = dbcon();
$pname      = mysql_real_escape_string($product_name, $link);
$pdesc      = mysql_real_escape_string($product_description, $link);
$query      = sprintf("INSERT INTO products ( `name`, `description`, `user_id`) VALUES ('%s', '%s', %d)", $pname, $pdesc, $_POST['id']);
```

## 예제를 실행 했을 때
실행하면 아래와 같은 결과를 보게 됩니다.
```
$ php example.php
2023-03-12 12:46:00 마스터 커넥션 소요시간: 0.0171초
2023-03-12 12:46:00 마스터 접속서버: 192.168.25.80 via TCP/IP
2023-03-12 12:46:00 마스터 접속서버 idx: 100
Array
(
    [acol] => 10
    [bcol] => 업데이트
    [dcol] => 2023-03-12 17:12:59
)

stdClass Object
(
    [acol] => 10
    [bcol] => 업데이트
    [dcol] => 2023-03-12 17:12:59
)
stdClass Object
(
    [acol] => 10
    [bcol] => 테이브3
    [dcol] => 2023-03-12 17:13:32
)

2023-03-12 12:46:00 -----------------------인서트----------------------------
2023-03-12 12:46:00 인서트갯수: 1

2023-03-12 12:46:00 -----------------------업데이트----------------------------
2023-03-12 12:46:00 업데이트된갯수: 1

2023-03-12 12:46:00 -----------------------Delete----------------------------
2023-03-12 12:46:00 삭제된갯수: 1
```