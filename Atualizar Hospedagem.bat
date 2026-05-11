@echo off
title Auto Git Push para GitHub

:: --- CONFIGURAÇÕES ---
:: 1. ATENÇÃO: defina o caminho CERTO da sua pasta do projeto
set PROJECT_DIR=C:\wamp64\www\encartes

:: 2. ATENÇÃO: defina o caminho CERTO da pasta de instalação do Git
:: Use um dos padrões abaixo. Na dúvida, teste os dois.
:: set GIT_DIR=C:\Program Files\Git\bin
set GIT_DIR=C:\Program Files\Git\cmd
:: --- FIM DAS CONFIGURAÇÕES ---

:: Verifica se o diretório do Git existe, se não, tenta o outro caminho
if not exist "%GIT_DIR%\git.exe" (
    echo Caminho do Git nao encontrado: %GIT_DIR%
    echo Tentando o caminho alternativo...
    set GIT_DIR=C:\Program Files\Git\bin
    if not exist "%GIT_DIR%\git.exe" (
        echo ERRO: Git nao encontrado. Verifique a instalacao.
        pause
        exit /b 1
    )
)

:: Adiciona o Git ao PATH para esta sessao
set PATH=%GIT_DIR%;%PATH%

:: Muda para o diretório do projeto
cd /d "%PROJECT_DIR%" || (
    echo ERRO: Pasta do projeto nao encontrada: %PROJECT_DIR%
    pause
    exit /b 1
)

echo.
echo =================================================
echo Iniciando processo de atualizacao...
echo Repositorio: %cd%
echo =================================================
echo.

git add .
git commit -m "Auto: %date% %time%"
git push origin main

echo.
echo =================================================
echo Processo finalizado!
pause