@echo off

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set CURR_HOME=%~dp0

goto init
goto cleanup

:init
if "%PHP_COMMAND%" == "" goto no_phpcommand

goto run
goto cleanup

:run
"%PHP_COMMAND%" -qC "%CURR_HOME%\bin\php-daemon" %*
goto cleanup

:no_phpcommand
REM echo ------------------------------------------------------------------------
REM echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
REM echo          executable (e.g. C:\PHP\php.exe).  (Assuming php.exe on Path)
REM echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto cleanup

:cleanup
if "%OS%"=="Windows_NT" @endlocal
REM pause
