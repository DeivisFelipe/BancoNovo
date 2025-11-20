@echo off
echo Iniciando BancoNovo Docker...
echo.

REM Copiar arquivo .env
echo Copiando .env.docker para .env...
copy /Y .env.docker .env

REM Subir containers
echo Subindo containers...
docker-compose up -d

echo.
echo ========================================
echo BancoNovo Docker iniciado!
echo ========================================
echo.
echo Servicos disponiveis:
echo - Laravel:   http://localhost:8000
echo - Vite:      http://localhost:5173
echo - WebSocket: http://localhost:9000
echo - PostgreSQL: localhost:5432
echo - pgAdmin:   http://localhost:5050
echo   * Email: admin@banconovo.com
echo   * Senha: admin123
echo.
echo Para ver os logs: docker-compose logs -f
echo Para parar: docker-compose down
echo ========================================
