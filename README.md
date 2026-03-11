# Apis_Access

`Apis_Access` es un proyecto backend desarrollado en **PHP** (utilizando **Slim Framework 4**) orientado a exponer APIs para interactuar con un sistema de control de acceso.

Su arquitectura sigue un modelo MVC simplificado, separando los Controladores, los Modelos y la lógica central de la aplicación. Además, hace uso de **Composer** para la gestión de dependencias y está preparado para ejecutarse bajo **Docker**.

El propósito central de esta iteración de este proyecto es administrar y gestionar registros históricos de usuarios (`userinfo`) dentro de la base de datos de control de asistencia.

## Endpoints de la API

El sistema actualmente expone dos APIs principales bajo la ruta base de la versión 1 (`/api/v1`). Ambas APIs operan bajo una **restricción de tiempo estricta**: solo pueden interactuar con registros de usuarios que tengan **más de 6 años de antigüedad** partiendo de su fecha y hora de creación (`create_time`) calculada dinámicamente frente a la fecha y hora actual.

### 1. Obtener Usuarios Antiguos (Legacy Users)

Obtiene una lista de todos los usuarios cuya fecha de creación exceda los 6 años de antigüedad.

*   **Ruta:** `GET /api/v1/users/legacy`
*   **Parámetros:** Ninguno
*   **Descripción de Respuesta:**
    Retorna un JSON con el estatus de la operación, la cantidad de registros encontrados y un array con la información solicitada de cada usuario (`name`, `lastname`, `card`, `card_number_type`, `Gender`, `identifycard`, `create_time`).

*   **Ejemplo de Petición cURL:**
    ```bash
    curl http://localhost:8090/api/v1/users/legacy
    ```

*   **Ejemplo de Respuesta Exitosa:**
    ```json
    {
        "status": "success",
        "count": 1,
        "data": [
            {
                "name": "Juan",
                "lastname": "Perez",
                "card": "8430291",
                "card_number_type": 1,
                "Gender": "M",
                "identifycard": "EMP001",
                "create_time": "2018-05-15 08:30:00"
            }
        ]
    }
    ```

---

### 2. Eliminar Usuario Antiguo Permamentemente

Elimina de forma física e irreversible un registro específico de un usuario, siempre y cuando cumpla con el criterio de haber sido creado hace más de 6 años. Si el usuario existe pero tiene menos de 6 años de antigüedad, la API denegará la eliminación y retornará un error `404`.

*   **Ruta:** `DELETE /api/v1/users/legacy/{id}`
*   **Parámetros de Ruta:**
    *   `id` (int): El ID interno (`userid`) del empleado en la base de datos.
*   **Descripción de Respuesta:**
    Retorna una confirmación del borrado del usuario si se cumplen las condiciones, o un mensaje de error explicativo en caso contrario.

*   **Ejemplo de Petición cURL:**
    ```bash
    curl -X DELETE http://localhost:8090/api/v1/users/legacy/10
    ```

*   **Ejemplo de Respuesta Exitosa (200 OK):**
    ```json
    {
        "status": "success",
        "message": "Usuario con ID 10 eliminado correctamente."
    }
    ```

*   **Ejemplo de Respuesta de Error (404 Not Found):**
    ```json
    {
        "status": "error",
        "message": "No se pudo eliminar el usuario. Puede que el ID no exista o el usuario no cumple con el requisito de ser mayor a 6 años."
    }
    ```
