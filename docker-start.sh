#!/bin/bash

echo "========================================"
echo "  Iniciando BancoNovo no Docker"
echo "========================================"
echo ""

echo "Copiando .env.docker para .env..."
cp -f .env.docker .env

echo ""
echo "Iniciando containers..."
docker-compose up -d

echo ""
echo "========================================"
echo "  BancoNovo iniciado com sucesso!"
echo "========================================"
echo ""
echo "  - App:       http://localhost:8000"
echo "  - Vite:      http://localhost:5173"
echo "  - WebSocket: http://localhost:9000"
echo "  - PgAdmin:   http://localhost:5050"
echo ""
echo "Para ver logs: docker-compose logs -f"
echo "Para parar:    docker-compose down"
echo "========================================"
