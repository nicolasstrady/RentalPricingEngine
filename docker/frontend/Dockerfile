FROM node:24-alpine

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend ./

EXPOSE 4200

CMD ["npm", "run", "start:docker"]
