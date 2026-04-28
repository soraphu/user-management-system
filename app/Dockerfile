# Stage 1: Build the React application
FROM node:22-alpine AS build
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy the rest of the application and build it
COPY . .
RUN npm run build

# Stage 2: Serve the application with Nginx
FROM nginx:stable-alpine
# Copy the built files from the previous stage to Nginx's html folder
COPY --from=build /app/dist /usr/share/nginx/html

# This replaces the default Nginx "Welcome" config with yours
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]