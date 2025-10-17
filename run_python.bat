@echo off
REM Critical Windows Socket fix for PHP/Apache execution
set SYSTEMROOT=C:\Windows
set SystemRoot=C:\Windows

REM Disable hash randomization to avoid random source issues
set PYTHONHASHSEED=0

REM Set unbuffered output for proper Laravel communication
set PYTHONUNBUFFERED=1

REM Explicitly set Python path
set PYTHONPATH=%PYTHONPATH%;C:\Users\chand\AppData\Local\Programs\Python\Python310\Lib\site-packages

REM Critical: Set working directory to script location to avoid path issues
cd /d "%~dp0"

REM Run Python with all arguments
"C:\Users\chand\AppData\Local\Programs\Python\Python310\python.exe" %*

REM Exit with Python's exit code
exit /b %ERRORLEVEL%