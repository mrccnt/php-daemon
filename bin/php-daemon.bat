@echo off

where php.exe >nul 2>nul
if %errorlevel%==1 (
    @echo php not found in path
    exit
)

if "%OS%"=="Windows_NT" @setlocal

set CURR_HOME=%~dp0

php -qC "%CURR_HOME%\bin\php-daemon" %*

if "%OS%"=="Windows_NT" @endlocal
