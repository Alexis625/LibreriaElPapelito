
<?php

require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController {

    private $categoria;

    public function __construct($conexion) {
        $this->categoria = new Categoria($conexion);
    }

    // Mostrar categorías
    public function index() {

        return $this->categoria->listarCategorias();
    }

    // Registrar categoría
    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $nombre = trim($_POST['nombre_categoria'] ?? '');
        $descripcion = trim($_POST['descripcion_categoria'] ?? '');

        if ($nombre === '') {
            return false;
        }

        return $this->categoria->agregarCategoria(
            $nombre,
            $descripcion
        );
    }


// Eliminar una categoría
public function eliminar() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $idCategoria = filter_input(
        INPUT_POST,
        'id_categoria',
        FILTER_VALIDATE_INT
    );

    if (!$idCategoria || $idCategoria <= 0) {
        return false;
    }

    return $this->categoria->eliminarCategoria($idCategoria);
}

}
