@echo off

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set DEFAULT_CLIENT_MIGRATION_HOME=%~dp0..

goto init
goto cleanup

:init

if "%CLIENT_MIGRATION_HOME%" == "" set CLIENT_MIGRATION_HOME=%DEFAULT_CLIENT_MIGRATION_HOME%
set DEFAULT_CLIENT_MIGRATION_HOME=

if "%PHP_COMMAND%" == "" goto no_phpcommand

goto run
goto cleanup

:run
"%PHP_COMMAND%" -d phar.readonly=off -d html_errors=off -qC "%CLIENT_MIGRATION_HOME%\bin\php-daemon.php" %*
goto cleanup

:no_phpcommand
REM echo ------------------------------------------------------------------------
REM echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
REM echo          executable (e.g. C:\PHP\php.exe).  (Assuming php.exe on Path)
REM echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto init

:err_home
echo ERROR: Environment var CLIENT_MIGRATION_HOME not set. Please point this
echo variable to your local php-daemon installation!
goto cleanup

:cleanup
if "%OS%"=="Windows_NT" @endlocal
REM pause
