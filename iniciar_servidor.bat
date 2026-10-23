@echo off
title Sistema Cerbero - Servidor Localhost 8080
color 0A
echo ==========================================================
echo  Iniciando Emulador Localhost do Sistema Cerbero...
echo  URL: http://localhost:8080/
echo ==========================================================
start http://localhost:8080/
powershell -ExecutionPolicy Bypass -File "%~dp0servidor_local.ps1"
pause
