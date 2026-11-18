
<?php

class Categoria {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Listar todas las categorías
    public function listarCategorias() {

        $sql = "SELECT
                    id_categoria,
                    nombre,
                    descripcion,
                    activa
                FROM categorias
                ORDER BY nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar una categoría
    public function agregarCategoria($nombre, $descripcion) {

        $sql = "INSERT INTO categorias (nombre, descripcion)
                VALUES (:nombre, :descripcion)";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion
        ]);
    }


// Eliminar una categoría si no tiene productos asociados
public function eliminarCategoria($idCategoria) {

    // Comprobar si existen productos asociados
    $sql = "SELECT COUNT(*)
            FROM productos
            WHERE id_categoria = :id_categoria";

    $consulta = $this->conexion->prepare($sql);

    $consulta->execute([
        ':id_categoria' => $idCategoria
    ]);

    $productosAsociados = $consulta->fetchColumn();

    // No permitir eliminar categorías utilizadas
    if ($productosAsociados > 0) {
        return false;
    }

    // Eliminar categoría
    $sql = "DELETE FROM categorias
            WHERE id_categoria = :id_categoria";

    $consulta = $this->conexion->prepare($sql);

    return $consulta->execute([
        ':id_categoria' => $idCategoria
    ]);
}


}
