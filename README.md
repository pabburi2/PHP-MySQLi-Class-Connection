# PHP MySQLi Multi Server
갈레라클러스터의 멀티마스터 서버에서 장애가 발생 했을때 다른 서버로 커넥션 할 수 있도록 구성 된 MySQLi 클래스 입니다.
<br/>

### 주요기능
  - failover 기능을 가진다.
  - 특정 쿼리에 대한 프로그램 위치를 찾을 수 있도록 로그를 남기는 기능
  - 슬로우쿼리에 대한 프로그램을 찾을 수 있도록 로그 남긴다
  - 통로를 단일화 시키기 위해 _query 메소스 사용
    (필요한 경우 query 메소스 상속으로 대체 가능)

### 변수
  - 객체형은 $oObj 형태로 소문자 o로 시작 합니다.<br/>
  - 배열은 $aAry 형태로 소문자 a로 시작합니다.<br/>
  - 멤버 변수는 _(언더라인)으로 시작 합니다.

<br/>

## 권장사항
✨ 중요 파일은 반드시 웹루트 밖에 두세요.<br/>
<br/>
웹루트란?<br/>
https://www.pabburi.co.kr/index.php 경로가 리눅스 시스템에선 아래와 같이 설정 되어 있을 경우
<br/> <br/>

웹사이트의 경로가 다음과 같을 때
```
/www/pabburi_www/html/index.php
                    ^
                    +---- 이 위치가 웹루트가 됩니다.

/www/pabburi_www/site_common/log
/www/pabburi_www/site_common/log/db
/www/pabburi_www/site_common/log/www

/www/pabburi_www/site_common/include
/www/pabburi_www/site_common/include/db
/www/pabburi_www/site_common/include/http
/www/pabburi_www/site_common/include/mail
```