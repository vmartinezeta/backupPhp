<?php
require_once "DatabaseOption.php";

class DatabaseBackup
{
    private $host;
    private $user;
    private $password;
    private $database;
    private $conn;
    private $handle;

    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    // Método para conectar a la base de datos
    private function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($this->conn->connect_error) {
            return false; // Retorna false si no se puede conectar
        }
        return true;
    }

    // Método para desconectar de la base de datos
    private function disconnect()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Método para generar el backup
    public function generate($database, $backupFile, $option)
    {
        $this->database = $database;

        // // Si no se proporciona un nombre de archivo, se genera uno automáticamente
        $backup_dir = dirname($backupFile);
        if (!is_dir($backup_dir)) {
            $backup_dir = __DIR__; // Directorio actual del script
        }

        // Verificar si el directorio tiene permisos de escritura
        if (!is_writable($backup_dir)) {
            // Verificar nuevamente los permisos
            return new Message("error", "No se pudo escribir en el directorio proporcionado ni en el directorio actual.");
        }

        if (!file_exists($backupFile)) {
            $backupFile = 'backup_' . $this->database . '_' . date("Y-m-d_H-i-s") . '.sql';
            $backupFile = $backup_dir . "\\" . $backupFile;
        }

        // Conectar a la base de datos
        if (!$this->connect()) {
            return new Message("error", "No se pudo conectar a la base de datos.");
        }

        // Abrir el archivo para escritura
        $this->handle = fopen($backupFile, 'w+');
        if (!$this->handle) {
            $this->disconnect();
            return new Message("error", "No se pudo abrir el archivo para escritura.");
        }

        // Obtener la lista de tablas
        $tables = $this->conn->query("SHOW TABLES");
        while ($row = $tables->fetch_row()) {
            $table = $row[0];
            if ($option === DatabaseOption::$TODO) {
                $this->writeInfoTable($table);
                $this->writeDataTable($table);
            } else if ($option === DatabaseOption::$SOLO_ESTRUCTURA) {
                $this->writeInfoTable($table);
            } else if ($option === DatabaseOption::$SOLO_DATOS) {
                $this->writeDataTable($table);
            }
        }
        // Cerrar el archivo
        fclose($this->handle);

        // Desconectar de la base de datos
        $this->disconnect();

        return new Message("success", "Backup creado exitosamente en: $backupFile");
    }

    public function writeInfoTable($table)
    {
        // Obtener la estructura de la tabla
        $create_table = $this->conn->query("SHOW CREATE TABLE $table");
        $create_table_row = $create_table->fetch_row();
        fwrite($this->handle, "-- Estructura de la tabla $table\n");
        fwrite($this->handle, $create_table_row[1] . ";\n\n");
    }

    public function writeDataTable($table)
    {
        // Obtener los datos de la tabla
        $data = $this->conn->query("SELECT * FROM $table");
        fwrite($this->handle, "-- Datos de la tabla $table\n");
        while ($row_data = $data->fetch_assoc()) {
            $keys = array_keys($row_data);
            $values = array_map([$this->conn, 'real_escape_string'], array_values($row_data));
            fwrite($this->handle, "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES ('" . implode("', '", $values) . "');\n");
        }
        fwrite($this->handle, "\n");
    }
}