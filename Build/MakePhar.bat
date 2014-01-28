@echo off

php -dphar.readonly=0 empir make QueueIT.Security.phar notavailable.php ..\QueueIT.Security --exclude=".buildpath|.project|.settings*