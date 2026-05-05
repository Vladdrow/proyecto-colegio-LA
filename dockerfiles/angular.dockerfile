FROM node:20-alpine

WORKDIR /app

# Copiar archivos de dependencias primero (para aprovechar caché de Docker)
COPY package*.json ./

# Instalar dependencias del proyecto
RUN npm install

# Instalar Angular CLI globalmente
RUN npm install -g @angular/cli

# Copiar el resto del código (opcional, porque usas volumen)
# COPY . .

# Exponer puerto
EXPOSE 4200

# Comando para iniciar con polling (mejor para Docker)
CMD ["ng", "serve", "--host", "0.0.0.0", "--port", "4200", "--poll=2000"]