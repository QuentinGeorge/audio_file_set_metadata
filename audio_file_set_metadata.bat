@ECHO OFF
set scriptPath=D:\Projects\audio_file_set_metadata\
set scriptName=audio_file_set_metadata.php
php %scriptPath%%scriptName% -d "%cd%"
ECHO.
PAUSE
CLS
EXIT
