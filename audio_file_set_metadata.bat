@ECHO OFF
set scriptPath=D:\Projects\audio_file_set_metadata\audio_file_set_metadata.php
php %scriptPath% -d "%cd%"
ECHO.
PAUSE
CLS
EXIT
