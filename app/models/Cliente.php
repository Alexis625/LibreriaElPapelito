
<?php

class Cliente
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // Listar clientes según los filtros aplicados
    public function listarClientes(
        $busqueda = '',
        $estado = '',
        $limite = 10,
        $offset = 0
    ) {
        $sql = "SELECT
                    id_cliente,
                    nombre_completo,
                    telefono,
                    correo,
                    direccion,
                    fecha_registro,
                    activo
                FROM clientes
                WHERE (
                    nombre_completo LIKE :busqueda
                    OR telefono LIKE :busqueda
                    OR correo LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array((string)$estado, ['0', '1'], true)) {
            $sql .= " AND activo = :estado";
            $parametros[':estado'] = (int)$estado;
        }

        $sql .= " ORDER BY nombre_completo ASC
                  LIMIT " . max(1, (int)$limite) . "
                  OFFSET " . max(0, (int)$offset);

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar clientes según los filtros aplicados
    public function contarClientes(
        $busqueda = '',
        $estado = ''
    ) {
        $sql = "SELECT COUNT(*)
                FROM clientes
                WHERE (
                    nombre_completo LIKE :busqueda
                    OR telefono LIKE :busqueda
                    OR correo LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array((string)$estado, ['0', '1'], true)) {
            $sql .= " AND activo = :estado";
            $parametros[':estado'] = (int)$estado;
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return (int)$consulta->fetchColumn();
    }

    // Obtener información de un cliente
    public function obtenerCliente($idCliente)
    {
        $sql = "SELECT
                    id_cliente,
                    nombre_completo,
                    telefono,
                    correo,
                    direccion,
                    fecha_registro,
                    activo
                FROM clientes
                WHERE id_cliente = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ':id' => $idCliente
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // Registrar un nuevo cliente
    public function registrarCliente(
        $nombreCompleto,
        $telefono,
        $correo,
        $direccion
    ) {
        try {

            $nombreCompleto = trim($nombreCompleto);
            $telefono = trim($telefono ?? '');
            $correo = trim($correo ?? '');
            $direccion = trim($direccion ?? '');

            // Validar nombre
            if (
                $nombreCompleto === '' ||
                mb_strlen($nombreCompleto) > 120
            ) {
                throw new Exception("El nombre del cliente no es válido.");
            }

            // Validar teléfono
            if (mb_strlen($telefono) > 25) {
                throw new Exception("El teléfono supera el límite permitido.");
            }

            // Validar correo
            if (
                $correo !== '' &&
                (
                    !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
                    mb_strlen($correo) > 120
                )
            ) {
                throw new Exception("El correo electrónico no es válido.");
            }

            // Validar dirección
            if (mb_strlen($direccion) > 255) {
                throw new Exception("La dirección supera el límite permitido.");
            }

            $sql = "INSERT INTO clientes (
                        nombre_completo,
                        telefono,
                        correo,
                        direccion
                    ) VALUES (
                        :nombre,
                        :telefono,
                        :correo,
                        :direccion
                    )";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':nombre' => $nombreCompleto,
                ':telefono' => $telefono !== '' ? $telefono : null,
                ':correo' => $correo !== '' ? $correo : null,
                ':direccion' => $direccion !== '' ? $direccion : null
            ]);

            return [
                'exito' => true,
                'id_cliente' => $this->conexion->lastInsertId()
            ];

        } catch (Throwable $e) {

            error_log("Error al registrar cliente: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => 'No fue posible registrar el cliente.'
            ];
        }
    }

    // Actualizar información de un cliente
    public function actualizarCliente(
        $idCliente,
        $nombreCompleto,
        $telefono,
        $correo,
        $direccion
    ) {
        try {

            $nombreCompleto = trim($nombreCompleto);
            $telefono = trim($telefono ?? '');
            $correo = trim($correo ?? '');
            $direccion = trim($direccion ?? '');

            // Validar identificador
            if (!filter_var($idCliente, FILTER_VALIDATE_INT)) {
                throw new Exception("El identificador del cliente no es válido.");
            }

            // Validar nombre
            if (
                $nombreCompleto === '' ||
                mb_strlen($nombreCompleto) > 120
            ) {
                throw new Exception("El nombre del cliente no es válido.");
            }

            // Validar teléfono
            if (mb_strlen($telefono) > 25) {
                throw new Exception("El teléfono supera el límite permitido.");
            }

            // Validar correo
            if (
                $correo !== '' &&
                (
                    !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
                    mb_strlen($correo) > 120
                )
            ) {
                throw new Exception("El correo electrónico no es válido.");
            }

            // Validar dirección
            if (mb_strlen($direccion) > 255) {
                throw new Exception("La dirección supera el límite permitido.");
            }

            $sql = "UPDATE clientes
                    SET nombre_completo = :nombre,
                        telefono = :telefono,
                        correo = :correo,
                        direccion = :direccion
                    WHERE id_cliente = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':nombre' => $nombreCompleto,
                ':telefono' => $telefono !== '' ? $telefono : null,
                ':correo' => $correo !== '' ? $correo : null,
                ':direccion' => $direccion !== '' ? $direccion : null,
                ':id' => $idCliente
            ]);

            if ($consulta->rowCount() === 0) {
                $cliente = $this->obtenerCliente($idCliente);

                if (!$cliente) {
                    throw new Exception("El cliente no existe.");
                }
            }

            return [
                'exito' => true
            ];

        } catch (Throwable $e) {

            error_log("Error al actualizar cliente: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => 'No fue posible actualizar el cliente.'
            ];
        }
    }

    // Activar o desactivar un cliente
    public function cambiarEstadoCliente($idCliente, $estado)
    {
        try {

            if (
                !filter_var($idCliente, FILTER_VALIDATE_INT) ||
                !in_array((string)$estado, ['0', '1'], true)
            ) {
                throw new Exception("Los datos del cliente no son válidos.");
            }

            $sql = "UPDATE clientes
                    SET activo = :estado
                    WHERE id_cliente = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':estado' => (int)$estado,
                ':id' => $idCliente
            ]);

            if ($consulta->rowCount() === 0) {
                $cliente = $this->obtenerCliente($idCliente);

                if (!$cliente) {
                    throw new Exception("El cliente no existe.");
                }
            }

            return [
                'exito' => true
            ];

        } catch (Throwable $e) {

            error_log("Error al cambiar estado del cliente: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => 'No fue posible cambiar el estado del cliente.'
            ];
        }
    }
}
