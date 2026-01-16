#!/bin/bash
# create_user_ssh.sh
# Script para crear usuario y configurar clave SSH

# Verificar si se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   echo "Este script debe ejecutarse como root (sudo)" 
   exit 1
fi

# Solicitar datos del usuario
read -p "Ingrese el nombre del nuevo usuario: " USERNAME
read -p "Ingrese el directorio home (presione Enter para /home/$USERNAME): " HOME_DIR
read -p "¿Desea agregar una clave SSH pública? (s/n): " ADD_SSH

# Configurar directorio home si no se especificó
if [ -z "$HOME_DIR" ]; then
    HOME_DIR="/home/$USERNAME"
fi

# Crear el usuario
echo "Creando usuario $USERNAME..."
useradd -m -d "$HOME_DIR" -s /bin/bash "$USERNAME"

# Establecer contraseña
echo "Configurando contraseña para $USERNAME..."
passwd "$USERNAME"

# Configurar clave SSH si se solicita
if [[ "$ADD_SSH" == "s" || "$ADD_SSH" == "S" ]]; then
    echo "Configurando SSH para $USERNAME..."
    
    # Crear directorio .ssh
    mkdir -p "$HOME_DIR/.ssh"
    chmod 700 "$HOME_DIR/.ssh"
    
    # Solicitar clave pública
    echo "Pegue la clave pública SSH (presione Ctrl+D cuando termine):"
    cat > "$HOME_DIR/.ssh/authorized_keys"
    
    # Configurar permisos
    chmod 600 "$HOME_DIR/.ssh/authorized_keys"
    chown -R "$USERNAME:$USERNAME" "$HOME_DIR/.ssh"
    
    echo "Clave SSH configurada correctamente."
fi

# Mostrar resumen
echo "======================================"
echo "Usuario $USERNAME creado exitosamente"
echo "Home directory: $HOME_DIR"
echo "UID: $(id -u $USERNAME)"
echo "GID: $(id -g $USERNAME)"