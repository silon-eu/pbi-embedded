rem =================================== synchronizace stavu uživatelů z AD
"F:\PHP\v8.3.6-nts\php.exe" "F:\websrv\bi\index.php" "Admin:Users:syncUsers"

rem =================================== odmazání logů starších než rok
"F:\PHP\v8.3.6-nts\php.exe" "F:\websrv\bi\index.php" "Admin:Reporting:logCleanup"